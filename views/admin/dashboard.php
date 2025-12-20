<?php
require_once __DIR__ . '/../../tools/navbar.php';
require_once __DIR__ . '/../../tools/sidebar.php';

?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title></title>
    </title>
    <title> dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap" rel="stylesheet">
    <!-- Firebase SDKs -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <?php renderNavbar('Dashboard', '/public/admin.php'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('statistics'); ?>

        <main class="p-6 ml-4 md:pl-64">
            <!-- Dashboard Page -->
            <div class="max-w-7xl mx-auto p-6">
                <h1 class="text-2xl font-bold text-center mb-6 text-gray-700">
                    Equipment Events Dashboard
                </h1>

                <!-- Grid -->
                <div id="equipmentGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <!-- Boxes injected by JS -->
                </div>
            </div>


        </main>
    </div>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";

        fetch(`${BASE_URL}/routes/events.php?action=events_by_equipment`)
            .then(res => res.json())
            .then(data => {
                const grid = document.getElementById('equipmentGrid');
                grid.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {
                    grid.innerHTML = `
                        <div class="col-span-full text-center text-gray-500">
                            No equipment data
                        </div>`;
                    return;
                }

                data.forEach(item => {
                    const box = document.createElement('div');
                    box.className = `
                        bg-white rounded-xl shadow
                        p-5 flex flex-col items-center
                        hover:shadow-lg transition
                    `;

                    box.innerHTML = `
                        <div class="text-lg font-semibold text-gray-700 mb-2">
                            ${item.equipment_name ?? 'Unknown Equipment'}
                        </div>

                        <div class="text-4xl font-bold text-blue-600">
                            ${item.events_count}
                        </div>

                        <div class="text-sm text-gray-400 mt-1">
                            Events
                        </div>
                    `;

                    grid.appendChild(box);
                });
            })
            .catch(err => {
                console.error(err);
                document.getElementById('equipmentGrid').innerHTML = `
                    <div class="col-span-full text-center text-red-500">
                        Failed to load data
                    </div>`;
            });
    </script>

</body>

</html>