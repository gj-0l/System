<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
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
    <div class="container">
        <h2>فحص المعدات اليومية</h2>
        <div id="message" class="message" style="display: none;"></div>

        <label for="equipmentSelect">اختر المعدة:</label>
        <select id="equipmentSelect">
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
            <button type="submit">💾 حفظ النتائج</button>
        </form>

        <p id="noChecklist" style="display:none;">❌ لا توجد فحوصات مضافة لهذه المعدة بعد.</p>
    </div>
</body>

</html>