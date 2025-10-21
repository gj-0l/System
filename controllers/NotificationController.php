<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';

class NotificationController
{

    public static function saveToken($user_id, $token)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET token = ? WHERE id = ?");
        return $stmt->execute([$token, $user_id]);
    }
    // Send and store notification
    public static function sendNotification($title, $body, $target_user_ids = [], $url = '', $sender_id = null, $target_type = null, $include_manager_of_sender = false)
    {
        $db = Database::getInstance()->getConnection();

        if (empty($title)) {
            return ['success' => false, 'message' => 'Title is required'];
        }

        // 🧩 تحديد المستلمين
        if (empty($target_user_ids)) {
            if (!$target_type) {
                return ['success' => false, 'message' => 'Either target_user_ids or target_type must be provided'];
            }

            if ($target_type === 'requester') {
                return ['success' => false, 'message' => 'For requester, you must provide target_user_ids'];
            }

            // جلب المستخدمين حسب النوع
            $stmt = $db->prepare("SELECT id FROM users WHERE type = ?");
            $stmt->execute([$target_type]);
            $target_user_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');

            if (empty($target_user_ids)) {
                return ['success' => false, 'message' => "No users found for type: $target_type"];
            }
        }

        $created_at = date('Y-m-d H:i:s');

        // ✅ إدراج الإشعارات في قاعدة البيانات
        $stmt = $db->prepare("INSERT INTO notifications (title, body, user_id, url, is_opened, created_at, sender_id) VALUES (?, ?, ?, ?, 0, ?, ?)");
        foreach ($target_user_ids as $user_id) {
            $stmt->execute([$title, $body, $user_id, $url, $created_at, $sender_id]);
        }

        // ✅ إضافة مدير المرسل كهدف إضافي في حال التفعيل
        if ($include_manager_of_sender && !empty($sender_id)) {
            $stmt = $db->prepare("SELECT u2.id 
                              FROM users u1 
                              JOIN users u2 ON u1.manager_id = u2.id 
                              WHERE u1.id = ? 
                              LIMIT 1");
            $stmt->execute([$sender_id]);
            $manager_id = $stmt->fetchColumn();

            if (!empty($manager_id)) {
                // أضف الإشعار إلى المدير أيضاً
                $stmt->execute([$title, $body, $manager_id, $url, $created_at, $sender_id]);
            }
        }

        // ✅ جلب الرموز FCM فقط للمستلمين الأصليين (بدون مدير المرسل)
        $in = str_repeat('?,', count($target_user_ids) - 1) . '?';
        $tokenStmt = $db->prepare("SELECT token FROM users WHERE id IN ($in) AND token IS NOT NULL");
        $tokenStmt->execute($target_user_ids);
        $tokens = array_column($tokenStmt->fetchAll(PDO::FETCH_ASSOC), 'token');

        // // إرسال إلى Firebase (اختياري)
        // if (!empty($tokens)) self::sendToFirebase($tokens, $title, $body, $url);

        return ['success' => true];
    }



    // Optional: mark as opened
    public static function markAsOpened($notification_id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_opened = 1 WHERE id = ?");
        $stmt->execute([$notification_id]);

        // return success response
        return true;
    }

    // Optional: get user notifications
    public static function getUserNotifications($user_id, $is_opened = false, $day = null)
    {
        $db = Database::getInstance()->getConnection();

        // ✅ استخدم التوقيت المحلي (العراق)
        date_default_timezone_set('Asia/Baghdad');

        // ✅ إذا ما تم تمرير يوم، استخدم اليوم الحالي
        if ($day === null) {
            $day = date('Y-m-d');
        }

        $query = "SELECT n.*, u.name AS sender_name
              FROM notifications n
              LEFT JOIN users u ON n.sender_id = u.id
              WHERE n.user_id = ?";

        // ✅ فلترة حسب حالة الفتح
        if ($is_opened !== null) {
            $query .= $is_opened ? " AND n.is_opened = 1" : " AND n.is_opened = 0";
        }

        // ✅ فلترة حسب تاريخ اليوم (من الحقل DATETIME)
        $query .= " AND DATE(n.created_at) = ?";

        $query .= " ORDER BY n.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $day]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
