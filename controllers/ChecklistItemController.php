<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';

class ChecklistItemController
{
    public static function store($equipment_id, $test_name, $initial_action, $default_status)
    {
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("INSERT INTO checklist_items (equipment_id, test_name, initial_action, default_status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$equipment_id, $test_name, $initial_action, $default_status]);

            return [
                'success' => true,
                'message' => 'تمت الإضافة بنجاح'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الإضافة: ' . $e->getMessage()
            ];
        }
    }


    public static function list()
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("SELECT * FROM checklist_items ORDER BY id ASC");
        $checkListItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $checkListItems;
    }

    public static function getChecklistItems($equipment_id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM checklist_items WHERE equipment_id = ?");
        $stmt->execute([$equipment_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
    }

    public static function delete($id)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM checklist_items WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'تم الحذف بنجاح']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ في الحذف: ' . $e->getMessage()]);
        }
    }



}
