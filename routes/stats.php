<?php
// routes/stats.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/StatsController.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

// التحقق من تسجيل الدخول
if (empty($_SESSION['auth_token'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك بالوصول']);
    exit;
}

$action = $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'daily':
            $date = $_GET['date'] ?? date('Y-m-d');
            echo json_encode(StatsController::getDailyStats($date));
            break;

        case 'monthly':
            $month = $_GET['month'] ?? date('Y-m');
            echo json_encode(StatsController::getMonthlyStats($month));
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'عملية غير صالحة']);
            break;
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مدعومة']);
