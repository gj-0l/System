<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=user_system;charset=utf8mb4", "root", "");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR base_email = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['type'];
        $_SESSION['group_id'] = $user['group_id'];

        // التوجيه حسب نوع الحساب
        switch ($user['type']) {
            case 'execution':
                header("Location: executionnotivcation.php");
                break;
            case 'requester':
                header("Location: requester_dashboard.php");
                break;
            case 'admin':
                header("Location: dashboard/dashboard.php"); 
                break;
            default:
                header("Location: login.php");
                break;
        }
        exit();
    } else {
        $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>تسجيل الدخول</title>
  <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    * {
      margin: 0; padding: 0; box-sizing: border-box;
      font-family: 'Cairo', sans-serif;
    }
    body {
      background: linear-gradient(to right, #e0f7ec, #a8e6cf);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      direction: rtl;
      padding: 20px;
    }
    .container {
      background: #fff;
      padding: 40px 50px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0, 128, 0, 0.2);
      width: 100%;
      max-width: 420px;
    }
    .title {
      text-align: center;
      color: #2e7d32;
      font-size: 28px;
      margin-bottom: 30px;
      font-weight: bold;
    }
    .input-field {
      position: relative;
      margin-bottom: 25px;
    }
    .input-field input {
      width: 100%;
      padding: 12px 40px 12px 12px;
      border: 2px solid #c8e6c9;
      border-radius: 8px;
      font-size: 16px;
      background-color: #f9f9f9;
      transition: border-color 0.3s ease;
    }
    .input-field input:focus {
      border-color: #66bb6a;
      background-color: #fff;
      outline: none;
    }
    .input-field i {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #66bb6a;
      font-size: 18px;
    }
    .btn {
      width: 100%;
      padding: 14px;
      background-color: #43a047;
      border: none;
      border-radius: 8px;
      color: white;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .btn:hover {
      background-color: #388e3c;
    }
    p {
      text-align: center;
      margin-top: 20px;
      font-size: 15px;
    }
    a {
      color: #2e7d32;
      font-weight: bold;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="container">
  <h2 class="title">تسجيل الدخول</h2>

  <form method="post" action="">
    <div class="input-field">
      <i class="fas fa-user"></i>
      <input type="email" name="email" placeholder="البريد الإلكتروني" required />
    </div>
    <div class="input-field">
      <i class="fas fa-lock"></i>
      <input type="password" name="password" placeholder="كلمة المرور" required />
    </div>
    <input type="submit" value="دخول" class="btn" />
    <p>لا تملك حساب؟ <a href="register.php">إنشاء حساب</a></p>
  </form>
</div>

<?php if (isset($error)): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'خطأ',
    text: <?= json_encode($error) ?>,
    confirmButtonColor: '#43a047'
  });
</script>
<?php endif; ?>

</body>
</html>
