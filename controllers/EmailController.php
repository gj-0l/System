<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

class EmailController
{
    public static function sendEmail($subject, $body, $target_user_ids = [], $target_type = null)
    {
        $db = Database::getInstance()->getConnection();

        // تحقق من العنوان
        if (empty($subject)) {
            return ['success' => false, 'message' => 'Subject is required'];
        }

        // تحديد المستلمين
        if (empty($target_user_ids)) {
            if (!$target_type) {
                return ['success' => false, 'message' => 'Either target_user_ids or target_type must be provided'];
            }

            $stmt = $db->prepare("SELECT email FROM users WHERE type = ? AND email IS NOT NULL AND status = 'active'");
            $stmt->execute([$target_type]);
            $emails = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'email');

            if (empty($emails)) {
                return ['success' => false, 'message' => "No emails found for type: $target_type"];
            }
        } else {
            $in = str_repeat('?,', count($target_user_ids) - 1) . '?';
            $stmt = $db->prepare("SELECT email FROM users WHERE id IN ($in) AND email IS NOT NULL");
            $stmt->execute($target_user_ids);
            $emails = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'email');

            if (empty($emails)) {
                return ['success' => false, 'message' => 'No valid emails found for given user_ids'];
            }
        }

        // إعداد PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // أو SMTP الخاص بك
            $mail->SMTPAuth = true;
            $mail->Username = 'rano12ran67@gmail.com';  // ايميلك
            $mail->Password = 'vsjf ngxb cezp amfr';    // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('rano12ran67@gmail.com', 'System');

            // إضافة المستلمين
            foreach ($emails as $email) {
                $mail->addAddress($email);
            }

            // المحتوى
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
