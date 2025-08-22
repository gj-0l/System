<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ChecklistItemController.php';

session_start();
if (empty($_SESSION['auth_token'])) {
  header("Location: " . BASE_URL . "/public/login.php");
  exit();
}

$items = ChecklistItemController::list();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>قائمة عناصر الفحص</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: linear-gradient(to right, #f1f8e9, #c8e6c9);
      padding: 20px;
      direction: rtl;
    }

    .container {
      max-width: 1000px;
      margin: auto;
      background: #fff;
      padding: 25px 30px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 128, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #388e3c;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      text-align: right;
    }

    th,
    td {
      padding: 12px 15px;
      border-bottom: 1px solid #a5d6a7;
    }

    th {
      background-color: #c8e6c9;
      color: #2e7d32;
    }

    tr:hover {
      background-color: #f1f8f4;
    }

    .btn-delete {
      background-color: #d32f2f;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .btn-delete:hover {
      background-color: #9a0007;
    }

    .btn-update {
      background-color: #1976d2;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }

    .btn-update a {
      text-decoration: none;
      color: white;
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
    <h2>قائمة عناصر الفحص</h2>

    <?php if (count($items) === 0): ?>
      <p>لا توجد عناصر حتى الآن.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>المعدة</th>
            <th>نوع الفحص</th>
            <th>الحالة الابتدائية</th>
            <th>الإجراء الأولي</th>
            <th>التحكم</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['id']) ?></td>
              <td><?= htmlspecialchars($item['equipment_name']) ?></td>
              <td><?= htmlspecialchars($item['test_name']) ?></td>
              <td><?= htmlspecialchars($item['default_status']) ?></td>
              <td><?= htmlspecialchars($item['initial_action']) ?></td>
              <td>
                <button class="btn-delete" onclick="confirmDelete(<?= $item['id'] ?>, this.closest('tr'))">حذف</button>
                <button class="btn-update"><a
                    href="<?= BASE_URL . '/public/update_check_item.php?id=' . $item['id'] ?>">تعديل</a></button>
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
        title: 'هل أنت متأكد من الحذف؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d32f2f',
        cancelButtonColor: '#388e3c',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch("<?= BASE_URL ?>/routes/checklist_item.php?id=" + id, {
            method: "DELETE",
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire("تم الحذف", data.message, "success");
                rowElement.remove();
              } else {
                Swal.fire("خطأ", data.message, "error");
              }
            })
            .catch(() => {
              Swal.fire("خطأ", "فشل الاتصال بالخادم", "error");
            });
        }
      });
    }
  </script>
</body>

</html>