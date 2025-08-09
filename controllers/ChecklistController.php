<?php

require_once __DIR__ . '/../core/Database.php';

class ChecklistController
{
    public static function submitChecklist()
    {
        $db = Database::getInstance()->getConnection();
        $data = json_decode(file_get_contents("php://input"), true);
        $date = date('Y-m-d');

        foreach ($data['status'] as $checklist_id => $status) {
            $stmt = $db->prepare("INSERT INTO checklist_results (checklist_id, date, status) VALUES (?, ?, ?)");
            $stmt->execute([$checklist_id, $date, $status]);
        }

        echo json_encode(['success' => true, 'message' => '✔️ تم حفظ النتائج بنجاح']);
    }

    public static function getAcceptedEquipmentsByDate($date = null, $status = 'accepted')
    {
        $db = Database::getInstance()->getConnection();

        if ($date === null) {
            $date = date('Y-m-d');
        }

        try {
            $sql = "
                SELECT DISTINCT e.*
                FROM equipment e
                INNER JOIN checklist_items ci ON ci.equipment_id = e.id
                INNER JOIN checklist_results cr ON cr.checklist_id = ci.id
                WHERE cr.date = ? AND cr.status = ?
                ORDER BY e.id ASC
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([$date, $status]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $rows ?: [];
        } catch (PDOException $e) {
            // Log the error if you have a logger, otherwise silently return empty array
            error_log("ChecklistController::getAcceptedEquipmentsByDate error: " . $e->getMessage());
            return [];
        }
    }
}
