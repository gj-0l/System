<?php
ob_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EquipmentController.php';

session_start();
header('Content-Type: application/json');

// التحقق من صلاحية الأدمن
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    respond(['success' => false, 'message' => 'غير مصرح لك']);
}

// دالة للمخرجات بشكل موحد
function respond($data)
{
    ob_clean();
    echo json_encode($data);
    ob_end_flush();
    exit;
}

// معالجة طلبات GET (جلب معدة حسب ID)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        respond(['success' => false, 'message' => 'معرّف المعدة غير صالح']);
    }

    $result = EquipmentController::get($id);
    respond($result);
}

// معالجة طلبات POST (store, update, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') === false) {
        respond(['success' => false, 'message' => 'نوع المحتوى غير مدعوم']);
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'store':
            $name = trim($input['equipment_name'] ?? '');
            $code = trim($input['equipment_code'] ?? '');
            $desc = trim($input['description'] ?? '');

            if ($name === '' || $code === '') {
                respond(['success' => false, 'message' => 'الاسم ورقم المعدة مطلوبان']);
            }

            $result = EquipmentController::create($name, $code, $desc);
            respond($result);

        case 'update':
            $id = intval($input['id'] ?? 0);
            $name = trim($input['equipment_name'] ?? '');
            $code = trim($input['equipment_code'] ?? '');
            $desc = trim($input['description'] ?? '');

            if ($id <= 0 || $name === '' || $code === '') {
                respond(['success' => false, 'message' => 'جميع الحقول مطلوبة لتحديث المعدة']);
            }

            $result = EquipmentController::update($id, $name, $code, $desc);
            respond($result);

        case 'delete':
            $id = intval($input['id'] ?? 0);

            if ($id <= 0) {
                respond(['success' => false, 'message' => 'معرّف المعدة غير صالح']);
            }

            $result = EquipmentController::delete($id);
            respond($result);

        default:
            respond(['success' => false, 'message' => 'عملية غير معروفة']);
    }
}

// الطلب غير مدعوم
respond(['success' => false, 'message' => 'طريقة الطلب غير مدعومة']);
