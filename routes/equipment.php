<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EquipmentController.php';

session_start();
header('Content-Type: application/json');

// التحقق من صلاحية الأدمن
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents("php://input"), true);

        // حذف
        if (isset($input['action']) && $input['action'] === 'delete' && isset($input['id'])) {
            EquipmentController::deleteAjax($input['id']);
            exit;
        }

        // إضافة
        if (isset($input['action']) && $input['action'] === 'store') {
            $name = trim($input['equipment_name'] ?? '');
            $code = trim($input['equipment_code'] ?? '');
            $desc = trim($input['description'] ?? '');

            if ($name === '' || $code === '') {
                echo json_encode(['success' => false, 'message' => 'الاسم ورقم المعدة مطلوبان']);
                exit;
            }

            $result = EquipmentController::storeAjax($name, $code, $desc);
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
