<?php
// search.php (updated) - FIXED RESULT STATUS LOGIC
require_once 'config.php';
require_once 'functions.php';

$class = $_GET['class'] ?? '';
$section = $_GET['section'] ?? '';
$group = $_GET['group'] ?? '';
$roll = $_GET['roll'] ?? '';
$student = null;
$results = [];

if ($class && $roll) {
    $results = get_student_results($pdo, $class, $section, $group, $roll);
    if ($results) {
        $student = [
            'name' => $results[0]['name'],
            'roll' => $results[0]['roll'],
            'class' => $results[0]['class'],
            'section' => $results[0]['section'],
            'group' => $results[0]['group']
        ];

        // Prepare subjects array
        $subjects = [];
        $total_marks = 0;
        $total_max = 0;
        foreach ($results as $r) {
            $subjects[] = [
                'subject' => $r['subject_name'],
                'marks' => floatval($r['marks']),
                'max' => floatval($r['max_marks'])
            ];
            $total_marks += floatval($r['marks']);
            $total_max += floatval($r['max_marks']);
        }
        $percentage = $total_max > 0 ? round(($total_marks/$total_max)*100,2) : 0;
        $subjects_with_grades = compute_subject_grades($subjects);
        $overall = compute_overall_grade($subjects_with_grades);
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Search Result - RFNHSC</title>
  <link rel="icon" href="logo.png" type="image/png">

<!-- for Apple devices -->
<link rel="apple-touch-icon" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<div class="container school-container">
  <div class="school-header">
    <h1><i class="fas fa-search me-2"></i>Search Student Result</h1>
    <div class="school-badge">RAJAKHALI FAIZUN NESSA HIGH SCHOOL & COLLEGE</div>
    
  </div>

  <form method="get" class="search-form row g-3">
    <div class="col-md-2">
      <label class="form-label">Class</label>
      <select class="form-select" name="class" required>
        <option value="">Select Class</option>
        <option value="6" <?php echo $class=='6'?'selected':''; ?>>Class 6</option>
        <option value="7" <?php echo $class=='7'?'selected':''; ?>>Class 7</option>
        <option value="8" <?php echo $class=='8'?'selected':''; ?>>Class 8</option>
        <option value="9" <?php echo $class=='9'?'selected':''; ?>>Class 9</option>
        <option value="10" <?php echo $class=='10'?'selected':''; ?>>Class 10</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Section</label>
      <select class="form-select" name="section">
        <option value="">Select Section</option>
        <option value="A" <?php echo $section=='A'?'selected':''; ?>>Section A</option>
        <option value="B" <?php echo $section=='B'?'selected':''; ?>>Section B</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Group</label>
      <select class="form-select" name="group">
        <option value="">Select Group</option>
        <option value="Science" <?php echo $group=='Science'?'selected':''; ?>>Science</option>
        <option value="Arts" <?php echo $group=='Arts'?'selected':''; ?>>Arts</option>
        <option value="Commerce" <?php echo $group=='Commerce'?'selected':''; ?>>Commerce</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Roll Number</label>
      <input class="form-control" name="roll" placeholder="Enter Roll Number" required value="<?php echo htmlspecialchars($roll); ?>">
    </div>
    <div class="col-md-3 align-self-end">
      <button class="btn btn-primary w-100 school-btn">
        <i class="fas fa-search me-1"></i>Search Result
      </button>
    </div>
  </form>

  <?php if($student && $results): ?>
    <div class="text-center mb-4">
      <a href="view_marksheet.php?class=<?php echo urlencode($class); ?>&section=<?php echo urlencode($section); ?>&group=<?php echo urlencode($group); ?>&roll=<?php echo urlencode($roll); ?>" class="btn btn-success school-btn">
        <i class="fas fa-print me-2"></i>View / Print Marksheet
      </a>
    </div>

    <div class="school-card">
      <div class="school-card-header">
        Results for <?php echo htmlspecialchars($student['name']); ?> (Roll: <?php echo htmlspecialchars($student['roll']); ?>)
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Subject</th>
                <th>Marks</th>
                <th>Max</th>
                <th>Percentage</th>
                <th>Grade</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($subjects_with_grades as $s): ?>
              <tr>
                <td><?php echo htmlspecialchars($s['subject']); ?></td>
                <td><?php echo htmlspecialchars($s['marks']); ?></td>
                <td><?php echo htmlspecialchars($s['max']); ?></td>
                <td><?php echo htmlspecialchars($s['percentage']); ?>%</td>
                <td><span class="badge-school"><?php echo htmlspecialchars($s['grade']); ?></span></td>
                <td><?php echo ($s['is_pass']) ? '<span class="text-success">Pass</span>' : '<span class="text-danger">Fail</span>'; ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot class="table-dark">
              <tr>
                <th>Total</th>
                <th><?php echo $total_marks; ?></th>
                <th><?php echo $total_max; ?></th>
                <th><?php echo $percentage; ?>%</th>
                <th colspan="2">GPA: <?php echo $overall['gpa']; ?> / Grade: <?php echo $overall['final']; ?></th>
              </tr>
              <tr>
                <th colspan="6" class="text-center <?php echo $overall['is_pass'] ? 'text-success' : 'text-danger'; ?>">
                  <strong>RESULT: <?php echo $overall['is_pass'] ? 'PASS' : 'FAIL'; ?></strong>
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
        
        <div class="progress">
          <div class="progress-bar <?php echo $overall['is_pass'] ? 'bg-success' : 'bg-danger'; ?>" role="progressbar" style="width: <?php echo $percentage; ?>%" 
               aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
            <?php echo $percentage; ?>%
          </div>
        </div>
      </div>
    </div>
  <?php elseif($class && $roll): ?>
    <div class="alert alert-warning text-center">
      <i class="fas fa-exclamation-triangle me-2"></i>No results found for the provided criteria.
    </div>
  <?php endif; ?>
</div>

<div class="developer-footer">
            <div class="school-name">Sayed Mahbub Salman</div>
            <div class="developer-title">Designer & Developer</div>
        </div>
</body>
</html>