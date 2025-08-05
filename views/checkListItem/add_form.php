<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>إضافة فحص للمعدة</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
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

        select,
        input[type="text"] {
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

            <button type="submit">➕ إضافة</button>
        </form>
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