<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

session_start();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

file_put_contents("log.txt", "Session ID: " . ($_SESSION['user_id'] ?? 'none') . "\n", FILE_APPEND);


if ($action === 'save_token' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? null;

    if (!empty($token) && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        NotificationController::saveToken($user_id, $token);
        echo json_encode(['success' => true]);
        exit;
    }
}

//get user notifications
if ($action === 'get_notifications' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $notifications = NotificationController::getUserNotifications($user_id);
        echo json_encode(['success' => true, 'notifications' => $notifications]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    }
}

// echo json_encode(['success' => false, 'message' => 'Invalid request']);
