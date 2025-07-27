<?php
$pdo = new PDO("mysql:host=localhost;dbname=equipment_system;charset=utf8", "root", "");
$date = date("Y-m-d");

$stmt = $pdo->query("
SELECT e.name 
FROM equipment e
JOIN checklist_items ci ON ci.equipment_id = e.id
JOIN checklist_results cr ON cr.checklist_id = ci.id AND cr.date = '$date'
GROUP BY e.id
HAVING SUM(cr.status = 'rejected') = 0
");

$ready_equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>المعدات الجاهزة للحجز اليوم:</h3>
<ul>
<?php foreach ($ready_equipment as $eq): ?>
    <li><?= $eq['name'] ?></li>
<?php endforeach; ?>
</ul>
