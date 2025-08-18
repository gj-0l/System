<?php

require_once __DIR__ . '/../controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::login();

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'list') {
    $users = AuthController::list();
    header('Content-Type: application/json');
    echo json_encode($users);

} else {
    header("Location: /login.php");
    exit();
}
