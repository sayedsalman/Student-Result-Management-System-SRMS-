<?php
// admin_panel.php (updated)
require_once 'config.php';
start_session_if_needed();
require_admin();

// fetch uploads
$upstmt = $pdo->prepare("SELECT * FROM uploads ORDER BY created_at DESC");
$upstmt->execute();
$uploads = $upstmt->fetchAll();

// fetch students sample (or all if you want)
$stu_stmt = $pdo->query("SELECT id, roll, name, class, section, `group` FROM students ORDER BY class, section, `group`, roll LIMIT 500");
$students = $stu_stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<div class="container school-container">
  <div class="school-header">
    <h1><i class="fas fa-cog me-2"></i>Admin Panel</h1>
    <div class="school-badge">Management Console</div>
  </div>
  
  <div class="admin-nav">
    <a href="admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Upload</a>
    <a href="create_admin.php" class="btn btn-outline-secondary"><i class="fas fa-user-plus me-1"></i>Create Admin</a>
  </div>

  <div class="school-card">
    <div class="school-card-header">
      <i class="fas fa-file-upload me-2"></i>Uploaded CSV Files
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>ID</th><th>File Name</th><th>Class</th><th>Section</th><th>Group</th><th>Uploaded At</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($uploads as $u): ?>
            <tr>
              <td><?php echo $u['id']; ?></td>
              <td><?php echo htmlspecialchars($u['original_name']); ?></td>
              <td><span class="badge-school">Class <?php echo htmlspecialchars($u['class']); ?></span></td>
              <td><?php echo htmlspecialchars($u['section'] ?: 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($u['group'] ?: 'N/A'); ?></td>
              <td><?php echo date('M j, Y g:i A', strtotime($u['created_at'])); ?></td>
              <td>
                <a class="btn btn-sm btn-primary" href="download_upload.php?id=<?php echo $u['id']; ?>">
                  <i class="fas fa-download me-1"></i>Download
                </a>
                <a class="btn btn-sm btn-success" href="export_results.php?class=<?php echo urlencode($u['class']); ?>&section=<?php echo urlencode($u['section']); ?>&group=<?php echo urlencode($u['group']); ?>">
                  <i class="fas fa-file-export me-1"></i>Export CSV
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="school-card">
    <div class="school-card-header">
      <i class="fas fa-users me-2"></i>Students (Recent 500)
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>Roll</th><th>Name</th><th>Class</th><th>Section</th><th>Group</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $s): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($s['roll']); ?></strong></td>
              <td><?php echo htmlspecialchars($s['name']); ?></td>
              <td><span class="badge-school">Class <?php echo htmlspecialchars($s['class']); ?></span></td>
              <td><?php echo htmlspecialchars($s['section'] ?: 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($s['group'] ?: 'N/A'); ?></td>
              <td>
                <a class="btn btn-sm btn-info" href="search.php?class=<?php echo urlencode($s['class']); ?>&section=<?php echo urlencode($s['section']); ?>&group=<?php echo urlencode($s['group']); ?>&roll=<?php echo urlencode($s['roll']); ?>">
                  <i class="fas fa-eye me-1"></i>View
                </a>
                <a class="btn btn-sm btn-secondary" href="export_results.php?class=<?php echo urlencode($s['class']); ?>&section=<?php echo urlencode($s['section']); ?>&group=<?php echo urlencode($s['group']); ?>&single_roll=<?php echo urlencode($s['roll']); ?>">
                  <i class="fas fa-download me-1"></i>Export
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>