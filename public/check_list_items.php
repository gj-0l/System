<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ChecklistItemController.php';
require_once __DIR__ . '/../core/Database.php';

require_once __DIR__ . '/../tools/sidebar.php';
require_once __DIR__ . '/../tools/navbar.php';

session_start();
if (empty($_SESSION['auth_token'])) {
  header("Location: " . BASE_URL . "/public/login.php");
  exit();
}

$db = Database::getInstance()->getConnection();
$equipments = $db->query("SELECT id, equipment_name FROM equipment ORDER BY equipment_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>قائمة عناصر الفحص</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
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
      color: #22939b;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      padding: 12px 10px;
      border-bottom: 1px solid #c8e6c9;
      text-align: right;
    }

    th {
      background-color: #a8e6cf;
      color: #1d8e96;
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
      color: #1d8e96;
      font-weight: bold;
      text-decoration: none;
    }

    a.back-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {

      table,
      thead,
      tbody,
      th,
      td,
      tr {
        display: block;
      }

      thead tr {
        display: none;
      }

      tr {
        margin-bottom: 15px;
        border: 1px solid #c8e6c9;
        border-radius: 10px;
        padding: 10px;
      }

      td {
        text-align: right;
        /* ✅ كل النصوص يمين */
        padding: 8px 10px 8px 130px;
        /* ✅ نترك مساحة لليبل */
        position: relative;
        direction: rtl;
        /* ✅ يلتزم بالاتجاه */
      }

      td::before {
        content: attr(data-label);
        font-weight: bold;
        position: absolute;
        right: 10px;
        /* ✅ الليبل على اليمين */
        top: 50%;
        transform: translateY(-50%);
        white-space: nowrap;
        color: #333;
      }

      td:last-child a {
        display: block;
        /* ✅ كل زر بسطر */
        width: 100%;
        /* ✅ ياخذ عرض كامل */
        text-align: center;
        /* ✅ النص بالوسط */
        margin-bottom: 6px;
        /* ✅ مسافة بين الأزرار */
      }

      td:last-child a:last-child {
        margin-bottom: 0;
        /* ✅ آخر زر بلا مسافة إضافية */
      }
    }
  </style>
</head>

<body>
  <?php renderNavbar('Check List'); ?>
  <div class="dashboard-container min-h-screen bg-gray-50">
    <?php renderSidebar('check_list_items'); ?>

    <main class="p-6 ml-4 md:pl-64" dir="rtl">
      <!-- Dropdown لاختيار المعدة -->
      <label for="equipmentSelect">اختر المعدة:</label>
      <select id="equipmentSelect">
        <option value="">-- اختر --</option>
        <?php foreach ($equipments as $eq): ?>
          <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['equipment_name']) ?></option>
        <?php endforeach; ?>
      </select>

      <!-- جدول العناصر -->
      <div id="itemsContainer" class="w-full max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md"
        style="margin-top:20px; display:none;">
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
    </main>
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
        cancelButtonColor: '#22939b',
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