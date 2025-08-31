<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ChecklistItemController.php';
require_once __DIR__ . '/../core/Database.php';

session_start();
if (empty($_SESSION['auth_token'])) {
  header("Location: " . BASE_URL . "/public/login.php");
  exit();
}

$db = Database::getInstance()->getConnection();
$equipments = $db->query("SELECT id, equipment_name FROM equipment ORDER BY equipment_name ASC")->fetchAll(PDO::FETCH_ASSOC);
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

    select,
    input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
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

    <!-- Dropdown لاختيار المعدة -->
    <label for="equipmentSelect">اختر المعدة:</label>
    <select id="equipmentSelect">
      <option value="">-- اختر --</option>
      <?php foreach ($equipments as $eq): ?>
        <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['equipment_name']) ?></option>
      <?php endforeach; ?>
    </select>

    <!-- جدول العناصر -->
    <div id="itemsContainer" style="margin-top:20px; display:none;">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>نوع الفحص</th>
            <th>الحالة الابتدائية</th>
            <th>الإجراء الأولي</th>
            <th>التحكم</th>
          </tr>
        </thead>
        <tbody id="itemsTableBody">
          <!-- يملأ بالـ JS -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
    document.getElementById("equipmentSelect").addEventListener("change", function () {
      let equipmentId = this.value;
      if (!equipmentId) {
        document.getElementById("itemsContainer").style.display = "none";
        return;
      }

      fetch("<?= BASE_URL ?>/routes/checkListItem.php?equipment_id=" + equipmentId, {
        method: "GET",
        headers: { "Content-Type": "application/json" }
      })
        .then(res => res.json())
        .then(data => {
          let tbody = document.getElementById("itemsTableBody");
          tbody.innerHTML = "";

          if (!data || data.length === 0) {
            tbody.innerHTML = "<tr><td colspan='5'>لا توجد عناصر لهذه المعدة.</td></tr>";
          } else {
            data.forEach(item => {
              let row = `
              <tr>
                <td>${item.id}</td>
                <td>${item.test_name}</td>
                <td>${item.default_status}</td>
                <td>${item.initial_action}</td>
                <td>
                  <button class="btn-delete" onclick="confirmDelete(${item.id}, this.closest('tr'))">حذف</button>
                  <button class="btn-update">
                    <a href="<?= BASE_URL ?>/public/update_check_item.php?id=${item.id}">تعديل</a>
                  </button>
                </td>
              </tr>
            `;
              tbody.insertAdjacentHTML("beforeend", row);
            });
          }

          document.getElementById("itemsContainer").style.display = "block";
        })
        .catch(() => {
          Swal.fire("خطأ", "فشل الاتصال بالخادم", "error");
        });
    });

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
          fetch("<?= BASE_URL ?>/routes/checkListItem.php", {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({ id: id })
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