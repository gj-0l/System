<?php
require_once __DIR__ . '/config/config.php';

session_start();
session_unset(); // حذف جميع متغيرات الجلسة
session_destroy(); // تدمير الجلسة
header("Location: " . BASE_URL . "/public/login.php"); // إعادة التوجيه إلى صفحة تسجيل الدخول
exit();
