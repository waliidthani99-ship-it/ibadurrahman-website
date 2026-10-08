<?php
require_once __DIR__ . "/../../includes/init.php";
function require_admin_login() {
    if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
        header("Location: login.php");
        exit;
    }
}

