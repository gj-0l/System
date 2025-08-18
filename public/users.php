<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../config/config.php';

// فقط المسؤول يقدر يدخل
if (empty($_SESSION['auth_token'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$users = AuthController::list();

$error = $_SESSION['users_error'] ?? null;
$success = $_SESSION['users_success'] ?? false;
$old = $_SESSION['users_old'] ?? [];

unset($_SESSION['userserror'], $_SESSION['userssuccess'], $_SESSION['usersold']);

require_once __DIR__ . '/../views/admin/users.php';