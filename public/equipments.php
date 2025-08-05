<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EquipmentController.php';

session_start();
if (empty($_SESSION['auth_token'])) {
  header("Location: " . BASE_URL . "/public/login.php");
  exit();
}

$equipments = EquipmentController::list();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <title>قائمة المعدات</title>
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

    th,
    td {
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
    <a href="<?= BASE_URL ?>/public/dashboard.php" class="back-link">⬅ العودة إلى لوحة التحكم</a>
    <h2>قائمة المعدات</h2>

    <?php if (count($equipments) === 0): ?>
      <p>لا توجد معدات مضافة بعد.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>رقم</th>
            <th>اسم المعدة</th>
            <th>رقم المعدة</th>
            <th>الوصف</th>
            <th>الإجراءات</th>
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
                <button class="btn-delete" onclick="confirmDelete(<?= $equip['id'] ?>, this.closest('tr'))">حذف</button>

              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <script>
    function confirmDelete(id, rowElement) {
      Swal.fire({
        title: 'هل أنت متأكد أنك تريد الحذف؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e53935',
        cancelButtonColor: '#43a047',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch("<?= BASE_URL ?>/routes/equipment.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              action: "delete",
              id: id
            })
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire("تم الحذف!", data.message, "success");
                rowElement.remove();  // حذف الصف من الجدول
              } else {
                Swal.fire("خطأ", data.message, "error");
              }
            })
            .catch(() => {
              Swal.fire("خطأ", "حدث خطأ أثناء الاتصال بالخادم", "error");
            });
        }
      });
    }
  </script>

</body>

</html>