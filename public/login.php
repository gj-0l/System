<?php
require_once __DIR__ . '/../config/config.php';

session_start();

// إذا المستخدم مسجل دخول مسبقًا، نوجهه مباشرة حسب نوعه
if (isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'execution':
            header("Location: " . BASE_URL . "/public/executer.php");
            exit();
        case 'requester':
            header("Location: " . BASE_URL . "/public/requester_calender.php");
            exit();
        case 'admin':
            header("Location: " . BASE_URL . "/public/dashboard.php");
            exit();
    }
}

// تمرير أي رسالة خطأ للجافاسكربت
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);

// استدعاء الواجهة
require_once __DIR__ . '/../views/auth/login_form.php';
