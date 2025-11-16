<?php
// export_results.php (new)
require_once 'config.php';
require_once 'functions.php';
start_session_if_needed();
require_admin();

$class = $_GET['class'] ?? '';
$section = $_GET['section'] ?? '';
$group = $_GET['group'] ?? '';
$single_roll = $_GET['single_roll'] ?? '';

if (empty($class)) die('class required');

if (!empty($single_roll)) {
    // export single student as CSV (same header logic)
    // We'll reuse export_results_csv but then filter to a single roll by creating temp CSV and filtering rows
    $csv = export_results_csv($pdo, $class, $section, $group);
    // filter for the single roll
    $lines = explode("\n", trim($csv));
    if (count($lines) === 0) die('No data');
    $header = array_shift($lines);
    $out_lines = [$header];
    foreach ($lines as $ln) {
        $fields = str_getcsv($ln);
        if (isset($fields[0]) && $fields[0] == $single_roll) {
            $out_lines[] = $ln;
            break;
        }
    }
    $final = implode("\n", $out_lines);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="result_'.$class.'_'.$single_roll.'.csv"');
    echo $final;
    exit;
} else {
    $csv = export_results_csv($pdo, $class, $section, $group);
    $fname = 'results_'.$class.($section ? '_sec'.$section : '').($group ? '_grp'.$group : '').'.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    echo $csv;
    exit;
}
