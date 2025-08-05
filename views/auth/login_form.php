<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <title>تسجيل الدخول</title>
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        /* نفس التنسيقات السابقة */
        /* ... */
    </style>
</head>

<body>

    <div class="container">
        <h2 class="title">تسجيل الدخول</h2>

        <form method="post" action="../routes/auth.php">
            <div class="input-field">
                <i class="fas fa-user"></i>
                <input type="email" name="email" placeholder="البريد الإلكتروني" required />
            </div>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="كلمة المرور" required />
            </div>
            <input type="submit" value="دخول" class="btn" />
            <p>لا تملك حساب؟ <a href="register.php">إنشاء حساب</a></p>
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: <?= json_encode($error) ?>,
                confirmButtonColor: '#43a047'
            });
        </script>
    <?php endif; ?>

</body>

</html>