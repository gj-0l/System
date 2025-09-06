<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';


header('Content-Type: application/json');
session_start();

// Helper to return JSON response
function respond($data)
{
    echo json_encode($data);
    exit;
}

// POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'register':
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = trim($input['password'] ?? '');
            $type = trim($input['type'] ?? '');

            if ($name && $email && $password && $type) {
                $res = AuthController::add_user($name, $email, $password, $type);

                // if ($res['success']) {
                NotificationController::sendNotification(
                    'New join request',
                    "New join requested by: {$name}",
                    null, // ← استبدله بمعرف الشخص اللي يستقبل الإشعار
                    BASE_URL . '/public/users.php',
                    $_SESSION['user_id'] ?? null,
                    'admin',
                );
                respond($res);
                // }
            } else {
                respond(['success' => false, 'message' => 'جميع الحقول مطلوبة']);
            }
            break;

        case 'update':
            $id = intval($input['id'] ?? 0);
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $type = trim($input['type'] ?? '');
            $status = trim($input['status'] ?? '');
            $password = isset($input['password']) ? trim($input['password']) : null;

            if ($id && $name && $email && $type && $status) {
                respond(AuthController::update_user($id, $name, $email, $password, $type, $status));
            } else {
                respond(['success' => false, 'message' => 'جميع الحقول مطلوبة']);
            }
            break;

        default:
            respond(['success' => false, 'message' => 'عملية غير صحيحة']);
    }
}

// GET Requests
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_user') {
        $id = intval($_GET['id'] ?? 0);

        if ($id) {
            respond(AuthController::get_user($id));
        } else {
            respond(['success' => false, 'message' => 'معرّف المستخدم مطلوب']);
        }
    } else {
        respond(['success' => false, 'message' => 'عملية غير صحيحة']);
    }
}

// DELETE Requests
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = intval($input['id'] ?? 0);

    if ($id) {
        respond(AuthController::delete_user($id));
    } else {
        respond(['success' => false, 'message' => 'معرّف المستخدم مطلوب']);
    }
}

// Invalid Request Method
else {
    respond(['success' => false, 'message' => 'الطلب غير مسموح']);
}
