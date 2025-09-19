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
    <title>Events list</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        h1 {
            text-align: center;
            color: #1d8e96;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #c8e6c9;
            text-align: right;
        }

        th {
            background-color: #a8e6cf;
            color: #1d8e96;
        }

        tr:hover {
            background-color: #f1f8f4;
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

        @media (max-width: 768px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead tr {
                display: none;
            }

            tr {
                margin-bottom: 15px;
                border: 1px solid #c8e6c9;
                border-radius: 10px;
                padding: 10px;
            }

            td {
                text-align: right;
                /* ✅ كل النصوص يمين */
                padding: 8px 10px 8px 130px;
                /* ✅ نترك مساحة لليبل */
                position: relative;
                direction: rtl;
                /* ✅ يلتزم بالاتجاه */
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                position: absolute;
                right: 10px;
                /* ✅ الليبل على اليمين */
                top: 50%;
                transform: translateY(-50%);
                white-space: nowrap;
                color: #333;
            }

            td:last-child a {
                display: block;
                /* ✅ كل زر بسطر */
                width: 100%;
                /* ✅ ياخذ عرض كامل */
                text-align: center;
                /* ✅ النص بالوسط */
                margin-bottom: 6px;
                /* ✅ مسافة بين الأزرار */
            }

            td:last-child a:last-child {
                margin-bottom: 0;
                /* ✅ آخر زر بلا مسافة إضافية */
            }
        }
    </style>
</head>

<body>
    <?php renderNavbar('Today Requests', '/public/requester.php'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('today_requests'); ?>

        <main class="p-6 ml-4 md:pl-64">
            <h3 style="text-align:center;">Today Events List</h3>

            <table id="eventsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>title</th>
                        <th>Status</th>
                        <th>Equipment</th>
                        <th>Requester</th>
                        <th>Start date</th>
                        <th>End date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <!-- سيتم ملؤه عبر JavaScript -->
                </tbody>
            </table>
    </div>

    <script>
        const BASE_URL = <?= json_encode(BASE_URL) ?>;

        // 🔹 دالة جلب وعرض الأحداث
        function loadEvents() {
            const pad = n => String(n).padStart(2, '0');
            const now = new Date();
            const tzOffset = -now.getTimezoneOffset();
            const tz = `${tzOffset >= 0 ? '+' : '-'}${pad(Math.floor(Math.abs(tzOffset) / 60))}:${pad(Math.abs(tzOffset) % 60)}`;

            const today = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
            const start = `${today}T00:00:00${tz}`;
            const end = `${today}T23:59:00${tz}`;
            fetch(`${BASE_URL}/routes/events.php?action=all_events&start=${start}&end=${end}`)
                .then(res => res.json())
                .then(events => {
                    const tbody = document.getElementById('eventsTableBody');
                    tbody.innerHTML = '';

                    if (!Array.isArray(events)) {
                        tbody.innerHTML = '<tr><td colspan="7">❌ خطأ في جلب البيانات</td></tr>';
                        return;
                    }

                    if (events.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7">No Events</td></tr>';
                        return;
                    }

                    events.forEach((event, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${event.title || '-'}</td>
                            <td>${event.status || '-'}</td>
                            <td>${event.extendedProps.equipment_name || '-'}</td>
                            <td>${event.extendedProps.created_by_name || '-'}</td>
                            <td>${event.executer_start || '-'}</td>
                            <td>${event.end || '-'}</td>
                            <td>
                                ${event.status === 'pending' && event.extendedProps.created_by == <?= json_encode($_SESSION['user_id']) ?>
                                ? `<a href="#"
                                            data-id="${event.id}" 
                                            onclick="deleteEvent(event)" 
                                            style="background:#d32f2f; color:white; padding:6px 12px; border-radius:6px; text-decoration:none;">
                                            Delete
                                        </a>`
                                : '-'
                            }
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    document.getElementById('eventsTableBody').innerHTML =
                        '<tr><td colspan="7">⚠️ فشل في الاتصال بالخادم</td></tr>';
                });
        }

        // 🔹 دالة الحذف
        function deleteEvent(e) {
            e.preventDefault();

            const eventId = e.target.getAttribute('data-id');

            if (!eventId) {
                Swal.fire("خطأ", "لا يمكن تحديد الحدث", "error");
                return;
            }

            Swal.fire({
                title: "هل أنت متأكد؟",
                text: "لن تتمكن من التراجع بعد الحذف!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "تأكيد الحذف"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${BASE_URL}/routes/events.php?action=delete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: eventId
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire("تم الحذف", data.message || "تم حذف الحدث بنجاح", "success")
                                    .then(() => loadEvents()); // 🔄 إعادة تحميل القائمة بدون reload
                            } else {
                                Swal.fire("خطأ", data.message || "فشل حذف الحدث", "error");
                            }
                        })
                        .catch(() => {
                            Swal.fire("خطأ", "فشل الاتصال بالخادم", "error");
                        });
                }
            });
        }

        // 🔹 أول استدعاء عند فتح الصفحة
        loadEvents();
    </script>


</body>

</html>