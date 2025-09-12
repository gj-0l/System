<?php
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance()->getConnection();

$selected_equipment = $_GET['equipment_id'] ?? null;
$checklist = [];
$equipment = $db->query("SELECT * FROM equipment")->fetchAll(PDO::FETCH_ASSOC);

if ($selected_equipment) {
  $stmt = $db->prepare("SELECT * FROM checklist_items WHERE equipment_id = ?");
  $stmt->execute([$selected_equipment]);
  $checklist = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $date = date('Y-m-d');
  foreach ($_POST['status'] as $checklist_id => $status) {
    $stmt = $db->prepare("INSERT INTO checklist_results (checklist_id, date, status) VALUES (?, ?, ?)");
    $stmt->execute([$checklist_id, $date, $status]);
  }
  $message = "✔️ تم حفظ نتائج الفحص بنجاح.";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>فحص المعدات</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #e8f5e9;
      direction: rtl;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 900px;
      margin: 40px auto;
      background-color: #ffffff;
      padding: 25px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0, 128, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #1d8e96;
      margin-bottom: 20px;
    }

    .message {
      background-color: #d0f0d0;
      color: #1b5e20;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: bold;
      text-align: center;
    }

    label {
      font-weight: bold;
      color: #1d8e96;
    }

    select,
    button {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      border-radius: 8px;
      border: 1px solid #a5d6a7;
      font-size: 16px;
    }

    select:focus,
    button:focus {
      outline: none;
      border-color: #66bb6a;
    }

    button {
      background-color: #0b6f76;
      color: white;
      margin-top: 20px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #22939b;
    }

    table {
      width: 100%;
      margin-top: 20px;
      border-collapse: collapse;
      background-color: #f1f8e9;
    }

    th,
    td {
      padding: 12px;
      text-align: center;
      border: 1px solid #c8e6c9;
    }

    th {
      background-color: #a5d6a7;
      color: #1b5e20;
    }

    p {
      color: #d32f2f;
      font-weight: bold;
      text-align: center;
    }
  </style>
</head>

<body>

  <div class="container">
    <h2>فحص المعدات اليومية</h2>

    <?php if (isset($message)): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="get">
      <label for="equipment_id">اختر المعدة:</label>
      <select name="equipment_id" id="equipment_id" onchange="this.form.submit()">
        <option value="">-- اختر --</option>
        <?php foreach ($equipment as $eq): ?>
          <option value="<?= $eq['id'] ?>" <?= ($selected_equipment == $eq['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($eq['equipment_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if ($selected_equipment && count($checklist)): ?>
      <form method="post">
        <table>
          <tr>
            <th>اسم الفحص</th>
            <th>الإجراء الابتدائي</th>
            <th>الحالة</th>
          </tr>
          <?php foreach ($checklist as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['test_name']) ?></td>
              <td><?= htmlspecialchars($item['initial_action']) ?></td>
              <td>
                <select name="status[<?= $item['id'] ?>]">
                  <option value="accepted">✔️ مقبول</option>
                  <option value="rejected">❌ مرفوض</option>
                </select>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
        <button type="submit">💾 حفظ النتائج</button>
      </form>
    <?php elseif ($selected_equipment): ?>
      <p>❌ لا توجد فحوصات مضافة لهذه المعدة بعد.</p>
    <?php endif; ?>
  </div>

</body>

</html>