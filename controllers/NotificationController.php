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
    public static function sendNotification($title, $body, $target_user_ids = [], $url = '', $sender_id = null)
    {
        $db = Database::getInstance()->getConnection();

        if (empty($title) || empty($target_user_ids)) {
            return ['success' => false, 'message' => 'Title and target users are required'];
        }

        $created_at = date('Y-m-d H:i:s');

        // Save notification in DB for each user
        $stmt = $db->prepare("INSERT INTO notifications (title, body, user_id, url, is_opened, created_at, sender_id) VALUES (?, ?, ?, ?, 0, ?, ?)");
        foreach ($target_user_ids as $user_id) {
            $stmt->execute([$title, $body, $user_id, $url, $created_at, $sender_id]);
        }

        // Fetch target tokens
        $in = str_repeat('?,', count($target_user_ids) - 1) . '?';
        $tokenStmt = $db->prepare("SELECT token FROM users WHERE id IN ($in) AND token IS NOT NULL");
        $tokenStmt->execute($target_user_ids);
        $tokens = array_column($tokenStmt->fetchAll(PDO::FETCH_ASSOC), 'token');

        // Send to Firebase
        if (!empty($tokens)) {
            self::sendToFirebase($tokens, $title, $body, $url);
        }

        return ['success' => true];
    }

    // Helper to send push notification
    private static function sendToFirebase($tokens, $title, $body, $url = '')
    {
        $serverKey = FIREBASE_SERVER_KEY; // Put this in your config.php

        $payload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'click_action' => $url,
            ],
            'data' => [
                'url' => $url
            ]
        ];

        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // Optional: mark as opened
    public static function markAsOpened($notification_id, $user_id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_opened = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
    }

    // Optional: get user notifications
    public static function getUserNotifications($user_id, $is_oppened = false)
    {
        $db = Database::getInstance()->getConnection();
        $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
        if ($is_oppened) {
            $query .= " AND is_opened = 1";
        }
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
