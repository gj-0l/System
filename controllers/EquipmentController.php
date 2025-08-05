<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';

class EquipmentController
{
    public static function storeAjax($name, $code, $description)
    {
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("INSERT INTO equipment (equipment_name, equipment_code, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $code, $description]);

            return [
                'success' => true,
                'message' => 'تمت إضافة المعدة بنجاح'
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

        $stmt = $db->query("SELECT * FROM equipment ORDER BY id ASC");
        $equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $equipments;
    }

    public static function deleteAjax($id)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM equipment WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'تم حذف المعدة بنجاح']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
        }
    }



}
