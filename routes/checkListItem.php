<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ChecklistItemController.php';

session_start();
header('Content-Type: application/json');

// التحقق من صلاحية الأدمن
if (empty($_SESSION['auth_token'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents("php://input"), true);

        // حذف
        if (isset($input['action']) && $input['action'] === 'delete' && isset($input['id'])) {
            ChecklistItemController::delete($input['id']);
            exit;
        }

        // إضافة
        if (isset($input['action']) && $input['action'] === 'store') {
            $equipment_id = trim($input['equipment_id'] ?? '');
            $test_name = trim($input['test_name'] ?? '');
            $initial_action = trim($input['initial_action'] ?? '');
            $default_status = trim($input['default_status'] ?? '');

            if ($equipment_id === '' || $test_name === '') {
                echo json_encode(['success' => false, 'message' => 'نوع الفحص والمعدة مطلوبان']);
                exit;
            }

            $result = ChecklistItemController::store($equipment_id, $test_name, $initial_action, $default_status);
            echo json_encode($result);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'نوع المحتوى غير مدعوم']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مدعومة']);
