<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=user_system", "root", "");

// التأكد أن المستخدم مسجّل الدخول ومصنف كـ requester
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'requester') {
    header("Location: login.php");
    exit();
}

// جلب جميع المعدات
$equipments = $pdo->query("SELECT * FROM equipments")->fetchAll(PDO::FETCH_ASSOC);

// استخراج المعدات الجاهزة فقط (التي تم تقييم كل الـ checklist لها بـ ✅ فقط)
$readyEquipments = [];

foreach ($equipments as $equipment) {
    $equipment_id = $equipment['id'];

    // جلب جميع الخيارات المرتبطة بالمعدة
    $stmt = $pdo->prepare("SELECT id FROM checklist_options WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);
    $option_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($option_ids) === 0) continue;

    // التحقق من أن كل خيار له تقييم ✅ فقط (أحدث تقييم لكل خيار)
    $allOk = true;
    foreach ($option_ids as $option_id) {
        $stmt = $pdo->prepare("SELECT is_ok FROM checklist_responses WHERE option_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$option_id]);
        $result = $stmt->fetchColumn();

        if ($result != 1) {
            $allOk = false;
            break;
        }
    }

    if ($allOk) {
        $readyEquipments[] = $equipment;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>المعدات الجاهزة للحجز</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .equipment { border: 1px solid #ddd; padding: 10px; margin: 10px 0; }
        button { padding: 10px; }
    </style>
</head>
<body>
    <h2>المعدات الجاهزة للحجز</h2>

    <?php if (empty($readyEquipments)): ?>
        <p>لا توجد معدات جاهزة حالياً.</p>
    <?php else: ?>
        <?php foreach ($readyEquipments as $equipment): ?>
            <div class="equipment">
                <strong><?= htmlspecialchars($equipment['name']) ?></strong><br>
                <form method="get" action="reserve.php">
                    <input type="hidden" name="equipment_id" value="<?= $equipment['id'] ?>">
                    <button type="submit">احجز هذه المعدة</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
