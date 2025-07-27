<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=user_system", "root", "");

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'requester') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipment_id = $_POST['equipment_id'];
    $time_from = $_POST['time_from'];
    $time_to = $_POST['time_to'];
    $reserved_by = $_SESSION['user_id'];

    // تحقق أن الوقت منطقي
    if (strtotime($time_from) < strtotime($time_to)) {
        $stmt = $pdo->prepare("INSERT INTO reservations (equipment_id, reserved_by, time_from, time_to) VALUES (?, ?, ?, ?)");
        $stmt->execute([$equipment_id, $reserved_by, $time_from, $time_to]);
        $message = "تم حجز المعدة بنجاح.";
    } else {
        $message = "الرجاء التأكد من أن وقت النهاية بعد وقت البداية.";
    }
} elseif (isset($_GET['equipment_id'])) {
    $equipment_id = $_GET['equipment_id'];
    $stmt = $pdo->prepare("SELECT name FROM equipments WHERE id = ?");
    $stmt->execute([$equipment_id]);
    $equipment = $stmt->fetch();
    if (!$equipment) {
        die("المعدة غير موجودة.");
    }
} else {
    die("معدة غير محددة.");
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حجز المعدة</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        input { padding: 8px; margin: 8px 0; width: 100%; }
        button { padding: 10px; }
        .msg { color: green; }
    </style>
</head>
<body>

<?php if (isset($message)): ?>
    <p class="msg"><?= htmlspecialchars($message) ?></p>
    <a href="requester_view.php">العودة إلى القائمة</a>
<?php else: ?>
    <h2>حجز المعدة: <?= htmlspecialchars($equipment['name']) ?></h2>
    <form method="post">
        <input type="hidden" name="equipment_id" value="<?= $equipment_id ?>">
        <label>من:</label>
        <input type="datetime-local" name="time_from" required>
        <label>إلى:</label>
        <input type="datetime-local" name="time_to" required>
        <button type="submit">تنفيذ الحجز</button>
    </form>
<?php endif; ?>

</body>
</html>
