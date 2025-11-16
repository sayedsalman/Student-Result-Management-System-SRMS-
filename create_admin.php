<?php
// create_admin.php
require_once 'config.php';

if (php_sapi_name() === 'cli') {
    // CLI usage: php create_admin.php username password
    global $argv;
    if (!isset($argv[1]) || !isset($argv[2])) {
        echo "Usage: php create_admin.php username password\n"; exit;
    }
    $username = $argv[1]; $password = $argv[2];
} else {
    // simple browser form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
    } else {
        echo '<form method="post"><input name="username" placeholder="username"><br><input name="password" placeholder="password"><br><button>Create</button></form>';
        exit;
    }
}

if (empty($username) || empty($password)) {
    die('Provide username & password');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
try {
    $stmt->execute([$username, $hash]);
    echo "Admin created: {$username}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
