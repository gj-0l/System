<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// فقط المسؤول يقدر يدخل
if (empty($_SESSION['auth_token'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$error = $_SESSION['user_error'] ?? null;
$success = $_SESSION['user_success'] ?? false;
$old = $_SESSION['user_old'] ?? [];

unset($_SESSION['user_error'], $_SESSION['user_success'], $_SESSION['user_old']);

require_once __DIR__ . '/../views/admin/users.php';