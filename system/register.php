<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=user_system;charset=utf8mb4", "root", "");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $type = $_POST['type'];

    if (!$name || !$email || !$password || !$type) {
        $error = "الرجاء ملء جميع الحقول.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "البريد الإلكتروني غير صالح.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR base_email = ?");
        $stmt->execute([$email, $email]);
        if ($stmt->fetch()) {
            $error = "البريد الإلكتروني مستخدم مسبقاً.";
        } else {
            $hashPass = password_hash($password, PASSWORD_BCRYPT);
            $group_id = uniqid('grp_');

            if ($type === 'execution') {
                for ($i = 1; $i <= 3; $i++) {
                    $emailSplit = explode("@", $email);
                    $newEmail = $emailSplit[0] . "+$i@" . $emailSplit[1];
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, type, group_id, base_email) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $newEmail, $hashPass, $type, $group_id, $email]);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, type, group_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashPass, $type, $group_id]);
            }

            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title> Singup </title>
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
    .input-field input,
    .input-field select {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #c8e6c9;
      border-radius: 8px;
      font-size: 16px;
      background-color: #f9f9f9;
      transition: border-color 0.3s ease;
    }
    .input-field input:focus,
    .input-field select:focus {
      border-color: #66bb6a;
      background-color: #fff;
      outline: none;
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
    .error {
      display: none;
    }
  </style>
</head>
<body>

<div class="container">
  <h2 class="title"> Singup</h2>

  <form method="post" action="">
    <div class="input-field">
      <input type="text" name="name" placeholder="Name.." required />
    </div>
    <div class="input-field">
      <input type="email" name="email" placeholder=" Email..." required />
    </div>
    <div class="input-field">
      <input type="password" name="password" placeholder=" password" required />
    </div>
    <div class="input-field">
      <select name="type" required>
        <option value=""> Type Accounting</option>
        <option value="execution">منفذ (Execution)</option>
        <option value="requester">طالب (Requester)</option>
        <option value="admin">أدمن (Admin)</option>
      </select>
    </div>
    <input type="submit" class="btn" value=" Singup" />
    <p>  Do you have sense? <a href="login.php"> Login</a></p>
  </form>
</div>

<?php if (isset($error)): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'error',
    text: <?= json_encode($error) ?>,
    confirmButtonColor: '#43a047'
  });
</script>
<?php endif; ?>

</body>
</html>
