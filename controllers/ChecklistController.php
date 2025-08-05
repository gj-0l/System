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
}
