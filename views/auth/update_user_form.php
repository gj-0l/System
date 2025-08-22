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
            display: flex;
            justify-content: center;
            align-items: center;
            direction: rtl;
            padding: 20px;
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
            color: #2e7d32;
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

        p {
            text-align: center;
            margin-top: 20px;
            font-size: 15px;
        }

        a {
            color: #2e7d32;
            font-weight: bold;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="title"> Singup</h2>

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
                    <option value="admin">أدمن (Admin)</option>
                </select>
            </div>
            <div class="input-field">
                <select name="status" id="status" required>
                    <option value="">status</option>
                    <option value="active">active</option>
                    <option value="inactive">inactive</option>
                </select>
            </div>
            <input type="submit" class="btn" value="Update" />
        </form>

    </div>

    <script>
        <?php if (!empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: <?= json_encode($error) ?>,
                confirmButtonColor: '#43a047'
            });
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'تمت الإضافة بنجاح',
                confirmButtonColor: '#43a047'
            }).then(() => {
                document.getElementById('equipmentForm').reset();
            });
        <?php endif; ?>
    </script>

    <script>
        // Get user id from URL
        function getUserIdFromUrl() {
            const params = new URLSearchParams(window.location.search);
            return params.get('id');
        }

        // Fetch user details and fill the form
        function fetchUserDetails() {
            const userId = getUserIdFromUrl();
            if (!userId) {
                Swal.fire("خطأ", "لم يتم تحديد المستخدم", "error");
                return;
            }

            fetch(`../routes/user.php?id=${userId}&action=get_user`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json"
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.user) {
                        document.getElementById("name").value = data.user.name;
                        document.getElementById("email").value = data.user.email;
                        document.getElementById("type").value = data.user.type;
                        document.getElementById("status").value = data.user.status;
                    } else {
                        Swal.fire("خطأ", data.message || "تعذر جلب بيانات المستخدم", "error");
                    }
                })
                .catch(() => {
                    Swal.fire("خطأ", "فشل الاتصال بالخادم", "error");
                });
        }

        // Call fetchUserDetails on page load
        window.onload = fetchUserDetails;

        // Update user function (example)
        function updateUser(event) {
            event.preventDefault();

            const userId = getUserIdFromUrl();
            const name = document.getElementById("name").value.trim();
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();
            const type = document.getElementById("type").value;
            const status = document.getElementById("status").value;

            if (!name || !email || !type) {
                Swal.fire("تنبيه", "يرجى تعبئة جميع الحقول", "warning");
                return;
            }

            fetch("../routes/user.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "update",
                    id: userId,
                    name,
                    email,
                    password,
                    type,
                    status
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