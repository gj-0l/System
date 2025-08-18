<?php

function sendNotification($data)
{
    ob_start(); // ← يمنع أي مخرجات غير مرغوبة

    require_once __DIR__ . '/../core/Database.php';
    $pdo = Database::getInstance()->getConnection();

    $title = $data['title'] ?? '';
    $body = $data['body'] ?? '';
    $url = $data['url'] ?? null;
    $user_ids = $data['user_ids'] ?? [];
    $sender_id = $data['sender_id'] ?? null;

    if (empty($user_ids))
        return;

    $stmt = $pdo->prepare("
        INSERT INTO notifications (title, body, user_id, url, created_at, sender_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $now = date('Y-m-d H:i:s');
    foreach ($user_ids as $uid) {
        $stmt->execute([$title, $body, $uid, $url, $now, $sender_id]);
    }

    sendFirebaseNotification($title, $body, $user_ids, $url);

    ob_end_clean(); // ← تنظيف أي مخرجات
}

function sendFirebaseNotification($title, $body, $user_ids, $url = '')
{
    require __DIR__ . '/../config/firebase.php';
    $pdo = Database::getInstance()->getConnection();

    $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
    $tokens_stmt = $pdo->prepare("SELECT token FROM users WHERE id IN ($placeholders)");
    $tokens_stmt->execute($user_ids);
    $tokens = $tokens_stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$tokens)
        return;

    $payload = [
        "registration_ids" => $tokens,
        "notification" => [
            "title" => $title,
            "body" => $body,
            "click_action" => $url,
        ],
        "priority" => "high"
    ];

    $headers = [
        'Authorization: key=' . FIREBASE_SERVER_KEY,
        'Content-Type: application/json',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $result = curl_exec($ch);

    // اختياري: خزن النتيجة بملف للمراقبة إذا صار خلل
    if (curl_errno($ch)) {
        file_put_contents(__DIR__ . '/notification_errors.log', curl_error($ch) . "\n", FILE_APPEND);
    }

    curl_close($ch);
}
