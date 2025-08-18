<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Calendar with Event Modal</title>

    <!-- Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.0.0/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Styles -->
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
        }

        .navbar {
            background: #0f766e;
            padding: 16px 24px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        #calendar {
            max-width: 1000px;
            margin: 0 auto;
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
        }
    </style>
</head>

<body>
    <div class="navbar">
        <div>Mobile Equipment</div>
        <div><a href="logout.php" style="color:white; text-decoration: none;">Logout</a></div>
    </div>

    <div id="calendar"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const calendarEl = document.getElementById('calendar');

            /** ------------------- Helper Functions ------------------- **/

            const escapeHtml = (str) => {
                if (!str && str !== 0) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };

            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                return new Date(dateStr).toLocaleString('en-US', {
                    month: '2-digit',
                    day: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            };

            const getTimeFromStr = (datetimeStr) => {
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

            /** ------------------- Initialize Calendar ------------------- **/

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridDay',
                headerToolbar: { start: null, center: 'title', end: null },
                selectable: true,

                events: async (fetchInfo, successCallback, failureCallback) => {
                    try {
                        const url = `${BASE_URL}/routes/events.php?action=events&start=${encodeURIComponent(fetchInfo.startStr)}&end=${encodeURIComponent(fetchInfo.endStr)}`;
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('Network response not ok');
                        const data = await res.json();
                        successCallback(data);
                    } catch (err) {
                        console.error('load events error', err);
                        failureCallback(err);
                    }
                },

                /** ------------------- Add New Event ------------------- **/
                select: async (info) => {
                    const startTime = getTimeFromStr(info.startStr);
                    const endTime = getTimeFromStr(info.endStr || info.startStr);
                    const types = await fetchTypes();

                    const typeOptionsHtml = types.length
                        ? types.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.equipment_name ?? t.slug)}</option>`).join('')
                        : '<option value="">-- No types available --</option>';

                    const { value: form } = await Swal.fire({
                        title: 'Add New Event',
                        html: `
                            <div style="display:flex;flex-direction:column;gap:10px;text-align:left;">
                                <input id="ev-title" class="swal2-input" placeholder="Event title">
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
                            flatpickr('#ev-start', {
                                noCalendar: true,
                                enableTime: true,
                                dateFormat: "H:i",
                                defaultDate: startTime
                            });
                            flatpickr('#ev-end', {
                                noCalendar: true,
                                enableTime: true,
                                dateFormat: "H:i",
                                defaultDate: endTime
                            });
                        },
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Add',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            const equipment_id = parseInt(document.getElementById('ev-type').value);
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

                            const today = new Date().toISOString().split('T')[0];
                            const start = `${today} ${startTime}:00`;
                            const end = endTime ? `${today} ${endTime}:00` : null;

                            return { equipment_id, title, start, end, area, location, worktype, description };
                        }
                    });

                    if (!form) {
                        calendar.unselect();
                        return;
                    }

                    try {
                        const res = await fetch(`${BASE_URL}/routes/events.php?action=add`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(form)
                        });
                        const json = await res.json();

                        if (json.success && json.id) {
                            calendar.addEvent({
                                id: json.id,
                                equipment_id: form.equipment_id,
                                title: form.title,
                                start: form.start,
                                end: form.end || null,
                                extendedProps: {
                                    type: form.type,
                                    area: form.area,
                                    location: form.location,
                                    worktype: form.worktype,
                                    description: form.description
                                }
                            });
                            Swal.fire({ icon: 'success', title: 'Added', text: 'Event added successfully.', timer: 1400, showConfirmButton: false });
                        } else {
                            Swal.fire('Error', json.message || 'Add failed', 'error');
                        }
                    } catch (e) {
                        console.error('add event error', e);
                        Swal.fire('Error', 'Server error while adding event', 'error');
                    }

                    calendar.unselect();
                },

                /** ------------------- Event Click (Details + Delete) ------------------- **/
                eventClick: async (info) => {
                    const props = info.event.extendedProps || {};
                    const html = `
                        <div style="text-align:left; line-height:1.4;">
                        <strong>Title:</strong> ${escapeHtml(info.event.title)}<br/>
                        <strong>Equipment name:</strong> ${escapeHtml(props.equipment_name || '')}<br/>
                        <strong>Created by:</strong> ${escapeHtml(props.created_by || '')}<br/>
                        <strong>From:</strong> ${formatDate(info.event.start)}<br/>
                        ${info.event.end ? `<strong>To:</strong> ${formatDate(info.event.end)}<br/>` : ''}
                        <strong>Area:</strong> ${escapeHtml(props.area || '')}<br/>
                        <strong>Location:</strong> ${escapeHtml(props.location || '')}<br/>
                        <strong>Work type:</strong> ${escapeHtml(props.worktype || '')}<br/>
                        ${props.description ? `<hr><div>${escapeHtml(props.description)}</div>` : ''}
                        </div>
                    `;

                    const result = await Swal.fire({
                        title: 'Event Details',
                        html,
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: 'Close',
                        denyButtonText: 'Delete'
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
                }
            });

            calendar.render();
        });
    </script>

</body>

</html>