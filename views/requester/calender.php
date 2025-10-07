<?php
require_once '../core/Database.php';
require_once '../config/config.php';

require_once __DIR__ . '/../../tools/sidebar.php';
require_once __DIR__ . '/../../tools/navbar.php';

session_start()
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Requester</title>

    <!-- Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.0.0/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Styles -->
    <style>
        #calendar {
            max-width: 1000px;
            margin: 0 auto;
        }

        .btn-add-event {
            background: #14b8a6;
            color: white;
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-weight: 600;
        }

        .swal2-popup .swal2-input,
        .swal2-popup select.swal2-input {
            display: block;
            width: 100% !important;
            box-sizing: border-box !important;
            height: 2.625em !important;
            font-size: 14px !important;
            padding: 0 10px !important;
            margin: 1em auto;
            border: 1px solid #d1d5db !important;
        }

        /* الصفحة */
        .page-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #f9fafb;
            font-family: "Segoe UI", sans-serif;
        }

        /* الهيدر */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            flex-wrap: wrap;
            /* ✅ يخلي العناصر تنزل تحت على الموبايل */
        }

        .page-header h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 4px 0;
        }

        .header-actions {
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }

        .header-actions button {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            background: #14b8a6;
            color: white;
            transition: background 0.2s ease;
        }

        .header-actions button:hover {
            background: #0f766e;
        }

        /* الـ Calendar */
        .calendar-wrapper {
            flex: 1;
            padding: 12px;
        }

        #calendar {
            max-width: 100%;
            margin: auto;
            background: white;
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        /* 📱 Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-header h1 {
                font-size: 1.1rem;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
                margin-top: 8px;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1rem;
            }

            .header-actions button {
                flex: 1;
                font-size: 0.85rem;
            }

            #calendar {
                padding: 4px;
                border-radius: 8px;
            }
        }
    </style>
</head>

<body>
    <?php renderNavbar('Calendar', '/public/requester.php'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('calendar'); ?>

        <main class="p-6 ml-4 md:pl-64">
            <main class="calendar-wrapper">
                <button class="btn-add-event" style="margin-bottom: 12px;" id="btnAddEvent">Add Request</button>
                <div id="calendar"></div>
            </main>
        </main>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let todayEventCount = 0;
            const calendarEl = document.getElementById('calendar');

            /** ------------------- Helper Functions ------------------- **/
            const escapeHtml = str => !str && str !== 0 ? '' : String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const formatDate = dateStr => !dateStr ? '' : new Date(dateStr).toLocaleString('en-US', {
                month: '2-digit', day: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: true
            });

            const getTimeFromStr = datetimeStr => {
                if (!datetimeStr) return '';
                const d = new Date(datetimeStr);
                return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
            };

            const fetchTypes = async () => {
                try {
                    const res = await fetch(`${BASE_URL}/routes/events.php?action=types`);
                    if (!res.ok) throw new Error('Network response not ok');
                    const json = await res.json();
                    return Array.isArray(json) ? json : [];
                } catch (e) {
                    console.warn('fetchTypes error', e);
                    return [];
                }
            };

            /** ------------------- Token Generator ------------------- **/
            const createToken = () => {
                const now = new Date();
                return `${now.getFullYear()}.${now.getMonth() + 1}.${now.getDate()}.${todayEventCount + 1}`;
            };

            /** ------------------- Calendar Init ------------------- **/
            const eventColors = [
                "#93c5fd", // أزرق هادئ
                "#86efac", // أخضر فاتح
                "#fcd34d", // أصفر باستيل
                "#f9a8d4", // وردي هادئ
                "#a5b4fc", // بنفسجي فاتح
                "#67e8f9"  // سماوي
            ];

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "timeGridDay",
                headerToolbar: { start: null, center: "title", end: null },
                selectable: false, // ❌ يمنع الإضافة عن طريق تحديد وقت بالكالندر
                allDaySlot: false, // نخلي فقط الساعات
                slotMinTime: "08:00:00", // ✅ البداية من الساعة 8 صباحاً
                slotMaxTime: "24:00:00", // ✅ لحد 12 بالليل

                eventContent: function (arg) {
                    const time = document.createElement("span");
                    time.textContent = arg.timeText;
                    time.style.fontSize = "13px";
                    time.style.fontWeight = "600";
                    time.style.marginRight = "6px";
                    time.style.color = "#111827";

                    const title = document.createElement("span");
                    title.textContent = `- ${arg.event.extendedProps.equipment_name}` || "(بدون عنوان)";
                    title.style.fontSize = "13px";
                    title.style.fontWeight = "500";
                    title.style.color = "#1f2937";

                    const wrapper = document.createElement("div");
                    wrapper.style.display = "flex";
                    wrapper.style.alignItems = "center";
                    wrapper.style.gap = "4px";
                    wrapper.style.lineHeight = "1.3";

                    wrapper.appendChild(time);
                    wrapper.appendChild(title);

                    return { domNodes: [wrapper] };
                },



                eventDidMount: function (info) {
                    const color = eventColors[info.event.id % eventColors.length];

                    info.el.style.background = color;
                    info.el.style.border = "none";
                    info.el.style.borderRadius = "6px";
                    info.el.style.padding = "4px 6px";
                    info.el.style.color = "#1f2937"; // رمادي غامق للخط
                    info.el.style.fontSize = "13px";
                    info.el.style.cursor = "pointer";
                    info.el.style.transition = "all 0.2s ease";

                    // hover effect
                    info.el.addEventListener("mouseenter", () => {
                        info.el.style.filter = "brightness(0.95)";
                        info.el.style.boxShadow = "0 2px 6px rgba(0,0,0,0.15)";
                    });
                    info.el.addEventListener("mouseleave", () => {
                        info.el.style.filter = "brightness(1)";
                        info.el.style.boxShadow = "none";
                    });
                },

                events: async (fetchInfo, successCallback, failureCallback) => {
                    try {
                        const res = await fetch(`${BASE_URL}/routes/events.php?action=today_events&start=${encodeURIComponent(fetchInfo.startStr)}&end=${encodeURIComponent(fetchInfo.endStr)}`);
                        if (!res.ok) throw new Error("Network response not ok");
                        const data = await res.json();
                        successCallback(data);
                    } catch (err) {
                        console.error("load events error", err);
                        failureCallback(err);
                    }

                    try {
                        const res = await fetch(`${BASE_URL}/routes/events.php?action=events_count`);
                        if (!res.ok) throw new Error("Network response not ok");
                        todayEventCount = await res.json() || 0;
                    } catch (err) {
                        console.error("load events count error", err);
                    }
                },

                select: info => handleAddEvent(info.startStr, info.endStr)
            });


            calendar.render();

            const handleAddEvent = async (startStr = null, endStr = null) => {
                const startTime = getTimeFromStr(startStr);
                const endTime = getTimeFromStr(endStr || startStr);
                const types = await fetchTypes();

                const typeOptionsHtml = types.length
                    ? types.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.equipment_name ?? t.slug)}</option>`).join('')
                    : '<option value="">-- No types available --</option>';

                const { value: form } = await Swal.fire({
                    title: `Add New Request`,
                    html: `
                    <div style="display:flex;flex-direction:column;gap:10px;text-align:left;">
                        <input id="ev-title" class="swal2-input" placeholder="Event title">
                        <label>Wo: ${createToken()}</label>
                        <select id="ev-type" class="swal2-input">${typeOptionsHtml}</select>
                        <input id="ev-start" class="swal2-input" placeholder="Start time">
                        <input id="ev-end" class="swal2-input" placeholder="End time">
                        <input id="ev-area" class="swal2-input" placeholder="Area">
                        <input id="ev-location" class="swal2-input" placeholder="Location">
                        <input id="ev-worktype" class="swal2-input" placeholder="Work type">
                        <textarea id="ev-desc" class="swal2-textarea" placeholder="Description"></textarea>
                    </div>
        `,
                    didOpen: () => {
                        const now = new Date();
                        const minTime = "08:00";
                        const maxTime = "23:59";

                        flatpickr('#ev-start', {
                            noCalendar: true,
                            enableTime: true,
                            dateFormat: "H:i",
                            defaultDate: startTime,
                            minTime: minTime,
                            maxTime: maxTime
                        });

                        flatpickr('#ev-end', {
                            noCalendar: true,
                            enableTime: true,
                            dateFormat: "H:i",
                            defaultDate: endTime,
                            minTime: minTime,
                            maxTime: maxTime
                        });
                    },

                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Add',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        const equipment_id = parseInt(document.getElementById('ev-type').value);
                        const token = createToken();
                        const title = document.getElementById('ev-title').value.trim();
                        const startTime = document.getElementById('ev-start').value.trim();
                        const endTime = document.getElementById('ev-end').value.trim();
                        const area = document.getElementById('ev-area').value.trim();
                        const location = document.getElementById('ev-location').value.trim();
                        const worktype = document.getElementById('ev-worktype').value.trim();
                        const description = document.getElementById('ev-desc').value.trim();

                        if (!title || !startTime) {
                            Swal.showValidationMessage('Title and Start time are required.');
                            return false;
                        }

                        // 🔒 منع اختيار وقت فائت
                        const now = new Date();
                        const [hour, minute] = startTime.split(':').map(Number);
                        const currentHour = now.getHours();
                        const currentMinute = now.getMinutes();

                        if (hour < 8 || hour > 23 || (hour === 23 && minute > 59)) {
                            Swal.showValidationMessage('Time must be between 08:00 and 23:59.');
                            return false;
                        }

                        if (hour < currentHour || (hour === currentHour && minute < currentMinute)) {
                            Swal.showValidationMessage('Cannot select a past time.');
                            return false;
                        }


                        const today = new Date().toISOString().split('T')[0];
                        const start = `${today} ${startTime}:00`;
                        const end = endTime ? `${today} ${endTime}:00` : null;

                        return { equipment_id, token, title, start, end, area, location, worktype, description };
                    }
                });

                if (!form) return;

                try {
                    const res = await fetch(`${BASE_URL}/routes/events.php?action=add`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(form)
                    });
                    const json = await res.json();

                    if (json.success && json.id) {
                        // ✅ إعادة جلب الأحداث بعد الإضافة
                        try {
                            const fetchRes = await fetch(`${BASE_URL}/routes/events.php?action=all_events&start=${encodeURIComponent(calendar.view.currentStart.toISOString())}&end=${encodeURIComponent(calendar.view.currentEnd.toISOString())}`);
                            if (fetchRes.ok) {
                                const events = await fetchRes.json();
                                calendar.removeAllEvents(); // إزالة كل الأحداث القديمة
                                events.forEach(ev => calendar.addEvent(ev)); // إضافة كل الأحداث الجديدة
                            }

                            // تحديث todayEventCount
                            const countRes = await fetch(`${BASE_URL}/routes/events.php?action=events_count`);
                            if (countRes.ok) todayEventCount = await countRes.json() || 0;

                        } catch (err) {
                            console.error('Error updating events or todayEventCount', err);
                        }

                        Swal.fire({ icon: 'success', title: 'Added', text: 'Event added successfully.', timer: 1400, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', json.message || 'Add failed', 'error');
                    }
                } catch (e) {
                    console.error('add request error', e);
                    Swal.fire('Error', 'Server error while adding event', 'error');
                }
            };


            /** ------------------- Event Click ------------------- **/
            calendar.setOption('eventClick', async info => {
                const props = info.event.extendedProps || {};
                const html = `
                    <div style="text-align:left; line-height:1.4;">
                        <strong>Title:</strong> ${escapeHtml(info.event.title)}<br/>
                        <strong>Wo:</strong> ${props.token}<br/>
                        <strong>Created by:</strong> ${escapeHtml(props.created_by_name || '')}<br/>
                        <strong>Area:</strong> ${escapeHtml(props.area || '')}<br/>
                        <strong>Location:</strong> ${escapeHtml(props.location || '')}<br/>
                        <strong>Work type:</strong> ${escapeHtml(props.worktype || '')}<br/>
                        <strong>Status:</strong> ${props.status}<br/>
                        <strong>Equipment name:</strong> ${escapeHtml(props.equipment_name || '')}<br/>
                        <strong>Executer start:</strong> ${formatDate(props.executer_start) || '-'}<br/>
                        ${info.event.end ? `<strong>To:</strong> ${formatDate(info.event.end)}<br/>` : ''}
                        ${props.description ? `<hr><div>${escapeHtml(props.description)}</div>` : ''}
                    </div>
                `;

                const result = await Swal.fire({
                    title: 'Event Details',
                    html,
                    showCancelButton: false,
                    showDenyButton: true,
                    confirmButtonText: 'Close',
                    showDenyButton: false
                });

                if (result.isDenied) {
                    const confirm = await Swal.fire({
                        title: 'Confirm delete?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'Cancel'
                    });
                    if (!confirm.isConfirmed) return;

                    try {
                        const res = await fetch(`${BASE_URL}/routes/events.php?action=delete`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: info.event.id })
                        });
                        const json = await res.json();
                        if (json.success) {
                            info.event.remove();
                            Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
                        } else {
                            Swal.fire('Error', json.message || 'Delete failed', 'error');
                        }
                    } catch (e) {
                        console.error('delete event error', e);
                        Swal.fire('Error', 'Server error while deleting event', 'error');
                    }
                }
            });

            /** ------------------- External Add Request Button ------------------- **/
            document.getElementById('btnAddEvent').addEventListener('click', () => handleAddEvent());
        });
    </script>
</body>

</html>