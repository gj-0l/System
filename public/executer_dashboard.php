<?php
session_start();
require_once __DIR__ . '/../controllers/EquipmentController.php';
require_once __DIR__ . '/../config/config.php';

// فقط المسؤول يقدر يدخل
if (empty($_SESSION['auth_token'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$equipment = EquipmentController::list();

$error = $_SESSION['equipment_error'] ?? null;
$success = $_SESSION['equipment_success'] ?? false;
$old = $_SESSION['equipment_old'] ?? [];

unset($_SESSION['equipment_error'], $_SESSION['equipment_success'], $_SESSION['equipment_old']);

require_once __DIR__ . '/../views/executer/equipments.php';