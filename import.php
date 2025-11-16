<?php
// import.php (new)
require_once 'config.php';
require_once 'functions.php';
start_session_if_needed();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php'); exit;
}

$upload_id = intval($_POST['upload_id'] ?? 0);
$stored_name = $_POST['stored_name'] ?? '';
$class = $_POST['class'] ?? '';
$section = trim($_POST['section'] ?? '');
$group = trim($_POST['group'] ?? '');

if (empty($upload_id) || empty($stored_name) || empty($class)) {
    die('Missing parameters');
}

$uploads_dir = __DIR__ . '/uploads';
$fullpath = $uploads_dir . '/' . basename($stored_name);
if (!is_file($fullpath)) die('Uploaded file not found.');

$handle = fopen($fullpath, 'r');
if (!$handle) die('Cannot open CSV');

$header = fgetcsv($handle);
if (!$header) { fclose($handle); die('Empty CSV'); }
for ($i=0;$i<count($header);$i++) $header[$i] = trim($header[$i]);

if (count($header) < 2 || strtolower($header[0]) !== 'roll' || strtolower($header[1]) !== 'name') {
    fclose($handle);
    die('CSV header must start with: roll,name,...');
}

$subject_names = array_slice($header, 2);

// begin transaction
$pdo->beginTransaction();
try {
    $subject_ids = [];
    foreach ($subject_names as $sub) {
    $sid = find_or_create_subject($pdo, $class, $section === '' ? null : $section, $group === '' ? null : $group, $sub);
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
    echo '<p><a href="admin_panel.php">Go to Admin Panel</a> | <a href="admin.php">Back</a></p>';
} catch (Exception $e) {
    $pdo->rollBack();
    fclose($handle);
    die('Import Error: ' . $e->getMessage());
}
