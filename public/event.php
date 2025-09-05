<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// فقط المسؤول يقدر يدخل
if (empty($_SESSION['auth_token'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$error = $_SESSION['event_error'] ?? null;
$success = $_SESSION['event_success'] ?? false;
$old = $_SESSION['event_old'] ?? [];

unset($_SESSION['event_error'], $_SESSION['event_success'], $_SESSION['equipment_old']);

require_once __DIR__ . '/../views/executer/event.php';