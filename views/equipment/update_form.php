<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <title>إضافة معدّة</title>
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <link rel="stylesheet" href="../public/css/style.css"> -->

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        body {
            background: linear-gradient(to right, #e0f7ec, #a8e6cf);
            min-height: 100vh;
            direction: rtl;
            padding: 20px;
        }

        .header {
            width: 100%;
            background-color: #43a047;
            padding: 15px 25px;
            color: white;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .header a {
            color: white;
            margin-left: 15px;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            background: #fff;
            padding: 40px 50px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 128, 0, 0.2);
            width: 100%;
            max-width: 420px;
            margin: auto;
        }

        .title {
            text-align: center;
            color: #2e7d32;
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .input-field {
            margin-bottom: 25px;
        }

        .input-field input,
        .input-field textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #c8e6c9;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
            resize: vertical;
        }

        .input-field input:focus,
        .input-field textarea:focus {
            border-color: #66bb6a;
            background-color: #fff;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background-color: #43a047;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #388e3c;
        }
    </style>
</head>

<body>
    <div class="header">
        <div><strong>لوحة التحكم - إضافة معدّة</strong></div>
        <div><a href="javascript:history.back()">🔙 رجوع</a></div>
    </div>

    <div class="container">
        <h2 class="title">إضافة معدّة جديدة</h2>

        <form id="add-form" onsubmit="return updateEquipment(event)" style="margin-bottom: 25px;">
            <div class="input-field">
                <input type="text" id="equipment_name" name="equipment_name" placeholder="اسم المعدّة" required
                    value="<?= htmlspecialchars($old['equipment_name'] ?? '') ?>" />
            </div>
            <div class="input-field">
                <input type="text" id="equipment_code" name="equipment_code" placeholder="رقم المعدّة" required
                    value="<?= htmlspecialchars($old['equipment_code'] ?? '') ?>" />
            </div>
            <div class="input-field">
                <textarea id="description" name="description" placeholder="وصف (اختياري)"
                    rows="4"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn">تعديل</button>
        </form>

    </div>

    <script>
        // Get equipment id from URL
        function getEquipmentId() {
            const params = new URLSearchParams(window.location.search);
            return params.get('id');
        }

        // Fetch equipment details and fill the form
        function fetchEquipment() {
            const equipmentId = getEquipmentId();
            if (!equipmentId) {
                Swal.fire("خطأ", "لم يتم تحديد المعدة", "error");
                return;
            }

            fetch(`../routes/equipment.php?id=${equipmentId}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json"
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.equipment) {
                        document.getElementById("equipment_name").value = data.equipment.equipment_name;
                        document.getElementById("equipment_code").value = data.equipment.equipment_code;
                        document.getElementById("description").value = data.equipment.description;
                    } else {
                        Swal.fire("خطأ", data.message || "تعذر جلب بيانات المعدة", "error");
                    }
                })
                .catch(() => {
                    Swal.fire("خطأ", "فشل الاتصال بالخادم", "error");
                });
        }

        // Call fetchEquipment on page load
        window.onload = fetchEquipment;

        // Update equipment function (example)
        function updateEquipment(event) {
            event.preventDefault();

            const equipmentId = getEquipmentId();
            const equipment_name = document.getElementById("equipment_name").value.trim();
            const equipment_code = document.getElementById("equipment_code").value.trim();
            const description = document.getElementById("description").value.trim();

            if (!equipment_name || !equipment_code) {
                Swal.fire("تنبيه", "يرجى تعبئة جميع الحقول", "warning");
                return;
            }

            fetch("../routes/equipment.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "update",
                    id: equipmentId,
                    equipment_name,
                    equipment_code,
                    description
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire("نجاح", data.message, "success");
                    } else {
                        Swal.fire("خطأ", data.message, "error");
                    }
                })
                .catch(() => {
                    Swal.fire("خطأ", "فشل الاتصال بالخادم", "error");
                });
        }
    </script>

    <script>
        function addEquipment(event) {
            event.preventDefault();

            const name = document.getElementById("name").value.trim();
            const code = document.getElementById("code").value.trim();
            const desc = document.getElementById("desc").value.trim();

            if (!name || !code) {
                Swal.fire("تنبيه", "يرجى إدخال اسم المعدة ورقمها", "warning");
                return;
            }

            fetch("../routes/equipment.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "store",
                    equipment_name: name,
                    equipment_code: code,
                    description: desc
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire("نجاح", data.message, "success").then(() => location.reload());
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