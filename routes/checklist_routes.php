<?php
require_once __DIR__ . '/../controllers/ChecklistController.php';
require_once __DIR__ . '/../controllers/EquipmentController.php';
require_once __DIR__ . '/../controllers/ChecklistItemController.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getEquipments':
        echo json_encode(EquipmentController::list());
        break;
    case 'getChecklist':
        $id = $_GET['id'] ?? 0;
        ChecklistItemController::getChecklistItems($id);
        break;
    case 'submitChecklist':
        ChecklistController::submitChecklist();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}
