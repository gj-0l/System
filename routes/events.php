<?php
// routes/events.php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../controllers/ChecklistController.php';
require_once __DIR__ . '/../controllers/CalenderController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
header('Content-Type: application/json; charset=utf-8');
session_start();

// Optional: check auth here
// if (!isset($_SESSION['auth_token'])) { echo json_encode(['error'=>'Unauthorized']); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? null);

if ($method === 'GET') {
    switch ($action) {
        case 'types':
            $date = $_GET['date'] ?? date('Y-m-d');
            echo json_encode(ChecklistController::getNotRejectedEquipments($date));
            break;

        case 'events':
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            echo json_encode(CalendarController::getEvents($start, $end));
            break;

        default:
            echo json_encode(['error' => 'Invalid GET action']);
    }
    exit;
}


if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    switch ($action) {
        case 'add':
            $res = CalendarController::addEvent($input);
            echo json_encode($res);
            // ✅ إرسال إشعار إذا تم الحفظ بنجاح
            if ($res['success']) {
                NotificationController::sendNotification(
                    'حدث جديد',
                    "تمت إضافة حدث جديد: {$input['title']}",
                    [3], // ← استبدله بمعرف الشخص اللي يستقبل الإشعار
                    BASE_URL . '/public/requester/event?id=0011219736.php',
                    $_SESSION['user_id'] ?? null
                );
            }

            break;
        case 'delete':
            $id = $input['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Missing id']);
                exit;
            }
            echo json_encode(CalendarController::deleteEvent($id));
            break;
        default:
            echo json_encode(['error' => 'Invalid POST action']);
    }
    exit;
}

echo json_encode(['error' => 'Unsupported method']);
