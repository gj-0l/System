<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';

require_once __DIR__ . '/../notifications/send_notification.php';


class EquipmentController
{
    public static function create($name, $code, $description)
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

    public static function get($id)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM equipment WHERE id = ?");
        $stmt->execute([$id]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($equipment) {
            return [
                'success' => true,
                'equipment' => $equipment
            ];
        } else {
            return [
                'success' => false,
                'message' => 'المعدة غير موجودة'
            ];
        }
    }

    public static function update($id, $name, $code, $description)
    {
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("UPDATE equipment SET equipment_name = ?, equipment_code = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $code, $description, $id]);

            return [
                'success' => true,
                'message' => 'تم تحديث المعدة بنجاح'
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
