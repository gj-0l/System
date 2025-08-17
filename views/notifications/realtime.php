<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /public/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Realtime Notifications</title>
    <script type="module" src="../../public/js/firebase-notifications.js"></script>
    <style>
        .notification {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h2>Realtime Notifications</h2>
    <div id="notifications-container"></div>
</body>

</html>