<?php
session_start();

// التأكد من أن المستخدم مسجل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'requester') {
    // إعادة التوجيه إذا لم يكن طالباً أو لم يسجل الدخول
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الطالب</title>
    <link rel="stylesheet" href="css/style.css"> <!-- اختياري لتنسيق الصفحة -->
</head>
<body>
    <div class="container">
        <h1>مرحباً، <?= htmlspecialchars($userName) ?> 👋</h1>
        <p>أنت الآن في لوحة تحكم الطالب (Requester).</p>

        <!-- مثال على بعض الخيارات التي يمكن إضافتها -->
        <ul>
            <li><a href="create_request.php">إنشاء طلب جديد</a></li>
            <li><a href="my_requests.php">عرض طلباتي</a></li>
            <li><a href="logout.php">تسجيل الخروج</a></li>
        </ul>
    </div>
</body>
</html>
