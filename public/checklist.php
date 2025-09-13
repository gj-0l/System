<?php
require_once __DIR__ . '/../tools/sidebar.php';
require_once __DIR__ . '/../tools/navbar.php';

require_once __DIR__ . '/../config/config.php';

session_start();
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>فحص المعدات</title>

    <!-- تعريف BASE_URL للاستخدام داخل JavaScript -->
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- تحميل ملف جافاسكربت مع defer -->
    <script src="<?= BASE_URL ?>/assets/js/checklist.js" defer></script>

    <!-- تضمين تنسيقات CSS -->
    <style>
        <?php include __DIR__ . '/../views/checklist/form_style.css'; ?>
    </style>
</head>

<body>
    <?php renderNavbar('All Asset Type', '/public/executer.php'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('all_asset_types'); ?>

        <main class="p-6 ml-4 md:pl-64" dir="rtl">
            <h2>فحص المعدات اليومية</h2>
            <div id="message" class="message" style="display: none;"></div>

            <label for="equipmentSelect">اختر المعدة:</label>
            <select id="equipmentSelect" class="select">
                <option value="">-- اختر --</option>
            </select>

            <form id="checklistForm" style="display:none;">
                <table id="checklistTable">
                    <thead>
                        <tr>
                            <th>اسم الفحص</th>
                            <th>الإجراء الابتدائي</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <button type="submit" class="btn">💾 حفظ النتائج</button>
            </form>

            <p id="noChecklist" style="display:none;">❌ لا توجد فحوصات مضافة لهذه المعدة بعد.</p>
        </main>
    </div>
</body>

</html>