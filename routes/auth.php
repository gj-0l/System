<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';

// الحصول على قيمة action من GET أو POST (حسب الحاجة)
$action = $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === null) {
    // لو POST بدون action، اعتبرها طلب تسجيل دخول
    AuthController::login();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_users') {
    if (isset($_SESSION['user_id'])) {
        $users = AuthController::list();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'users' => $users]);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        exit();
    }
}

// إذا لم يكن أي من الشروط السابقة، أعد التوجيه لصفحة تسجيل الدخول
header("Location: /login.php");
exit();
