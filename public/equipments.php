<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EquipmentController.php';
require_once __DIR__ . '/../tools/sidebar.php';
require_once __DIR__ . '/../tools/navbar.php';

session_start();
if (empty($_SESSION['auth_token'])) {
  header("Location: " . BASE_URL . "/public/login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>قائمة المعدات</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    h2 {
      color: #1d8e96;
      text-align: center;
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

    .btn-delete,
    .btn-update {
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      border: none;
      transition: background-color 0.3s ease;
      margin-bottom: 4px;
      width: 100%;
      box-sizing: border-box;
    }

    .btn-delete {
      background-color: #e53935;
      color: white;
    }

    .btn-delete:hover {
      background-color: #ab000d;
    }

    .btn-update {
      background-color: #3587e5;
      color: white;
    }

    .btn-update a {
      text-decoration: none;
      color: white;
      display: block;
      width: 100%;
    }

    .btn-update:hover {
      background-color: #1c5fc4;
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
        padding: 8px 10px;
        position: relative;
      }

      td::before {
        content: attr(data-label);
        font-weight: bold;
        display: inline-block;
        width: 120px;
      }
    }
  </style>
</head>

<body>
  <?php renderNavbar('Equipments List'); ?>
  <div class="dashboard-container min-h-screen bg-gray-50">
    <?php renderSidebar('equipments'); ?>

    <main class="p-6 ml-4 md:pl-64" dir="rtl">
      <h2>قائمة المعدات</h2>
      <div class="w-full max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <table id="equipmentsTable">
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
            <tr>
              <td colspan="5">⏳ جارٍ تحميل البيانات...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <script>
    const BASE_URL = <?= json_encode(BASE_URL) ?>;
    const tbody = document.querySelector("#equipmentsTable tbody");

    async function loadEquipments() {
      try {
        const res = await fetch(`${BASE_URL}/routes/equipment.php`);
        const equipments = await res.json();

        tbody.innerHTML = "";

        if (!Array.isArray(equipments) || equipments.length === 0) {
          tbody.innerHTML = `<tr><td colspan="5">لا توجد معدات</td></tr>`;
          return;
        }

        equipments.forEach((equip, index) => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td data-label="رقم">${index + 1}</td>
            <td data-label="اسم المعدة">${equip.equipment_name || "-"}</td>
            <td data-label="رقم المعدة">${equip.equipment_code || "-"}</td>
            <td data-label="الوصف">${equip.description || "-"}</td>
            <td data-label="الإجراءات">
              <button class="btn-delete" onclick="confirmDelete(${equip.id}, this.closest('tr'))">حذف</button>
              <button class="btn-update"><a href="${BASE_URL}/public/update_equipment.php?id=${equip.id}">تعديل</a></button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      } catch (err) {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="5">⚠️ خطأ في الاتصال بالسيرفر</td></tr>`;
      }
    }

    function confirmDelete(id, rowElement) {
      Swal.fire({
        title: 'هل أنت متأكد أنك تريد الحذف؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e53935',
        cancelButtonColor: '#0b6f76',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const res = await fetch(`${BASE_URL}/routes/equipment.php`, {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ action: "delete", id })
            });
            const data = await res.json();

            if (data.success) {
              Swal.fire("تم الحذف!", data.message, "success");
              rowElement.remove();
            } else {
              Swal.fire("خطأ", data.message, "error");
            }
          } catch {
            Swal.fire("خطأ", "حدث خطأ أثناء الاتصال بالخادم", "error");
          }
        }
      });
    }

    loadEquipments();
  </script>
</body>

</html>