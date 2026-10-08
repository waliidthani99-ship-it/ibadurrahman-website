<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';
function csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}
function csrf_field() {
    $token = csrf_token();
    echo "<input type=\"hidden\" name=\"csrf_token\" value=\"$token\">";
}
function isLoggedIn() {
    return isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true;
}
function requireAdmin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, "UTF-8");
}
