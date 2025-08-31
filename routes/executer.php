<?php
ob_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EquipmentController.php';

session_start();
header('Content-Type: application/json');

if (
    !isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'execution'
) {
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

// معالجة طلبات GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = intval($_GET['id'] ?? 0);

    if ($id > 0) {
        // إذا انطيت id → يجيب معدة وحدة
        $result = EquipmentController::get($id);
        respond($result);
    } else {
        // إذا ماكو id → يجيب كل المعدات مع حالتها اليوم
        $result = EquipmentController::listWithTodayStatus();
        respond($result);
    }
}

// الطلب غير مدعوم
respond(['success' => false, 'message' => 'طريقة الطلب غير مدعومة']);
