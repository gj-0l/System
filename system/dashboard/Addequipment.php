<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=user_system;charset=utf8mb4", "root", "");

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipment_name = trim($_POST['equipment_name']);
    $equipment_code = trim($_POST['equipment_code']);
    $description = trim($_POST['description']);

    if (!$equipment_name || !$equipment_code) {
        $error = "Please fill in all essential fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM equipment WHERE equipment_code = ?");
        $stmt->execute([$equipment_code]);
        if ($stmt->fetch()) {
            $error = "Equipment number is already in useً";
        } else {
            $stmt = $pdo->prepare("INSERT INTO equipment (equipment_name, equipment_code, description) VALUES (?, ?, ?)");
            $stmt->execute([$equipment_name, $equipment_code, $description]);
            $success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>Add quipment </title>
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
      direction: rtl;
      padding: 20px;
    }
    .header {
      width: 100%;
      background-color: #43a047;
      padding: 15px 25px;
      color: white;
      font-size: 18px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 8px;
      margin-bottom: 30px;
    }
    .header a {
      color: white;
      margin-left: 15px;
      text-decoration: none;
      font-weight: bold;
    }
    .container {
      background: #fff;
      padding: 40px 50px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0, 128, 0, 0.2);
      width: 100%;
      max-width: 420px;
      margin: auto;
    }
    .title {
      text-align: center;
      color: #2e7d32;
      font-size: 28px;
      margin-bottom: 30px;
      font-weight: bold;
    }
    .input-field {
      margin-bottom: 25px;
    }
    .input-field input,
    .input-field textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #c8e6c9;
      border-radius: 8px;
      font-size: 16px;
      background-color: #f9f9f9;
      transition: border-color 0.3s ease;
      resize: vertical;
    }
    .input-field input:focus,
    .input-field textarea:focus {
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
  </style>
</head>
<body>

<!-- ✅ الهيدر العلوي -->
<div class="header">
  <div><strong>dashboard -Adde quipment   </strong></div>
  <div>
    <a href="javascript:history.back()">🔙 Back</a>
   
  </div>
</div>

<!-- ✅ النموذج -->
<div class="container">
  <h2 class="title">  Add new equipment</h2>

  <form method="post" action="" id="equipmentForm">
    <div class="input-field">
      <input type="text" name="equipment_name" placeholder=" Name equipment" required value="<?= isset($equipment_name) ? htmlspecialchars($equipment_name) : '' ?>" />
    </div>
    <div class="input-field">
      <input type="text" name="equipment_code" placeholder=" Number equipment" required value="<?= isset($equipment_code) ? htmlspecialchars($equipment_code) : '' ?>" />
    </div>
    <div class="input-field">
      <textarea name="description" placeholder="Stomach description (optional)  " rows="4"><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
    </div>
    <input type="submit" class="btn" value=" Add equipment" />
  </form>
</div>

<!-- ✅ الرسائل -->
<script>
<?php if (isset($error)): ?>
Swal.fire({
  icon: 'error',
  title: 'error',
  text: <?= json_encode($error) ?>,
  confirmButtonColor: '#43a047'
});
<?php endif; ?>

<?php if ($success): ?>
Swal.fire({
  icon: 'success',
  title: '  Added successfully',
  confirmButtonColor: '#43a047'
}).then(() => {
  document.getElementById('equipmentForm').reset();
});
<?php endif; ?>
</script>

</body>
</html>
