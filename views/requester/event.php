<?php
require_once '../core/Database.php';
require_once '../config/config.php';
require_once '../controllers/CalenderController.php';

require_once __DIR__ . '/../../tools/sidebar.php';
require_once __DIR__ . '/../../tools/navbar.php';

$token = $_GET['id'] ?? null;
if (!$token) {
    die("Missed Token");
}

$event = CalendarController::getEventByToken($token);
if (!$event) {
    die("Event not found");
}

$status = strtolower(trim($event['status']));
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Event Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .card {
            background: #fff;
            margin: 16px;
            padding: 20px;
            border-radius: 12px;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .card h2 {
            margin: 0 0 16px;
            font-size: 1.4rem;
            color: #111827;
        }

        .details {
            display: grid;
            grid-template-columns: 120px 1fr;
            row-gap: 10px;
            column-gap: 16px;
        }

        .label {
            font-weight: 600;
            color: #374151;
        }

        .value {
            color: #111827;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .actions button {
            flex: 1;
            min-width: 120px;
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: 0.2s ease;
        }

        .btn-approve {
            background: #14b8a6;
            color: #fff;
        }

        .btn-approve:hover {
            background: #0d9488;
        }

        .btn-reject {
            background: #ef4444;
            color: #fff;
        }

        .btn-reject:hover {
            background: #dc2626;
        }

        .btn-start {
            background: #f59e0b;
            color: #fff;
        }

        .btn-start:hover {
            background: #d97706;
        }

        .btn-end {
            background: #3b82f6;
            color: #fff;
        }

        .btn-end:hover {
            background: #2563eb;
        }

        .message {
            margin-top: 15px;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .success {
            color: #16a34a;
        }

        .error {
            color: #dc2626;
        }

        @media (max-width: 600px) {
            .details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php renderNavbar('Event details', '/public/requester.php'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar(''); ?>

        <main class="p-6 ml-4 md:pl-64">
            <div class="card">
                <h2>Event Details</h2>

                <!-- تفاصيل الحدث -->
                <div class="details">
                    <div class="label">Title:</div>
                    <div class="value" id="value-title"></div>
                    <div class="label">PR:</div>
                    <div class="value" id="value-pr"></div>

                    <div class="label">Equipment:</div>
                    <div class="value" id="value-equipment"></div>

                    <div class="label">Area:</div>
                    <div class="value" id="value-area"></div>

                    <div class="label">Location:</div>
                    <div class="value" id="value-location"></div>

                    <div class="label">Work Type:</div>
                    <div class="value" id="value-type"></div>

                    <div class="label">Description:</div>
                    <div class="value" id="value-description"></div>

                    <div class="label">From:</div>
                    <div class="value" id="value-from"></div>

                    <div class="label">To:</div>
                    <div class="value" id="value-to"></div>

                    <div class="label">Created By:</div>
                    <div class="value" id="value-created-by"></div>

                    <div class="label">Status:</div>
                    <div class="value" id="status-value"></div>

                    <div class="label">Cancellation note:</div>
                    <div class="value" id="cancellation-note"></div>
                </div>

                <div id="message" class="message"></div>
            </div>
        </main>
    </div>

    <script>
        const token = "<?= $token ?>";
        const id = "<?= $event['id'] ?>";
        const executerId = "<?= $_SESSION['user_id'] ?? '' ?>";
        let requesterId = null;

        // تحميل تفاصيل الحدث
        function loadEventDetails() {
            fetch(`../routes/events.php?action=event&token=${encodeURIComponent(token)}`)
                .then(res => res.json())
                .then(event => {
                    if (!event || event.error) {
                        showMessage("❌ لم يتم العثور على الحجز", true);
                        return;
                    }

                    requesterId = event.created_by_id || null;

                    document.getElementById("value-title").textContent = event.title || '';
                    document.getElementById("value-pr").textContent = event.token || '';
                    document.getElementById("value-equipment").textContent = event.equipment_name || '';
                    document.getElementById("value-area").textContent = event.area || '';
                    document.getElementById("value-location").textContent = event.location || '';
                    document.getElementById("value-type").textContent = event.worktype || '';
                    document.getElementById("value-description").textContent = event.description || '';
                    document.getElementById("value-from").textContent = event.start || '';
                    document.getElementById("value-to").textContent = event.end || '';
                    document.getElementById("value-created-by").textContent = event.created_by || '';
                    document.getElementById("status-value").textContent = event.status || '';
                    document.getElementById("cancellation-note").textContent = event.cancellation_reason || '';
                })
                .catch(() => {
                    showMessage("❌ خطأ بالاتصال بالسيرفر", true);
                });
        }

        // دالة الرسائل
        function showMessage(text, isError = false) {
            const msg = document.getElementById("message");
            msg.textContent = text;
            msg.className = "message " + (isError ? "error" : "success");
        }

        // تحميل الصفحة
        window.onload = loadEventDetails;
    </script>
</body>

</html>