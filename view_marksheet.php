<?php
// view_marksheet.php
require_once 'config.php';
require_once 'functions.php';

$class = $_GET['class'] ?? '';
$section = $_GET['section'] ?? '';
$group = $_GET['group'] ?? '';
$roll = $_GET['roll'] ?? '';

$results = [];
$student = null;

if($class && $roll) {
    $results = get_student_results($pdo, $class, $section, $group, $roll);
    if($results) {
        $student = [
            'name' => $results[0]['name'],
            'roll' => $results[0]['roll'],
            'class' => $results[0]['class'],
            'section' => $results[0]['section'],
            'group' => $results[0]['group']
        ];

        $subjects = [];
        $total_marks = 0;
        $total_max = 0;
        foreach($results as $r) {
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
    } else {
        die("No results found for this student.");
    }
} else {
    die("Invalid parameters.");
}

// School information - customize these as needed
$school_name = "Rajakhali Faizunnessa High School & College";
$school_address = "Rajakhali, Pekua, Cox's Bazar";
$exam_name = "Half Yearly Examination 2025";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Marksheet - <?php echo htmlspecialchars($student['name']); ?></title>
<link rel="icon" href="logo.png" type="image/png">

<!-- for Apple devices -->
<link rel="apple-touch-icon" href="logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
@page {
    size: A4;
    margin: 0;
}

@media print {
    body {
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .marksheet-container {
        margin: 0;
        padding: 0;
        box-shadow: none;
        border: none;
    }
    
    .no-print {
        display: none !important;
    }
    
    /* Ensure everything fits on one page */
    .marksheet-container {
        min-height: 29.5cm !important;
        max-height: 29.5cm !important;
        overflow: hidden !important;
    }
}

body {
    font-family: 'Times New Roman', serif;
    background: #f5f5f5;
    margin: 0;
    padding: 20px;
}

.marksheet-container {
    width: 21cm;
    min-height: 29.7cm;
    margin: 0 auto;
    background: white;
    position: relative;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    /* Gorgeous frame design */
    border: 15px solid transparent;
    border-image: linear-gradient(45deg, #2c3e50, #34495e, #2c3e50);
    border-image-slice: 1;
    padding: 10px;
}

/* Inner decorative border */
.marksheet-container::before {
    content: '';
    position: absolute;
    top: 5px;
    left: 5px;
    right: 5px;
    bottom: 5px;
    border: 2px solid #bdc3c7;
    pointer-events: none;
}

.watermark {
    position: absolute;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    opacity: 0.08;
    font-size: 80px;
    color: #2c3e50;
    z-index: 1;
    white-space: nowrap;
    font-weight: bold;
}

/* Logo watermark */
.logo-watermark {
    position: absolute;
    top: 30%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.1;
    z-index: 1;
    font-size: 120px;
    color: #2c3e50;
}

.logo-watermark i {
    font-size: 140px;
}

.header {
    text-align: center;
    padding: 15px 20px;
    border-bottom: 3px double #2c3e50;
    position: relative;
    z-index: 2;
    margin-bottom: 10px;
}

.school-name {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
    text-transform: uppercase;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.school-address {
    font-size: 14px;
    color: #34495e;
    margin-bottom: 8px;
    font-weight: bold;
}

.exam-name {
    font-size: 18px;
    font-weight: bold;
    color: #c0392b;
    margin-bottom: 10px;
    text-decoration: underline;
}

.student-info {
    padding: 15px 20px;
    border-bottom: 2px solid #bdc3c7;
    margin-bottom: 10px;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.info-table td {
    padding: 8px 12px;
    border: 1px solid #bdc3c7;
    font-size: 15px;
}

.info-table td:first-child {
    font-weight: bold;
    background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
    width: 25%;
    color: #2c3e50;
    text-align: right;
    padding-right: 20px;
}

.info-table td:last-child {
    font-weight: bold;
    color: #c0392b;
    background: #f8f9fa;
    padding-left: 20px;
}

.marks-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    font-size: 14px;
}

.marks-table th {
    background: linear-gradient(135deg, #34495e, #2c3e50);
    color: white;
    padding: 10px 6px;
    border: 1px solid #2c3e50;
    text-align: center;
    font-weight: bold;
}

.marks-table td {
    padding: 8px 6px;
    border: 1px solid #bdc3c7;
    text-align: center;
}

.marks-table tr:nth-child(even) {
    background: #f8f9fa;
}

.marks-table tr:hover {
    background: #e8f4f8;
}

.summary {
    padding: 12px 20px;
    background: linear-gradient(135deg, #ecf0f1, #dfe6e9);
    border-top: 2px solid #bdc3c7;
    border-bottom: 2px solid #bdc3c7;
    margin: 10px 0;
}

.summary-table {
    width: 100%;
    border-collapse: collapse;
}

.summary-table td {
    padding: 8px 12px;
    border: 1px solid #bdc3c7;
    font-weight: bold;
    font-size: 15px;
}

.summary-table td:first-child {
    background: linear-gradient(135deg, #34495e, #2c3e50);
    color: white;
    width: 30%;
    text-align: right;
    padding-right: 20px;
}

.summary-table td:last-child {
    background: #ffffff;
    color: #c0392b;
    font-size: 16px;
    padding-left: 20px;
}

.footer {
    padding: 15px 20px;
    text-align: center;
    margin-top: 10px;
}

.signature-area {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
}

.signature-box {
    text-align: center;
    width: 30%;
}

.signature-line {
    border-top: 2px solid #2c3e50;
    margin: 30px 0 5px 0;
    padding-top: 5px;
    font-weight: bold;
    color: #34495e;
}

.grade-scale {
    margin-top: 15px;
    font-size: 11px;
    text-align: left;
}

.grade-scale table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
}

.grade-scale th, .grade-scale td {
    border: 1px solid #bdc3c7;
    padding: 2px 6px;
    text-align: center;
}

.grade-scale th {
    background: #34495e;
    color: white;
}

.print-btn {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
}

/* Compact layout for better fitting */
.compact .marks-table td {
    padding: 6px 4px;
    font-size: 13px;
}

.compact .info-table td {
    padding: 6px 10px;
    font-size: 14px;
}

.compact .summary-table td {
    padding: 6px 10px;
    font-size: 14px;
}

/* Student name styling */
.student-name {
    font-size: 20px;
    font-weight: bold;
    color: #2c3e50;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Ensure one page printing */
.one-page {
    page-break-after: avoid;
    page-break-inside: avoid;
}

/* Print optimizations */
@media print {
    .marksheet-container {
        border: 10px solid #2c3e50 !important;
        padding: 5px !important;
        margin: 0 !important;
    }
    
    .header {
        padding: 10px 15px !important;
    }
    
    .student-info {
        padding: 10px 15px !important;
    }
    
    .footer {
        padding: 10px 15px !important;
    }
    
    /* Reduce spacing for print */
    .signature-area {
        margin-top: 20px !important;
    }
    
    .signature-line {
        margin: 20px 0 5px 0 !important;
    }
}
</style>
</head>
<body>
<div class="print-btn no-print">
    <button onclick="window.print()" class="btn btn-primary btn-lg">
        <i class="fas fa-print me-2"></i>Print Marksheet(Desktop)
    </button>
</div>

<div class="marksheet-container compact one-page">
    <!-- Watermark -->
    <div class="watermark"><?php echo $school_name; ?></div>
    
    <!-- Logo Watermark -->
    <div class="logo-watermark">
        <img src="logo.png"/>
    </div>
    
    <!-- Header -->
    <div class="header">
        <div class="school-name"><?php echo $school_name; ?></div>
        <div class="school-address"><?php echo $school_address; ?></div>
        <div class="exam-name"><?php echo $exam_name; ?></div>
        <div style="font-size: 16px; font-weight: bold; color: #2c3e50;">STUDENT MARKSHEET</div>
    </div>
    
    <!-- Student Information -->
    <div class="student-info">
        <table class="info-table">
            <tr>
                <td>Student Name</td>
                <td><span class="student-name"><?php echo htmlspecialchars($student['name']); ?></span></td>
            </tr>
            <tr>
                <td>Roll Number</td>
                <td><strong style="color: #c0392b; font-size: 16px;"><?php echo htmlspecialchars($student['roll']); ?></strong></td>
            </tr>
            <tr>
                <td>Class</td>
                <td><strong><?php echo htmlspecialchars($student['class']); ?></strong></td>
            </tr>
            <?php if($student['section']): ?>
            <tr>
                <td>Section</td>
                <td><strong><?php echo htmlspecialchars($student['section']); ?></strong></td>
            </tr>
            <?php endif; ?>
            <?php if($student['group']): ?>
            <tr>
                <td>Group</td>
                <td><strong><?php echo htmlspecialchars($student['group']); ?></strong></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <!-- Marks Table -->
    <div style="padding: 0 15px;">
        <table class="marks-table">
            <thead>
                <tr>
                    <th width="5%">SL</th>
                    <th width="45%">Subject</th>
                    <th width="12%">Marks</th>
                    <th width="12%">Max Marks</th>
                    <th width="13%">Percentage</th>
                    <th width="13%">Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($subjects_with_grades as $index => $s): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td style="text-align: left; padding-left: 12px;"><?php echo htmlspecialchars($s['subject']); ?></td>
                    <td><strong><?php echo htmlspecialchars($s['marks']); ?></strong></td>
                    <td><?php echo htmlspecialchars($s['max']); ?></td>
                    <td><strong><?php echo htmlspecialchars($s['percentage']); ?>%</strong></td>
                    <td><strong style="color: #c0392b;"><?php echo htmlspecialchars($s['grade']); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Summary -->
    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>Total Marks Obtained</td>
                <td><?php echo $total_marks; ?> out of <?php echo $total_max; ?></td>
            </tr>
            <tr>
                <td>Overall Percentage</td>
                <td><?php echo $percentage; ?>%</td>
            </tr>
            <tr>
                <td>Final Grade</td>
                <td><?php echo $overall['final']; ?> (GPA: <?php echo $overall['gpa']; ?>)</td>
            </tr>
            <tr>
                <td>Result Status</td>
                <td style="color: <?php echo ($overall['final'] != 'F') ? '#27ae60' : '#c0392b'; ?>; font-weight: bold;">
                    <?php echo ($overall['final'] != 'F') ? 'PASS' : 'FAIL'; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Footer with Signatures -->
    <div class="footer">
        <div class="signature-area">
            <div class="signature-box">
                <div class="signature-line" style="width: 80%;"></div>
                <div>Class Teacher</div>
            </div>
            <div class="signature-box">
                <div class="signature-line" style="width: 80%;"></div>
                <div>Principal</div>
            </div>
            <div class="signature-box">
                <div class="signature-line" style="width: 80%;"></div>
                <div>Guardian's Signature</div>
            </div>
        </div>
        
        <div style="margin-top: 10px; font-size: 11px; color: #7f8c8d;">
            Generated on: <?php echo date('d/m/Y'); ?> | Sayed Mahbub Salman
        </div>
    </div>
</div>

<script>
// Auto-print option (optional)
window.onload = function() {
    // Uncomment the line below if you want to auto-print when page loads
    // window.print();
};

// Ensure proper printing and one page only
window.onafterprint = function() {
    // Optional: Redirect back after printing
    // window.history.back();
};

// Additional script to ensure one-page printing
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.marksheet-container');
    const contentHeight = container.scrollHeight;
    const maxHeight = 29.5 * 37.8; // 29.5cm in pixels (approx)
    
    if (contentHeight > maxHeight) {
        // Reduce font sizes slightly if content is too long
        const elements = container.querySelectorAll('*');
        elements.forEach(el => {
            const style = window.getComputedStyle(el);
            const fontSize = parseFloat(style.fontSize);
            if (fontSize > 12) {
                el.style.fontSize = (fontSize * 0.95) + 'px';
            }
        });
    }
});
</script>
</body>
</html>