<?php
require_once '../core/Database.php';
require_once '../config/config.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Events list</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #f1f1f1;
            padding: 20px;
        }

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

    <div class="container">
        <div class="logout">
            <a href="<?= BASE_URL ?>/public/logout.php">Logout</a>
        </div>

        <h3 style="text-align:center;">Today Events List</h3>

        <table id="eventsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>title</th>
                    <th>Status</th>
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
                        tbody.innerHTML = '<tr><td colspan="7">لا توجد أحداث</td></tr>';
                        return;
                    }

                    events.forEach((event, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${event.title || '-'}</td>
                            <td>${event.status || '-'}</td>
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