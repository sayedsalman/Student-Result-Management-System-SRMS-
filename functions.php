<?php
// functions.php
require_once 'config.php';

function upsert_student($pdo, $roll, $name, $class, $section, $group = null) {
    $sql = "SELECT id FROM students WHERE class = ? AND section = ? AND roll = ? AND (`group` = ? OR (`group` IS NULL AND ? IS NULL))";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$class, $section, $roll, $group, $group]);
    $row = $stmt->fetch();
    if ($row) {
        $id = $row['id'];
        $pdo->prepare("UPDATE students SET name = ?, `group` = ? WHERE id = ?")
            ->execute([$name, $group, $id]);
        return $id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (roll, name, class, section, `group`) VALUES (?,?,?,?,?)");
        $stmt->execute([$roll, $name, $class, $section, $group]);
        return $pdo->lastInsertId();
    }
}

function find_or_create_subject($pdo, $class, $section, $group, $subject_name) {
    $sql = "SELECT id, max_marks FROM subjects WHERE class = ? AND section = ? AND `group` = ? AND subject_name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$class, $section, $group, $subject_name]);
    $row = $stmt->fetch();
    if ($row) return $row['id'];
    
    // Set default max marks based on subject name (ICT = 50, others = 100)
    $max_marks = (strtoupper($subject_name) === 'ICT') ? 50.00 : 100.00;
    
    $stmt = $pdo->prepare("INSERT INTO subjects (class, section, `group`, subject_name, max_marks) VALUES (?,?,?,?,?)");
    $stmt->execute([$class, $section, $group, $subject_name, $max_marks]);
    return $pdo->lastInsertId();
}

