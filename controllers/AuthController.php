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

        if ($user) {
            if ($user['status'] !== 'active') {
                $_SESSION['login_error'] = "حسابك غير مفعل. يرجى التواصل مع الإدارة.";
                header("Location: " . BASE_URL . "/public/login.php");
                exit();
            }

            if (password_verify($password, $user['password'])) {
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
                        header("Location: " . BASE_URL . "/public/executer_dashboard.php");
                        break;
                    case 'requester':
                        header("Location: " . BASE_URL . "/public/requester_calendar.php");
                        break;
                    case 'manager':
                        header("Location: " . BASE_URL . "/public/manager.php");
                        break;
                    case 'admin':
                        header("Location: " . BASE_URL . "/public/admin.php");
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

        // Generate a unique token
        $raw_token = $name . $email . bin2hex(random_bytes(16)) . time();
        $token = hash('sha256', $raw_token);

        $status = 'inactive'; // Default status

        try {
            $stmt = $db->prepare("INSERT INTO users (name, email, password, type, status, token) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $type, $status, $token]);

            // جيب آخر ID مضاف
            $user_id = $db->lastInsertId();

            // جيب بيانات المستخدم كاملة
            $userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $userStmt->execute([$user_id]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => 'Join request sent, please wait for admin approval.',
                'token' => $token,
                'data' => $userData
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الإضافة: ' . $e->getMessage()
            ];
        }
    }


    public static function update_user($id, $manager_id, $name, $email, $password, $type, $status)
    {
        $db = Database::getInstance()->getConnection();
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET manager_id =?, name = ?, email = ?, password = ?, type = ?, status = ? WHERE id = ?");
                $stmt->execute([$manager_id, $name, $email, $hashed_password, $type, $status, $id]);
            } else {
                $stmt = $db->prepare("UPDATE users SET manager_id =?, name = ?, email = ?, type = ?, status = ? WHERE id = ?");
                $stmt->execute([$manager_id, $name, $email, $type, $status, $id]);
            }

            return [
                'success' => true,
                'message' => 'تم تحديث المستخدم بنجاح'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()
            ];
        }
    }

    public static function get_user($id)
    {
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                return [
                    'success' => true,
                    'user' => $user
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'المستخدم غير موجود'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المستخدم: ' . $e->getMessage()
            ];
        }
    }

    public static function delete_user($id)
    {
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'تم حذف المستخدم بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'المستخدم غير موجود أو تم حذفه مسبقاً'
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
