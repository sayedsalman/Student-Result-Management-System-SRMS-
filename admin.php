<?php
// admin.php (updated)
require_once 'config.php';
start_session_if_needed();

$logged = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin - Upload Results</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<div class="container school-container">
 <div class="school-header">
    <h1><i class="fas fa-user-shield me-2"></i>Admin - Upload Results</h1>
    <div class="school-badge">R.F.N. HIGH SCHOOL</div>
 </div>

  <?php if (!$logged): ?>
    <div class="login-container">
      <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <form method="post" class="login-form">
        <div class="form-group">
          <i class="fas fa-user"></i>
          <input class="form-control" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
          <i class="fas fa-lock"></i>
          <input class="form-control" name="password" placeholder="Password" type="password" required>
        </div>
        <button class="btn btn-primary school-btn" name="login">Login</button>
      </form>
    </div>
  <?php else: ?>
    <div class="admin-nav">
      <a href="?logout=1" class="btn btn-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
      <a href="admin_panel.php" class="btn btn-secondary"><i class="fas fa-cog"></i> Admin Panel</a>
    </div>

    <form action="upload_preview.php" method="post" enctype="multipart/form-data" class="upload-form">
      <div class="row g-3">
        <div class="col-md-2">
          <label class="form-label">Class</label>
          <select name="class" class="form-select" id="classSelect" required>
            <option value="">Select Class</option>
            <option>6</option><option>7</option><option>8</option><option>9</option><option>10</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Section</label>
          <select name="section" class="form-select" id="sectionSelect">
            <option value="">Select Section</option>
            <option>A</option><option>B</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Group</label>
          <select name="group" class="form-select" id="groupSelect">
            <option value="">Select Group</option>
            <option>Science</option><option>Arts</option><option>Commerce</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">CSV File</label>
          <input type="file" name="csvfile" accept=".csv,text/csv" class="form-control" required>
        </div>
        <div class="col-md-2 align-self-end">
          <button class="btn btn-success w-100 school-btn"><i class="fas fa-upload"></i> Upload & Preview</button>
        </div>
      </div>
      <div class="mt-3">
        <small>CSV header MUST start with: <code>roll,name</code> then subject columns. Example: <code>roll,name,Bangla,English,Math,Science</code></small>
      </div>
    </form>
  <?php endif; ?>
</div>

<script>
document.getElementById('classSelect').addEventListener('change', function() {
    const classVal = this.value;
    const sectionSelect = document.getElementById('sectionSelect');
    const groupSelect = document.getElementById('groupSelect');
    
    // Reset and enable/disable based on class
    if (classVal === '9' || classVal === '10') {
        sectionSelect.disabled = false;
        groupSelect.disabled = false;
    } else if (classVal === '6' || classVal === '7' || classVal === '8') {
        sectionSelect.disabled = false;
        groupSelect.disabled = true;
        groupSelect.value = '';
    } else {
        sectionSelect.disabled = true;
        groupSelect.disabled = true;
        sectionSelect.value = '';
        groupSelect.value = '';
    }
});
</script>
</body>
</html>