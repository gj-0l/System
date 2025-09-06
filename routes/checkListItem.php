<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ChecklistItemController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

session_start();
header('Content-Type: application/json');

// ✅ التحقق من صلاحية الجلسة
if (empty($_SESSION['auth_token'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

// ✅ دالة موحدة للرد
function respond($data)
{
    echo json_encode($data);
    exit;
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

// ✅ GET: getById / list
// ✅ GET: getById / list / getByEquipment
if ($requestMethod === 'GET') {
    // ?id=5 → جلب فحص معين
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        if ($id <= 0) {
            respond(['success' => false, 'message' => 'معرّف الفحص غير صالح']);
        }

        $result = ChecklistItemController::get($id);
        respond($result);
    }

    // ?equipment_id=xx → جلب عناصر المعدة المحددة
    if (isset($_GET['equipment_id'])) {
        $equipment_id = intval($_GET['equipment_id']);
        if ($equipment_id <= 0) {
            respond(['success' => false, 'message' => 'معرّف المعدة غير صالح']);
        }

        $result = ChecklistItemController::getChecklistItems($equipment_id);
        respond($result);
    }

    // ?list → جلب جميع الفحوصات
    if (isset($_GET['list'])) {
        $result = ChecklistItemController::list();
        respond($result);
    }

    respond(['success' => false, 'message' => 'طلب غير صالح']);
}


// ✅ POST: store / update
if ($requestMethod === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') === false) {
        respond(['success' => false, 'message' => 'نوع المحتوى غير مدعوم']);
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'store':
            $equipment_id = trim($input['equipment_id'] ?? '');
            $test_name = trim($input['test_name'] ?? '');
            $initial_action = trim($input['initial_action'] ?? '');
            $default_status = trim($input['default_status'] ?? '');

            if ($equipment_id === '' || $test_name === '') {
                respond(['success' => false, 'message' => 'نوع الفحص والمعدة مطلوبان']);
            }

            $result = ChecklistItemController::store($equipment_id, $test_name, $initial_action, $default_status);

            // إرسال إشعار بعد الحفظ
            // if ($result['success']) {
            //     NotificationController::sendNotification(
            //         '📋 فحص جديد',
            //         "تمت إضافة فحص جديد: {$test_name}",
            //         [27, 24], // ← غيّر المعرفات حسب الحاجة
            //         BASE_URL . '/public/requester/event?id=0011219736.php',
            //         $_SESSION['user_id'] ?? null
            //     );
            // }

            respond($result);

        case 'update':
            $id = intval($input['id'] ?? 0);
            $equipment_id = trim($input['equipment_id'] ?? '');
            $test_name = trim($input['test_name'] ?? '');
            $initial_action = trim($input['initial_action'] ?? '');
            $default_status = trim($input['default_status'] ?? '');

            if ($id <= 0 || $equipment_id === '' || $test_name === '') {
                respond(['success' => false, 'message' => 'جميع الحقول مطلوبة لتحديث الفحص']);
            }

            $result = ChecklistItemController::update($id, $equipment_id, $test_name, $initial_action, $default_status);
            respond($result);

        // default:
        //     respond(['success' => false, 'message' => 'عملية غير صالحة']);
    }
}

// ✅ DELETE: حذف فحص
if ($requestMethod === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = intval($input['id'] ?? 0);

    // if ($id <= 0) {
    //     respond(['success' => false, 'message' => 'معرّف الفحص غير صالح']);
    // }

    ChecklistItemController::delete($id);
    respond(['success' => true, 'message' => 'تم حذف الفحص بنجاح']);
}

// ❌ غير ذلك
respond(['success' => false, 'message' => 'طريقة الطلب غير مدعومة']);
