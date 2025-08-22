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

        // استخدام JOIN للحصول على بيانات الفحص مع اسم المعدة دفعة واحدة
        $query = "
        SELECT 
            ci.id,
            ci.test_name,
            ci.initial_action,
            ci.default_status,
            ci.equipment_id,
            e.equipment_name,
            e.equipment_code
        FROM checklist_items ci
        LEFT JOIN equipment e ON ci.equipment_id = e.id
        ORDER BY ci.id ASC
    ";

        $stmt = $db->query($query);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $items;
    }


    public static function getChecklistItems($equipment_id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM checklist_items WHERE equipment_id = ?");
        $stmt->execute([$equipment_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
    }

    public static function get($id)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM checklist_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        //get equipment details by equipment_id
        $equipment_id = $item['equipment_id'];
        $stmt = $db->prepare("SELECT * FROM equipment WHERE id = ?");
        $stmt->execute([$equipment_id]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            return [
                'success' => true,
                'item' => $item,
                'equipment' => $equipment
            ];
        } else {
            return [
                'success' => false,
                'message' => 'عنصر القائمة غير موجود'
            ];
        }
    }

    public static function update($id, $equipment_id, $test_name, $initial_action, $default_status)
    {
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("UPDATE checklist_items SET equipment_id = ?, test_name = ?, initial_action = ?, default_status = ? WHERE id = ?");
            $stmt->execute([$equipment_id, $test_name, $initial_action, $default_status, $id]);

            return [
                'success' => true,
                'message' => 'تم التحديث بنجاح'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()
            ];
        }
    }

    public static function delete($id)
    {
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("DELETE FROM checklist_items WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'تم حذف العنصر بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'العنصر غير موجود أو تم حذفه مسبقاً'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()
            ];
        }
    }
}
