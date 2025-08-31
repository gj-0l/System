<?php

require_once __DIR__ . '/../core/Database.php';

class ChecklistController
{
    public static function submitChecklist()
    {
        $db = Database::getInstance()->getConnection();
        $data = json_decode(file_get_contents("php://input"), true);
        $date = date('Y-m-d');

        try {
            foreach ($data['status'] as $checklist_id => $status) {
                // تحقق إذا موجود إدخال لنفس المعدة اليوم
                $checkStmt = $db->prepare("
                SELECT COUNT(*) 
                FROM checklist_results 
                WHERE checklist_id = ? 
                  AND DATE(date) = ?
            ");
                $checkStmt->execute([$checklist_id, $date]);
                $exists = $checkStmt->fetchColumn();

                if ($exists > 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => "❌ لا يمكنك إضافة نتائج جديدة لنفس المعدة (ID: $checklist_id) بتاريخ اليوم $date"
                    ]);
                    throw new Exception("Duplicate entry for checklist_id $checklist_id on $date");
                }

                // إدخال جديد
                $stmt = $db->prepare("
                INSERT INTO checklist_results (checklist_id, date, status) 
                VALUES (?, ?, ?)
            ");
                $stmt->execute([$checklist_id, $date, $status]);
            }

            echo json_encode(['success' => true, 'message' => '✔️ تم حفظ النتائج بنجاح']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => '❌ خطأ أثناء الحفظ: ' . $e->getMessage()]);
        }
    }


    public static function getNotRejectedEquipments($date = null)
    {
        $db = Database::getInstance()->getConnection();

        if ($date === null) {
            $date = date('Y-m-d');
        }

        try {
            $sql = "
            SELECT e.*, COALESCE(MAX(cr.status), 'not_checked') AS status
            FROM equipment e
            LEFT JOIN checklist_items ci ON ci.equipment_id = e.id
            LEFT JOIN checklist_results cr 
                ON cr.checklist_id = ci.id AND DATE(cr.date) = ?
            GROUP BY e.id
            HAVING status != 'rejected'
            ORDER BY e.id ASC
        ";

            $stmt = $db->prepare($sql);
            $stmt->execute([$date]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $rows ?: [];
        } catch (PDOException $e) {
            error_log("ChecklistController::getEquipmentsNotRejectedByDate error: " . $e->getMessage());
            return [];
        }
    }



}
