<?php
require_once __DIR__ . "/includes/auth.php";
require_admin_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registrations.php");
    exit;
}
if (!isset($_POST["csrf_token"]) || !isset($_SESSION["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
    header("Location: registrations.php");
    exit;
}

$pdo = getDbConnection();
$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
$action = isset($_POST["action"]) ? $_POST["action"] : "";
$redirect = "registrations.php";
if (isset($_POST["return"]) && $_POST["return"] === "student" && $id > 0) {
    $redirect = "student.php?id=" . $id;
}

$allowed = ["approve", "reject", "pending", "delete"];
if ($id > 0 && in_array($action, $allowed, true)) {
    if ($action === "delete") {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: registrations.php");
        exit;
    }
    $statusMap = ["approve" => "Approved", "reject" => "Rejected", "pending" => "Pending"];
    $stmt = $pdo->prepare("UPDATE students SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$statusMap[$action], $id]);
}

header("Location: " . $redirect);
exit;