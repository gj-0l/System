<?php
require_once __DIR__ . '/../../tools/sidebar.php';
require_once __DIR__ . '/../../tools/navbar.php';

?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>إضافة فحص للمعدة</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .form-control {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background-color: #0b6f76;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #22939b;
        }
    </style>
</head>

<body>
    <?php renderNavbar('Add Check'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('add_check_item'); ?>

        <main class="p-6 ml-4 md:pl-64" dir="rtl">
            <h2>إضافة فحص جديد للمعدة</h2>

            <form id="add-form" onsubmit="return addEquipment(event)">
                <label>المعدة</label>
                <select name="equipment_id" id="equipment_id" class="form-control" required>
                    <option value="">-- اختر المعدة --</option>
                    <?php foreach ($equipment as $eq): ?>
                        <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['equipment_name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>نوع الفحص</label>
                <input type="text" name="test_name" id="test_name" class="form-control" placeholder="مثلاً: فحص الزيت"
                    required />

                <label>الحالة الابتدائية</label>
                <select name="default_status" id="default_status" class="form-control" required>
                    <option value="accepted">مقبول</option>
                    <option value="rejected">مرفوض</option>
                </select>

                <label>الإجراء الأولي</label>
                <input type="text" name="initial_action" id="initial_action" class="form-control"
                    placeholder="مثلاً: تغيير الزيت" />

                <button class="btn" type="submit">➕ إضافة</button>
            </form>
        </main>
    </div>

    <script>
        function addEquipment(event) {
            event.preventDefault();

            const equipment_id = document.getElementById("equipment_id").value.trim();
            const test_name = document.getElementById("test_name").value.trim();
            const default_status = document.getElementById("default_status").value.trim();
            const initial_action = document.getElementById("initial_action").value.trim();

            if (!equipment_id || !test_name) {
                Swal.fire("تنبيه", "يرجى إدخال المعدة ونوع الفحص", "warning");
                return;
            }

            fetch("../routes/checkListItem.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "store",
                    equipment_id: equipment_id,
                    test_name: test_name,
                    default_status: default_status,
                    initial_action: initial_action
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire("نجاح", data.message, "success").then(() => {
                            document.getElementById("add-form").reset();
                        });
                    } else {
                        Swal.fire("خطأ", data.message, "error");
                    }
                })
                .catch(() => {
                    Swal.fire("خطأ", "فشل الاتصال بالخادم", "error");
                });
        }
    </script>

</body>

</html>