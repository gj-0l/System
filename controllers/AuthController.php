<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';

class AuthController
{
    public static function login()
    {
        session_start();
        $db = Database::getInstance()->getConnection();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = "يرجى إدخال البريد الإلكتروني وكلمة المرور.";
            header("Location: " . BASE_URL . "/public/login.php");
            exit();
        }

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            // تحقق من وجود التوكن، إذا ما موجود نولده
            if (empty($user['token'])) {
                $token = hash('sha256', uniqid($user['email'] . $user['name'], true));
                $update = $db->prepare("UPDATE users SET token = ? WHERE id = ?");
                $update->execute([$token, $user['id']]);
            } else {
                $token = $user['token'];
            }

            // تخزين التوكن في السيشن
            $_SESSION['auth_token'] = $token;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['type'];
            $_SESSION['user_name'] = $user['name'];

            // إعادة التوجيه حسب النوع
            switch ($user['type']) {
                case 'execution':
                    header("Location: " . BASE_URL . "/public/executer.php");
                    break;
                case 'requester':
                    header("Location: " . BASE_URL . "/public/requester_calendar.php");
                    break;
                case 'admin':
                    header("Location: " . BASE_URL . "/public/dashboard.php");
                    break;
                default:
                    header("Location: " . BASE_URL . "/public/login.php");
                    break;
            }

            exit();
        } else {
            $_SESSION['login_error'] = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
            header("Location: " . BASE_URL . "/public/login.php");
            exit();
        }
    }

    public static function list()
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("SELECT * FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    public static function logout()
    {
        session_start();

        // إزالة كل بيانات الجلسة
        $_SESSION = [];
        session_unset();
        session_destroy();

        // إعادة التوجيه لصفحة تسجيل الدخول
        header("Location: " . BASE_URL . "/public/login.php");
        exit();
    }



    public static function add_user($name, $email, $password, $type)
    {
        $db = Database::getInstance()->getConnection();
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Generate a unique token by combining user info + randomness
        $raw_token = $name . $email . bin2hex(random_bytes(16)) . time();
        $token = hash('sha256', $raw_token); // 64-char hashed token

        try {
            $stmt = $db->prepare("INSERT INTO users (name, email, password, type, token) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $type, $token]);

            return [
                'success' => true,
                'message' => 'تمت إضافة المستخدم بنجاح',
                'token' => $token
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الإضافة: ' . $e->getMessage()
            ];
        }
    }

}
