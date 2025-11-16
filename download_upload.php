<?php
// download_upload.php (new)
require_once 'config.php';
start_session_if_needed();
require_admin();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die('Invalid id');

$stmt = $pdo->prepare("SELECT * FROM uploads WHERE id = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();
if (!$u) die('Not found');

$path = __DIR__ . '/uploads/' . $u['filename'];
if (!is_file($path)) die('File missing');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'.basename($u['original_name']).'"');
readfile($path);
exit;
