<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=user_system;charset=utf8mb4", "root", "");

// حذف معدة إذا وصلنا طلب حذف
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: equipment_list.php");
    exit();
}

// جلب جميع المعدات بالترتيب التصاعدي حسب id
$stmt = $pdo->query("SELECT * FROM equipment ORDER BY id ASC");
$equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title> Equipment List</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: linear-gradient(to right, #e0f7ec, #a8e6cf);
      padding: 20px;
      direction: rtl;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 20px 30px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 128, 0, 0.15);
    }
    h2 {
      color: #2e7d32;
      text-align: center;
      margin-bottom: 25px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: right;
    }
    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #c8e6c9;
    }
    th {
      background-color: #a8e6cf;
      color: #2e7d32;
    }
    tr:hover {
      background-color: #f1f8f4;
    }
    .btn-delete {
      background-color: #e53935;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }
    .btn-delete:hover {
      background-color: #ab000d;
    }
    a.back-link {
      display: inline-block;
      margin-bottom: 15px;
      color: #2e7d32;
      font-weight: bold;
      text-decoration: none;
    }
    a.back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="container">
  <a href="dashboard.php" class="back-link">⬅   Back to the control panel</a>
  <h2> Equipment List</h2>

  <?php if (count($equipments) === 0): ?>
    <p>     No equipment added yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Num</th>
          <th>Name Equipment </th>
          <th> num Equipment</th>
          <th>Description</th>
          <th>Necessary procedures</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($equipments as $equip): ?>
        <tr>
          <td><?= htmlspecialchars($equip['id']) ?></td>
          <td><?= htmlspecialchars($equip['equipment_name']) ?></td>
          <td><?= htmlspecialchars($equip['equipment_code']) ?></td>
          <td><?= nl2br(htmlspecialchars($equip['description'])) ?></td>
          <td>
            <button class="btn-delete" onclick="confirmDelete(<?= $equip['id'] ?>)">Delet</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
function confirmDelete(id) {
  Swal.fire({
    title: '    Are you sure you want to delete?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e53935',
    cancelButtonColor: '#43a047',
    confirmButtonText: 'yes، Delet',
    cancelButtonText: 'cancellation'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'dashboard/showequipment.php?delete=' + id;
    }
  });
}
</script>

</body>
</html>
