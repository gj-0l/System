<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <title> dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap" rel="stylesheet">
    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-messaging-compat.js"></script>

    <script>
        // إعداد Firebase (استبدل القيم بقيمك)
        const firebaseConfig = {
            apiKey: "AIzaSyBwxIvQua1PMFur2bonw3ZSkRd2IL36e_A",
            authDomain: "mobile-equipment-3ac58.firebaseapp.com",
            projectId: "mobile-equipment-3ac58",
            storageBucket: "mobile-equipment-3ac58.firebasestorage.app",
            messagingSenderId: "736129810254",
            appId: "1:736129810254:web:1f70eaa87ec803279fa81f",
            measurementId: "G-DYC99K0M32", // يمكن وضعها هنا أو في دالة getToken مباشرة
        };
        firebase.initializeApp(firebaseConfig);

        const messaging = firebase.messaging();

        // اطلب إذن الإشعارات وجلب التوكن
        messaging.requestPermission()
            .then(() => messaging.getToken({ vapidKey: 'BLvVVJkkOyQNHDeca15iLwY7RLOqIf5xWooimnt_xWjqyGN7b6Q2I59qsX5WizmlrNRyuo57QqmCOpqaiJ90Da0' }))
            .then((currentToken) => {
                if (currentToken) {
                    // أرسل التوكن للسيرفر
                    fetch('../../routes/notifications.php?action=save_token', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ token: currentToken })
                    }).then(res => {
                        if (res.ok) {
                            console.log("تم حفظ التوكن بنجاح.");
                        }
                    }).catch(err => console.error("خطأ في حفظ التوكن:", err));
                } else {
                    console.log('لا يوجد توكن للإشعارات.');
                }
            })
            .catch((err) => {
                console.error('فشل في الحصول على إذن الإشعارات أو التوكن:', err);
            });
    </script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Cairo', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #a8e6cf, #dcedc1);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
            direction: rtl;
        }

        .dashboard-container {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(34, 139, 34, 0.15);
            width: 100%;
            max-width: 900px;
            padding: 30px 40px;
        }

        h1 {
            color: #2e7d32;
            font-weight: 700;
            font-size: 32px;
            margin-bottom: 35px;
            text-align: center;
            letter-spacing: 1.5px;
        }

        .nav-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .nav-buttons a {
            flex: 1 1 200px;
            background-color: #43a047;
            color: #fff;
            text-align: center;
            padding: 18px 0;
            font-size: 18px;
            font-weight: 600;
            border-radius: 14px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 10px rgba(67, 160, 71, 0.4);
        }

        .nav-buttons a:hover {
            background-color: #2e7d32;
            box-shadow: 0 6px 14px rgba(46, 125, 50, 0.6);
        }

        .logout {
            margin-top: 40px;
            text-align: center;
        }

        .logout a {
            background-color: #c62828;
            color: white;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 17px;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(198, 40, 40, 0.4);
            transition: background-color 0.3s ease;
        }

        .logout a:hover {
            background-color: #8e1c1c;
            box-shadow: 0 6px 14px rgba(142, 28, 28, 0.6);
        }

        @media (max-width: 600px) {
            .nav-buttons a {
                flex: 1 1 100%;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <h1> Welcome to the control panel </h1>

        <div class="nav-buttons">
            <a href="../public/add_equipment.php">➕ Add equipment</a>
            <a href="../public/equipments.php">➕ Equipments List</a>

            <a href="../public/add_user.php">👤 Create User</a>

            <a href="../public/add_check_item.php">✅ إضافة الجيك (Checklist)</a>
            <a href="../public/users.php">👥 Users List </a>
        </div>

        <div class="logout">
            <a href="<?= BASE_URL ?>/public/logout.php"> Logout</a>
        </div>
    </div>

</body>

</html>