<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

class EmailController
{
    public static function sendEmail($subject, $body, $target_user_ids = [], $target_type = null, $include_managers = false)
    {
        $db = Database::getInstance()->getConnection();

        // ✅ التحقق من وجود العنوان
        if (empty($subject)) {
            return ['success' => false, 'message' => 'Subject is required'];
        }

        $emails = [];

        // ✅ تحديد المستلمين
        if (empty($target_user_ids)) {
            if (!$target_type) {
                return ['success' => false, 'message' => 'Either target_user_ids or target_type must be provided'];
            }

            $stmt = $db->prepare("SELECT id, email FROM users WHERE type = ? AND email IS NOT NULL AND status = 'active'");
            $stmt->execute([$target_type]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $target_user_ids = array_column($users, 'id');
            $emails = array_column($users, 'email');

            if (empty($emails)) {
                return ['success' => false, 'message' => "No emails found for type: $target_type"];
            }
        } else {
            $in = str_repeat('?,', count($target_user_ids) - 1) . '?';
            $stmt = $db->prepare("SELECT id, email FROM users WHERE id IN ($in) AND email IS NOT NULL");
            $stmt->execute($target_user_ids);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $emails = array_column($users, 'email');

            if (empty($emails)) {
                return ['success' => false, 'message' => 'No valid emails found for given user_ids'];
            }
        }

        // ✅ جلب مدراء المستخدمين (إن وُجدوا)
        if ($include_managers) {
            $in = str_repeat('?,', count($target_user_ids) - 1) . '?';
            $stmt = $db->prepare("SELECT DISTINCT u2.email 
                              FROM users u 
                              JOIN users u2 ON u.manager_id = u2.id 
                              WHERE u.id IN ($in) 
                              AND u2.email IS NOT NULL 
                              AND u2.status = 'active'");
            $stmt->execute($target_user_ids);
            $manager_emails = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'email');

            // دمج البريد الإلكتروني للمدراء بدون تكرار
            $emails = array_unique(array_merge($emails, $manager_emails));
        }

        // ✅ إرسال الإيميل باستخدام PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // يمكنك تغييره إلى SMTP استضافتك
            $mail->SMTPAuth = true;
            $mail->Username = 'rano12ran67@gmail.com';  // ايميل الإرسال
            $mail->Password = 'vsjf ngxb cezp amfr';    // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('rano12ran67@gmail.com', 'System');

            // ✅ إضافة المستلمين
            foreach ($emails as $email) {
                $mail->addAddress($email);
            }

            // ✅ المحتوى
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return ['success' => true, 'message' => 'تم إرسال البريد بنجاح'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => "فشل الإرسال: {$mail->ErrorInfo}"];
        }
    }

}
