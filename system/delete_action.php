<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'execution') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_id = intval($_POST['action_id']);

    $pdo = new PDO("mysql:host=localhost;dbname=user_system;charset=utf8mb4", "root", "");

    // تحقق أن الإجراء ينتمي لنفس group_id لتجنب حذف غير مصرح به
    $stmt = $pdo->prepare("SELECT group_id FROM actions WHERE id = ?");
    $stmt->execute([$action_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['group_id'] === $_SESSION['group_id']) {
        $stmt = $pdo->prepare("DELETE FROM actions WHERE id = ?");
        $stmt->execute([$action_id]);
    }
}
header("Location: dashboard.php");
exit();
