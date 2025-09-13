<?php
require_once __DIR__ . '/../tools/sidebar.php';
require_once __DIR__ . '/../tools/navbar.php';

session_start();
$pdo = new PDO("mysql:host=localhost;dbname=user_system", "root", "");

// جلب قائمة المعدات
$equipments = $pdo->query("SELECT * FROM equipment")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipment_id = $_POST['equipment_id'];
    $options = $_POST['options'];
    $user_id = $_SESSION['user_id'];

    foreach ($options as $option_id => $value) {
        $stmt = $pdo->prepare("REPLACE INTO checklist_responses (equipment_id, option_id, is_ok, responded_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$equipment_id, $option_id, $value === 'ok' ? 1 : 0, $user_id]);
    }

    echo "تم الحفظ";
}
?>

<body>
    <?php renderNavbar('Dashboard'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('dashboard'); ?>

        <main class="p-6 ml-4 md:pl-64">
            <form method="post">
                <label>اختر المعدة:</label>
                <select name="equipment_id" required onchange="this.form.submit()">
                    <option value="">اختر</option>
                    <?php foreach ($equipments as $eq): ?>
                        <option value="<?= $eq['id'] ?>" <?= (isset($_POST['equipment_id']) && $_POST['equipment_id'] == $eq['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($eq['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </main>
    </div>
</body>

<?php if (isset($_POST['equipment_id'])):
    $equipment_id = $_POST['equipment_id'];
    $options = $pdo->prepare("SELECT * FROM checklist_options WHERE equipment_id = ?");
    $options->execute([$equipment_id]);
    $options = $options->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <form method="post">
        <input type="hidden" name="equipment_id" value="<?= $equipment_id ?>">
        <?php foreach ($options as $option): ?>
            <p><?= $option['option_text'] ?>
                <label><input type="radio" name="options[<?= $option['id'] ?>]" value="ok" required> ✅ صح</label>
                <label><input type="radio" name="options[<?= $option['id'] ?>]" value="not_ok"> ❌ خطأ</label>
            </p>
        <?php endforeach; ?>
        <button type="submit">حفظ التقييم</button>
    </form>
<?php endif; ?>