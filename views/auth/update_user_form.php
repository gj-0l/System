<?php
require_once __DIR__ . '/../../tools/sidebar.php';
require_once __DIR__ . '/../../tools/navbar.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>تعديل المستخدم</title>
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        .container {
            background: #fff;
            padding: 40px 50px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 128, 0, 0.2);
            width: 100%;
            max-width: 420px;
        }

        .title {
            text-align: center;
            color: #1d8e96;
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .input-field {
            position: relative;
            margin-bottom: 25px;
        }

        .input-field input,
        .input-field select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #c8e6c9;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        .input-field input:focus,
        .input-field select:focus {
            border-color: #66bb6a;
            background-color: #fff;
            outline: none;
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
    <?php renderNavbar('Update User'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('users'); ?>

        <main class="p-6 ml-4 md:pl-64" dir="rtl">
            <h2 class="title">تعديل المستخدم</h2>

            <form id="equipmentForm" method="post" onsubmit="return updateUser(event)">
                <div class="input-field">
                    <input type="text" name="name" id="name" placeholder="Name.." required />
                </div>

                <div class="input-field">
                    <input type="email" name="email" id="email" placeholder="Email..." required />
                </div>

                <div class="input-field">
                    <input type="password" name="password" id="password" placeholder="Password" />
                </div>

                <div class="input-field">
                    <select name="type" id="type" required>
                        <option value="">Type Accounting</option>
                        <option value="execution">منفذ (Execution)</option>
                        <option value="requester">طالب (Requester)</option>
                        <option value="manager">مدير (manager)</option>
                        <option value="admin">أدمن (Admin)</option>
                    </select>
                </div>

                <div class="input-field">
                    <select name="status" id="status" required>
                        <option value="">Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- ✅ القائمة الجديدة للمدير -->
                <div class="input-field">
                    <select name="manager_id" id="manager_id">
                        <option value="">-- اختر المدير --</option>
                    </select>
                </div>

                <input type="submit" class="btn" value="Update" />
            </form>
        </main>
    </div>

    <script>
        // 🔹 جلب كل المستخدمين لملء قائمة المديرين
        function loadManagers() {
            fetch('../routes/auth.php?action=get_users')
                .then(res => res.json())
                .then(data => {
                    const managerSelect = document.getElementById('manager_id');
                    managerSelect.innerHTML = `<option value="">-- اختر المدير --</option>`;

                    if (data.success && Array.isArray(data.users)) {
                        data.users.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.name;
                            managerSelect.appendChild(option);
                        });
                    }
                })
                .catch(err => {
                    console.error('Error loading managers:', err);
                });
        }

        // 🔹 الحصول على user_id من الرابط
        function getUserIdFromUrl() {
            const params = new URLSearchParams(window.location.search);
            return params.get('id');
        }

        // 🔹 جلب بيانات المستخدم لملء الحقول
        function fetchUserDetails() {
            const userId = getUserIdFromUrl();
            if (!userId) {
                Swal.fire("خطأ", "لم يتم تحديد المستخدم", "error");
                return;
            }

            fetch(`../routes/user.php?id=${userId}&action=get_user`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.user) {
                        document.getElementById("name").value = data.user.name;
                        document.getElementById("email").value = data.user.email;
                        document.getElementById("type").value = data.user.type;
                        document.getElementById("status").value = data.user.status;

                        // 🔹 بعد تحميل المدراء، نحدد المدير الحالي
                        setTimeout(() => {
                            document.getElementById("manager_id").value = data.user.manager_id || "";
                        }, 300);
                    } else {
                        Swal.fire("خطأ", data.message || "تعذر جلب بيانات المستخدم", "error");
                    }
                })
                .catch(() => {
                    Swal.fire("خطأ", "فشل الاتصال بالخادم", "error");
                });
        }

        // 🔹 استدعاء الدوال عند تحميل الصفحة
        window.onload = function () {
            loadManagers();
            fetchUserDetails();
        };

        // 🔹 دالة تحديث المستخدم
        function updateUser(event) {
            event.preventDefault();

            const userId = getUserIdFromUrl();
            const name = document.getElementById("name").value.trim();
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();
            const type = document.getElementById("type").value;
            const status = document.getElementById("status").value;
            const manager_id = document.getElementById("manager_id").value || null;

            if (!name || !email || !type) {
                Swal.fire("تنبيه", "يرجى تعبئة جميع الحقول", "warning");
                return;
            }

            fetch("../routes/user.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    action: "update",
                    id: userId,
                    name,
                    email,
                    password,
                    type,
                    status,
                    manager_id
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
</body>

</html>