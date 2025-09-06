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

        case 'all_events':
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            echo json_encode(CalendarController::getEvents($start, $end));
            break;
        case 'today_events':
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            echo json_encode(CalendarController::getEvents($start, $end, true));
            break;

        case 'event':
            $token = $_GET['token'] ?? null;
            if (!$token) {
                echo json_encode(['error' => 'Missing token']);
                exit;
            }
            $event = CalendarController::getEventByToken($token);
            echo json_encode($event);
            break;

        case 'events_count':
            echo json_encode(CalendarController::getEventsCountByDate());
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
                    "PR: {$input['token']}",
                    "Location: {$input['location']}",
                    null, // ← استبدله بمعرف الشخص اللي يستقبل الإشعار
                    BASE_URL . '/public/event.php?id=' . $input['token'],
                    $_SESSION['user_id'] ?? null,
                    'execution',
                );
            }
            break;

        case 'delete':
            $id = $input['id'] ?? null;
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Missing id']);
                exit;
            }
            echo json_encode(CalendarController::deleteEvent($id, $user_id));
            break;

        case 'start':
            $id = $input['id'] ?? null;
            $executerId = $_SESSION['user_id'] ?? null;
            $requesterId = $input['requesterId'] ?? null;

            if (!$id || !$executerId) {
                echo json_encode(['success' => false, 'message' => 'Missing id or executer']);
                exit;
            }
            $res = CalendarController::startEvent($id, $executerId);
            echo json_encode($res);

            if ($res['success']) {
                NotificationController::sendNotification(
                    'executer started',
                    "executer started working on your request {$res['token']}",
                    [$requesterId], // ← استبدله بمعرف الشخص اللي يستقبل الإشعار
                    BASE_URL . '/public/events.php',
                    $_SESSION['user_id'] ?? null,
                    'requester',
                );
            }
            break;

        case 'end':
            $id = $input['id'] ?? null;
            $executerId = $_SESSION['user_id'] ?? null;
            if (!$id || !$executerId) {
                echo json_encode(['success' => false, 'message' => 'Missing id or executer']);
                exit;
            }
            echo json_encode(CalendarController::endEvent($id));
            break;

        case 'cancel':
            $id = $input['id'] ?? null;
            $reason = $input['reason'] ?? null;
            $executerId = $_SESSION['user_id'] ?? null;
            $requesterId = $input['requesterId'] ?? null;

            if (!$id || !$executerId) {
                echo json_encode(['success' => false, 'message' => 'Missing id or executer']);
                exit;
            }
            if (!$reason) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'the cancellaction reason is required']);
                exit;
            }

            $res = CalendarController::cancelEvent($id, $executerId, $reason);
            echo json_encode($res);

            if ($res['success']) {
                NotificationController::sendNotification(
                    'request cancelled',
                    "executer cancelled your request {$res['token']}",
                    [$requesterId], // ← استبدله بمعرف الشخص اللي يستقبل الإشعار
                    BASE_URL . '/public/events.php',
                    $_SESSION['user_id'] ?? null,
                    'requester',
                );
            }
            break;

        default:
            echo json_encode(['error' => 'Invalid POST action']);
    }
    exit;
}

echo json_encode(['error' => 'Unsupported method']);
