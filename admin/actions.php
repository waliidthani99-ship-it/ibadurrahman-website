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

// Build a safe redirect, preserving list filters when provided.
$return = isset($_POST["return"]) ? $_POST["return"] : "";
if ($return === "student" && $id > 0) {
    $redirect = "student.php?id=" . $id;
} else {
    $allowedKeys = ["status", "type", "class", "q", "page"];
    $query = [];
    foreach ($allowedKeys as $key) {
        $field = "ret_" . $key;
        if (isset($_POST[$field]) && $_POST[$field] !== "") {
            $query[$key] = $_POST[$field];
        }
    }
    $redirect = "registrations.php" . ($query ? "?" . http_build_query($query) : "");
}

$allowed = ["approve", "reject", "pending", "activate", "delete"];

try {
    if ($id > 0 && in_array($action, $allowed, true)) {
        if ($action === "delete") {
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: registrations.php");
            exit;
        }

        if ($action === "approve") {
            $pdo->beginTransaction();
            // Lock the row, generate the official number if missing (transaction-safe).
            $number = assign_registration_number_if_missing($pdo, $id);
            $stmt = $pdo->prepare("UPDATE students SET status = 'Approved', registration_number = ?, registered_at = COALESCE(registered_at, NOW()), updated_at = NOW() WHERE id = ?");
            $stmt->execute([$number, $id]);
            $pdo->commit();
        } else {
            $statusMap = ["reject" => "Rejected", "pending" => "Pending", "activate" => "Active"];
            $stmt = $pdo->prepare("UPDATE students SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$statusMap[$action], $id]);
        }
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Fall through to redirect; error is not exposed to the user.
}

header("Location: " . $redirect);
exit;
