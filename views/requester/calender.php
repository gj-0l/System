<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Calendar with Event Modal</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.0.0/index.global.min.css" rel="stylesheet"> -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.0.0/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
        }

        #calendar {
            max-width: 1000px;
            margin: 0 auto;
        }
    </style>
    <style>
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

    <div id="calendar"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            // helper functions
            function escapeHtml(s) {
                if (!s && s !== 0) return '';
                return String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatDate(d) {
                if (!d) return '';
                // English style: MM/DD/YYYY hh:mm AM/PM
                return new Date(d).toLocaleString('en-US', {
                    month: '2-digit',
                    day: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            async function fetchTypes() {
                // returns array of { id, slug, name } or [] on error
                try {
                    const res = await fetch(`${BASE_URL}/routes/events.php?action=types`);
                    if (!res.ok) throw new Error('Network response not ok');
                    const json = await res.json();
                    return Array.isArray(json) ? json : [];
                } catch (e) {
                    console.warn('fetchTypes error', e);
                    return [];
                }
            }

            // Initialize calendar
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                selectable: true,
                // editable: true,

                // Load events from backend (FullCalendar calls this when needed)
                events: function (fetchInfo, successCallback, failureCallback) {
                    const url = `${BASE_URL}/routes/events.php?action=events&start=${encodeURIComponent(fetchInfo.startStr)}&end=${encodeURIComponent(fetchInfo.endStr)}`;
                    fetch(url)
                        .then(r => {
                            if (!r.ok) throw new Error('Network response not ok');
                            return r.json();
                        })
                        .then(data => successCallback(data))
                        .catch(err => {
                            console.error('load events error', err);
                            failureCallback(err);
                        });
                },

                // ----------------- Add event (select) -----------------
                select: async function (info) {
                    // Fetch the types (equipment options) from backend
                    const types = await fetchTypes();
                    const typeOptionsHtml = types.length
                        ? types.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.equipment_name ?? t.slug)}</option>`).join('')
                        : '<option value="">-- No types available --</option>';

                    const { value: form } = await Swal.fire({
                        title: 'Add New Event',
                        html: `
                            <div style="display:flex;flex-direction:column;gap:10px;text-align:left;">
                                <input id="ev-title" class="swal2-input" placeholder="Event title">

                                <select id="ev-type" class="swal2-input" style="box-sizing: border-box; width:100%;">
                                <option value="">-- Select Equipment Type --</option>
                                ${typeOptionsHtml}
                                </select>

                                <input id="ev-start" class="swal2-input" placeholder="Start">
                                <input id="ev-end" class="swal2-input" placeholder="End (optional)">

                                <input id="ev-area" class="swal2-input" placeholder="Area">
                                <input id="ev-location" class="swal2-input" placeholder="Location">
                                <input id="ev-worktype" class="swal2-input" placeholder="Work type">

                                <textarea id="ev-desc" class="swal2-textarea" placeholder="Description"></textarea>
                            </div>
                        `,
                        didOpen: () => {
                            // initialize flatpickr on the inputs inside Swal
                            // Flatpickr works on selectors; ensure these IDs exist
                            flatpickr('#ev-start', {
                                enableTime: true,
                                dateFormat: "Y-m-d H:i",
                                defaultDate: info.startStr
                            });
                            flatpickr('#ev-end', {
                                enableTime: true,
                                dateFormat: "Y-m-d H:i",
                                defaultDate: info.endStr || null
                            });

                            // Fix select sizing inside Swal (in case global CSS needed)
                            const sel = document.getElementById('ev-type');
                            if (sel) {
                                sel.style.width = '100%';
                                sel.style.boxSizing = 'border-box';
                                sel.style.height = '2.5em';
                                sel.style.padding = '0 8px';
                            }
                        },
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Add',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            const equipment_id = parseInt(document.getElementById('ev-type').value);
                            const title = document.getElementById('ev-title').value.trim();
                            const start = document.getElementById('ev-start').value.trim();
                            const end = document.getElementById('ev-end').value.trim();
                            const area = document.getElementById('ev-area').value.trim();
                            const location = document.getElementById('ev-location').value.trim();
                            const worktype = document.getElementById('ev-worktype').value.trim();
                            const description = document.getElementById('ev-desc').value.trim();

                            if (!title || !start) {
                                Swal.showValidationMessage('Title and Start are required.');
                                return false;
                            }

                            return { equipment_id, title, start, end, area, location, worktype, description };
                        }
                    });

                    if (!form) {
                        calendar.unselect();
                        return;
                    }

                    // Send to backend to store
                    try {
                        const res = await fetch(`${BASE_URL}/routes/events.php?action=add`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(form)
                        });
                        const json = await res.json();

                        if (json.success && json.id) {
                            // add to calendar with returned id
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

                // ----------------- Click on event (view + delete) -----------------
                eventClick: function (info) {
                    const props = info.event.extendedProps || {};
                    const html = `
                                <div style="text-align:left; line-height:1.4;">
                                <strong>Title:</strong> ${escapeHtml(info.event.title)}<br/>
                                <strong>Equipment name:</strong> ${escapeHtml(props.equipment_name || '')}<br/>
                                <strong>From:</strong> ${formatDate(info.event.start)}<br/>
                                ${info.event.end ? `<strong>To:</strong> ${formatDate(info.event.end)}<br/>` : ''}
                                <strong>Area:</strong> ${escapeHtml(props.area || '')}<br/>
                                <strong>Location:</strong> ${escapeHtml(props.location || '')}<br/>
                                <strong>Work type:</strong> ${escapeHtml(props.worktype || '')}<br/>
                                ${props.description ? `<hr><div>${escapeHtml(props.description)}</div>` : ''}
                                </div>
                            `;

                    Swal.fire({
                        title: 'Event Details',
                        html,
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: 'Close',
                        denyButtonText: 'Delete'
                    }).then(async (r) => {
                        if (r.isDenied) {
                            const confirm = await Swal.fire({
                                title: 'Confirm delete?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, delete',
                                cancelButtonText: 'Cancel'
                            });
                            if (!confirm.isConfirmed) return;

                            // delete on backend
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
                }

                // other FullCalendar options can be added here
            });

            calendar.render();
        });</script>

</body>

</html>