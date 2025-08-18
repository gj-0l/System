<?php
ob_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EquipmentController.php';

session_start();
header('Content-Type: application/json');

// التحقق من صلاحية الأدمن
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك']);
    ob_end_flush();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (isset($input['action']) && $input['action'] === 'delete' && isset($input['id'])) {
            ob_clean();
            EquipmentController::deleteAjax($input['id']);
            ob_end_flush();
            exit;
        }

        if (isset($input['action']) && $input['action'] === 'store') {
            $name = trim($input['equipment_name'] ?? '');
            $code = trim($input['equipment_code'] ?? '');
            $desc = trim($input['description'] ?? '');

            if ($name === '' || $code === '') {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'الاسم ورقم المعدة مطلوبان']);
                ob_end_flush();
                exit;
            }

            $result = EquipmentController::storeAjax($name, $code, $desc);
            ob_clean();
            echo json_encode($result);
            ob_end_flush();
            exit;
        }

        ob_clean();
        echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
        ob_end_flush();
        exit;
    }

    ob_clean();
    echo json_encode(['success' => false, 'message' => 'نوع المحتوى غير مدعوم']);
    ob_end_flush();
    exit;
}

ob_clean();
echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مدعومة']);
ob_end_flush();
exit;
