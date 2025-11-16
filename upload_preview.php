<?php
// upload_preview.php (updated)
require_once 'config.php';
start_session_if_needed();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php'); exit;
}

$class = $_POST['class'] ?? '';
$section = trim($_POST['section'] ?? '');
$group = trim($_POST['group'] ?? '');

if (empty($class)) die('Class required');

if (!isset($_FILES['csvfile']) || $_FILES['csvfile']['error'] !== UPLOAD_ERR_OK) die('File upload failed');

$tmp = $_FILES['csvfile']['tmp_name'];
$original_name = $_FILES['csvfile']['name'];
$ext = pathinfo($original_name, PATHINFO_EXTENSION);
if (strtolower($ext) !== 'csv') {
    // still allow if content-type is CSV but extension differs - but we require .csv recommended
    // continue but you may choose to reject
}

$uploads_dir = __DIR__ . '/uploads';
if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

$stored_name = time() . '_' . bin2hex(random_bytes(6)) . '.csv';
$fullpath = $uploads_dir . '/' . $stored_name;
if (!move_uploaded_file($tmp, $fullpath)) {
    die('Failed to store uploaded file.');
}

// Insert metadata to uploads table
$stmt = $pdo->prepare("INSERT INTO uploads (filename, original_name, class, section, `group`, uploaded_by) VALUES (?,?,?,?,?,?)");
$stmt->execute([$stored_name, $original_name, $class, $section === '' ? null : $section, $group === '' ? null : $group, $_SESSION['is_admin'] ? 'admin' : null]);
$upload_id = $pdo->lastInsertId();

// Read header + first 10 rows
$handle = fopen($fullpath, 'r');
if (!$handle) die('Cannot open CSV file.');

$header = fgetcsv($handle);
if (!$header) {
    fclose($handle);
    die('Empty CSV');
}
for ($i=0;$i<count($header);$i++) $header[$i] = trim($header[$i]);

$preview_rows = [];
$max_preview = 10;
$i = 0;
while ($i < $max_preview && ($row = fgetcsv($handle)) !== false) {
    $preview_rows[] = $row;
    $i++;
}
fclose($handle);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>CSV Preview</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<div class="container school-container">
  <div class="school-header">
    <h2><i class="fas fa-eye me-2"></i>CSV Preview</h2>
    <div class="school-badge">Data Validation</div>
  </div>
  
  <div class="school-card">
    <div class="school-card-header">
      File Information
    </div>
    <div class="card-body">
      <p><strong>Original filename:</strong> <?php echo htmlspecialchars($original_name); ?></p>
      <p><strong>Stored as:</strong> <?php echo htmlspecialchars($stored_name); ?></p>
      <p><strong>Class:</strong> <?php echo htmlspecialchars($class); ?> | 
         <strong>Section:</strong> <?php echo htmlspecialchars($section ?: 'N/A'); ?> | 
         <strong>Group:</strong> <?php echo htmlspecialchars($group ?: 'N/A'); ?></p>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead class="table-dark">
        <tr><?php foreach ($header as $h): ?><th><?php echo htmlspecialchars($h); ?></th><?php endforeach; ?></tr>
      </thead>
      <tbody>
        <?php if (count($preview_rows) === 0): ?>
          <tr><td colspan="<?php echo count($header); ?>" class="text-center">No data rows found in CSV.</td></tr>
        <?php else: ?>
          <?php foreach ($preview_rows as $pr): ?>
            <tr><?php foreach ($header as $colIndex => $h): ?>
              <td><?php echo htmlspecialchars($pr[$colIndex] ?? ''); ?></td>
            <?php endforeach; ?></tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <form action="import.php" method="post">
      <input type="hidden" name="upload_id" value="<?php echo htmlspecialchars($upload_id); ?>">
      <input type="hidden" name="stored_name" value="<?php echo htmlspecialchars($stored_name); ?>">
      <input type="hidden" name="class" value="<?php echo htmlspecialchars($class); ?>">
      <input type="hidden" name="section" value="<?php echo htmlspecialchars($section); ?>">
      <input type="hidden" name="group" value="<?php echo htmlspecialchars($group); ?>">
      <button type="submit" class="btn btn-success school-btn me-md-2">
        <i class="fas fa-check-circle me-1"></i>Confirm Import
      </button>
    </form>
    <a href="admin.php" class="btn btn-secondary">
      <i class="fas fa-times-circle me-1"></i>Cancel
    </a>
  </div>
</div>
</body>
</html>