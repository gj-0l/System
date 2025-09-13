<?php
require_once '../core/Database.php';
require_once '../config/config.php';

require_once __DIR__ . '/../../tools/sidebar.php';
require_once __DIR__ . '/../../tools/navbar.php';

?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Show Equipments</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #1d8e96;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #a5d6a7;
            color: #333;
        }

        tr:hover {
            background-color: #f1f8e9;
        }

        .logout {
            text-align: left;
            margin-bottom: 20px;
        }

        .logout a {
            background-color: #d32f2f;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .logout a:hover {
            background-color: #b71c1c;
        }
    </style>
</head>

<body>
    <?php renderNavbar('Dashboard', '/public/executer.php'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('dashboard'); ?>

        <main class="p-6 ml-4 md:pl-64">
            <h3 style="text-align:center;">Equipment List</h3>

            <table id="equipmentsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Equipment Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="equipmentsTableBody">
                    <!-- سيتم ملؤه عبر JavaScript -->
                </tbody>
            </table>
    </div>

    <script>
        // ✅ جلب المعدات كلها مع الحالة من checklist_results
        fetch('../routes/executer.php',
            {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            }
        )
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('equipmentsTableBody');
                tbody.innerHTML = '';

                // إذا ما فيه بيانات
                if (!Array.isArray(data) || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3">لا يوجد بيانات</td></tr>';
                    return;
                }

                // ملأ الجدول بالبيانات
                data.forEach((eq, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                <td>${index + 1}</td>
                <td>${eq.equipment_name}</td>
                <td>${eq.status || '-'}</td>
            `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error fetching equipments:', error);
                document.getElementById('equipmentsTableBody').innerHTML =
                    '<tr><td colspan="3">فشل في الاتصال بالخادم</td></tr>';
            });

    </script>

</body>

</html>