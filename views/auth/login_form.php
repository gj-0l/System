<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title>KCML</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #e0f7ec, #a8e6cf);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 30px 25px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        .container img {
            max-width: 120px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }

        .input-field {
            position: relative;
            width: 100%;
        }

        .input-field i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            font-size: 16px;
            pointer-events: none;
        }

        .input-field input {
            width: 100%;
            box-sizing: border-box;
            /* ✅ يمنع الطلوع خارج الحدود */
            padding: 12px 40px 12px 12px;
            /* مكان للأيقونة */
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.3s ease;
            font-size: 14px;
        }

        .input-field input:focus {
            border-color: #0b6f76;
        }

        .btn {
            background: #0b6f76;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #095c62;
        }

        p {
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }

        p a {
            color: #0b6f76;
            text-decoration: none;
            font-weight: bold;
        }

        p a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .container {
                padding: 20px;
                border-radius: 10px;
            }

            .container img {
                max-width: 90px;
            }

            .btn {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <img src="../assets/images/logo.png" alt="Logo" style="max-width:210px;" />

        <h3 style="color: #0b6f76">Welcome to the Digitalization World</h3>
        <p>You are now entering the first step in the Digitalization World of
            reserving mobile equipment</p>

        <form method="post" action="../routes/auth.php">
            <div class="input-field">
                <i class="fas fa-user"></i>
                <input type="email" name="email" placeholder="Email" required />
            </div>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required />
            </div>
            <input type="submit" value="Login" class="btn" />
            <p>Not have an account? <a href="<?= BASE_URL ?>/public/add_user.php">Create account</a></p>
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: <?= json_encode($error) ?>,
                confirmButtonColor: '#0b6f76'
            });
        </script>
    <?php endif; ?>

</body>

</html>