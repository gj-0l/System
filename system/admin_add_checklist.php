<?php
$pdo = new PDO("mysql:host=localhost;dbname=user_system;charset=utf8mb4", "root", "");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipment_id = $_POST['equipment_id'] ?? null;
    $test_name = $_POST['test_name'] ?? null;
    $initial_action = $_POST['initial_action'] ?? null;
    $default_status = $_POST['default_status'] ?? 'accepted';

    if ($equipment_id && $test_name) {
        $stmt = $pdo->prepare("INSERT INTO checklist_items (equipment_id, test_name, initial_action, default_status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$equipment_id, $test_name, $initial_action, $default_status]);
        $message = "✔️ تم الإضافة بنجاح";
    } else {
        $message = "⚠️ الرجاء إدخال كل الحقول المطلوبة.";
    }
}

$equipment = $pdo->query("SELECT * FROM equipment")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>إضافة فحص للمعدة</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      direction: rtl;
      margin: 0;
      padding: 0;
      background-color: #f8f9fa;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .form-container {
      width: 90%;
      max-width: 600px;
      background: #fff;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
    }

    select, input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    button {
      background-color: #28a745;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      font-size: 1.1rem;
    }

    .message {
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 6px;
      background-color: #e9ecef;
      color: #333;
      text-align: center;
    }

    @media (max-width: 480px) {
      .form-container {
        padding: 15px;
      }

      button {
        font-size: 1rem;
        padding: 10px;
      }
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>إضافة فحص جديد للمعدة</h2>

  <?php if (isset($message)): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <form method="post">
    <label>المعدة</label>
    <select name="equipment_id" required>
      <option value="">-- اختر المعدة --</option>
      <?php foreach ($equipment as $eq): ?>
        <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['equipment_name']) ?></option>
      <?php endforeach; ?>
    </select>

    <label>نوع الفحص</label>
    <input type="text" name="test_name" placeholder="مثلاً: فحص الزيت" required />

    <label>الحالة الابتدائية</label>
    <select name="default_status" required>
      <option value="accepted">مقبول</option>
      <option value="rejected">مرفوض</option>
    </select>

    <label>الإجراء الأولي</label>
    <input type="text" name="initial_action" placeholder="مثلاً: تغيير الزيت" />

    <button type="submit">➕ إضافة</button>
  </form>
</div>

</body>
</html>
