<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EquipmentController.php';

session_start();
if (empty($_SESSION['auth_token'])) {
  header("Location: " . BASE_URL . "/public/login.php");
  exit();
}
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
      margin: 0;
    }

    .container {
      max-width: 1000px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 128, 0, 0.15);
      overflow-x: auto;
    }

    h2 {
      color: #2e7d32;
      text-align: center;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 600px;
    }

    th,
    td {
      padding: 12px 10px;
      border-bottom: 1px solid #c8e6c9;
      text-align: right;
    }

    th {
      background-color: #a8e6cf;
      color: #2e7d32;
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
      color: #2e7d32;
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
  <div class="container">
    <a href="<?= BASE_URL ?>/public/dashboard.php" class="back-link">⬅ العودة إلى لوحة التحكم</a>
    <h2>قائمة المعدات</h2>

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
        cancelButtonColor: '#43a047',
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