<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['action']) && $input['action'] === 'register') {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');
        $type = trim($input['type'] ?? '');

        if ($name && $email && $password && $type) {
            echo json_encode(AuthController::add_user($name, $email, $password, $type));
        } else {
            echo json_encode(['success' => false, 'message' => 'جميع الحقول مطلوبة']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'عملية غير صحيحة']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'الطلب غير مسموح']);
}
