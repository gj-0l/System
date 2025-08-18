<?php

require_once __DIR__ . '/../controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::login();
} else {
    header("Location: /login.php");
    exit();
}

if ($action === 'get_users' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['user_id'])) {
        $users = AuthController::list();
        echo json_encode(['success' => true, 'users' => $notifications]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    }
}
