<?php
session_start();
session_unset(); // حذف جميع متغيرات الجلسة
session_destroy(); // تدمير الجلسة
header("Location: login.php"); // إعادة التوجيه إلى صفحة تسجيل الدخول
exit();