function upsert_result($pdo, $student_id, $subject_id, $marks, $max_marks = null) {
    // If max_marks not provided, get it from subject table
    if ($max_marks === null) {
        $stmt = $pdo->prepare("SELECT max_marks FROM subjects WHERE id = ?");
        $stmt->execute([$subject_id]);
        $max_marks = $stmt->fetchColumn();
        if ($max_marks === false) $max_marks = 100.00;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM results WHERE student_id = ? AND subject_id = ?");
    $stmt->execute([$student_id, $subject_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE results SET marks = ?, max_marks = ? WHERE student_id = ? AND subject_id = ?");
        $stmt->execute([$marks, $max_marks, $student_id, $subject_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO results (student_id, subject_id, marks, max_marks) VALUES (?,?,?,?)");
        $stmt->execute([$student_id, $subject_id, $marks, $max_marks]);
    }
}

function get_student_results($pdo, $class, $section, $group, $roll) {
    $sql = "SELECT s.id AS student_id, s.name, s.roll, s.class, s.section, s.`group`, sub.subject_name, r.marks, r.max_marks
            FROM students s
            LEFT JOIN results r ON r.student_id = s.id
            LEFT JOIN subjects sub ON sub.id = r.subject_id
            WHERE s.class = ? AND s.section = ? AND s.roll = ? AND (s.`group` = ? OR (s.`group` IS NULL AND ? IS NULL))";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$class, $section, $roll, $group, $group]);
    return $stmt->fetchAll();
}

function export_results_csv($pdo, $class, $section, $group = null) {
    // Get subjects for this class/section/group in order
    $sql = "SELECT DISTINCT subject_name FROM subjects WHERE class = ? AND section = ?";
    $params = [$class, $section];
    if (!empty($group)) {
        $sql .= " AND `group` = ?";
        $params[] = $group;
    }
    $sql .= " ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $subject_names = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Build header
    $headers = array_merge(['roll','name'], $subject_names);

    // Get students
    $sql = "SELECT id, roll, name FROM students WHERE class = ? AND section = ?";
    $params = [$class, $section];
    if (!empty($group)) { 
        $sql .= " AND `group` = ?"; 
        $params[] = $group; 
    }
    $sql .= " ORDER BY roll ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

    // Build CSV rows
    $lines = [];
    $lines[] = $headers;
    foreach ($students as $stu) {
        $row = [$stu['roll'], $stu['name']];
        foreach ($subject_names as $sub) {
            $sstmt = $pdo->prepare("SELECT id FROM subjects WHERE class=? AND section=? AND `group`=? AND subject_name=? LIMIT 1");
            $sstmt->execute([$class, $section, $group, $sub]);
            $sid = $sstmt->fetchColumn();
            $mark = '';
            if ($sid) {
                $rstmt = $pdo->prepare("SELECT marks FROM results WHERE student_id=? AND subject_id=? LIMIT 1");
                $rstmt->execute([$stu['id'], $sid]);
                $mark = $rstmt->fetchColumn();
            }
            $row[] = $mark !== false ? $mark : '';
        }
        $lines[] = $row;
    }
    
    $fh = fopen('php://temp', 'r+');
    foreach ($lines as $fields) {
        fputcsv($fh, $fields);
    }
    rewind($fh);
    $csv = stream_get_contents($fh);
    fclose($fh);
    return $csv;
}

/* -------------------------
   Grading helpers - FIXED LOGIC
   ------------------------- */
function grade_from_mark($marks, $max_marks = 100) {
    $m = floatval($marks);
    $percentage = ($max_marks > 0) ? ($m / $max_marks) * 100 : 0;
    
    if ($percentage >= 80) return 'A+';
    if ($percentage >= 70) return 'A';
    if ($percentage >= 60) return 'A-';
    if ($percentage >= 50) return 'B';
    if ($percentage >= 40) return 'C';
    if ($percentage >= 33) return 'D';
    return 'F';
}

function grade_point_from_mark($marks, $max_marks = 100) {
    $m = floatval($marks);
    $percentage = ($max_marks > 0) ? ($m / $max_marks) * 100 : 0;
    
    if ($percentage >= 80) return 5.0;
    if ($percentage >= 70) return 4.0;
    if ($percentage >= 60) return 3.5;
    if ($percentage >= 50) return 3.0;
    if ($percentage >= 40) return 2.0;
    if ($percentage >= 33) return 1.0;
    return 0.0;
}

function compute_subject_grades($subjects) {
    $out = [];
    foreach ($subjects as $s) {
        $pct = ($s['max'] > 0) ? ($s['marks'] / $s['max']) * 100 : 0;
        $grade = grade_from_mark($s['marks'], $s['max']);
        $gp = grade_point_from_mark($s['marks'], $s['max']);
        $out[] = array_merge($s, [
            'percentage' => round($pct, 2), 
            'grade' => $grade, 
            'grade_point' => $gp,
            'is_pass' => ($grade != 'F')
        ]);
    }
    return $out;
}

function compute_overall_grade($subjects_with_gp) {
    $sum_gp = 0; 
    $count = 0;
    $has_fail = false;
    
    foreach ($subjects_with_gp as $s) {
        // If any subject is failed, overall result should be FAIL
        if (!$s['is_pass']) {
            $has_fail = true;
        }
        $sum_gp += floatval($s['grade_point'] ?? 0);
        $count++;
    }
    
    if ($count == 0) return ['gpa' => 0, 'final' => 'F', 'is_pass' => false];
    
    // If failed in any subject, overall result is FAIL regardless of GPA
    if ($has_fail) {
        return ['gpa' => 0, 'final' => 'F', 'is_pass' => false];
    }
    
    $gpa = round($sum_gp / $count, 2);
    
    if ($gpa >= 5.0) $final = 'A+';
    elseif ($gpa >= 4.0) $final = 'A';
    elseif ($gpa >= 3.5) $final = 'A-';
    elseif ($gpa >= 3.0) $final = 'B';
    elseif ($gpa >= 2.0) $final = 'C';
    elseif ($gpa >= 1.0) $final = 'D';
    else $final = 'F';
    
    return ['gpa' => $gpa, 'final' => $final, 'is_pass' => ($final != 'F')];
}
?>