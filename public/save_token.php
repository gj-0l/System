<?php
require_once __DIR__ . '/core/Database.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if ($token && $user_id) {
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("UPDATE users SET token = ? WHERE id = ?");
    $stmt->execute([$token, $user_id]);
}
