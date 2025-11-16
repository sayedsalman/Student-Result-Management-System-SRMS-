<?php
// upload.php
require_once 'config.php';
require_once 'functions.php';
start_session_if_needed();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php'); exit;
}

$class = $_POST['class'] ?? '';
$section = trim($_POST['section'] ?? '');
$group = trim($_POST['group'] ?? '');

if (empty($class)) die('Class required');

if (!isset($_FILES['csvfile']) || $_FILES['csvfile']['error'] !== UPLOAD_ERR_OK) {
    die('File upload failed');
}

$tmp = $_FILES['csvfile']['tmp_name'];
if (!is_uploaded_file($tmp)) die('File error');

$handle = fopen($tmp, 'r');
if (!$handle) die('Cannot open CSV');

$header = fgetcsv($handle);
if (!$header) die('Empty CSV');

for ($i=0;$i<count($header);$i++) $header[$i] = trim($header[$i]);

if (count($header) < 2 || strtolower($header[0]) !== 'roll' || strtolower($header[1]) !== 'name') {
    die('CSV header must start with: roll,name,...');
}

$subject_names = array_slice($header, 2);

// begin transaction
$pdo->beginTransaction();
try {
    $subject_ids = [];
    foreach ($subject_names as $sub) {
        $sid = find_or_create_subject($pdo, $class, $group === '' ? null : $group, $sub);
        $subject_ids[] = $sid;
    }

    $row_count = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 2) continue;
        $roll = trim($row[0] ?? '');
        $name = trim($row[1] ?? '');
        if ($roll === '' || $name === '') continue;

        $student_id = upsert_student($pdo, $roll, $name, $class, $section === '' ? null : $section, $group === '' ? null : $group);

        for ($i=0; $i<count($subject_ids); $i++) {
            $marks_raw = $row[2 + $i] ?? '';
            $marks = is_numeric($marks_raw) ? floatval($marks_raw) : 0;
            upsert_result($pdo, $student_id, $subject_ids[$i], $marks, 100);
        }
        $row_count++;
    }
    $pdo->commit();
    fclose($handle);
    echo "<p>Imported {$row_count} students for class {$class}.</p>";
    echo '<p><a href="admin.php">Back to admin</a></p>';
} catch (Exception $e) {
    $pdo->rollBack();
    fclose($handle);
    die('Import Error: ' . $e->getMessage());
}
