<?php
require_once __DIR__ . "/../includes/init.php";
if (PHP_SAPI !== "cli") {
    die("This script is intended for CLI/one-time setup only.");
}
try {
$pdo = getDbConnection();
    $fullName = readline("Admin full name: ") ?: "Administrator";
    $username = readline("Admin username: ") ?: "admin";
    $pass = readline("Admin password: ");
    if (empty($pass)) { echo "Password required"; exit(1); }
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $upd = $pdo->prepare("UPDATE admins SET full_name = ?, password_hash = ? WHERE username = ?");
        $upd->execute([$fullName, $hash, $username]);
        echo "Admin updated successfully.";
    } else {
        $ins = $pdo->prepare("INSERT INTO admins (full_name, username, password_hash) VALUES (?, ?, ?)");
        $ins->execute([$fullName, $username, $hash]);
        echo "Admin created successfully.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

