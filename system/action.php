<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'execution') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_text = trim($_POST['action_text']);
    if ($action_text !== '') {
        $pdo = new PDO("mysql:host=localhost;dbname=user_system;charset=utf8mb4", "root", "");

        $stmt = $pdo->prepare("INSERT INTO actions (group_id, action_text) VALUES (?, ?)");
        $stmt->execute([$_SESSION['group_id'], $action_text]);
    }
}
header("Location: dashboard.php");
exit();
