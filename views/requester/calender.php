<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Calendar with Event Modal</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.0.0/index.global.min.css" rel="stylesheet">
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

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridDay',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                selectable: true,
                editable: true, // إن أردت السحب والتعديل مباشرة

                // ----------------- Add event (select) -----------------
                select: async function (info) {
                    const { value: formValues } = await Swal.fire({
                        title: 'Add New Event',
                        html: `
                            <div style="display:flex;flex-direction:column;gap:10px;text-align:left;">
                            <input id="event-title" class="swal2-input" placeholder="Event Title">

                            <select id="type" class="swal2-input" style="box-sizing:border-box;">
                                <option value="">-- Select Type --</option>
                                <option value="execution">Execution</option>
                                <option value="requester">Requester</option>
                                <option value="admin">Admin</option>
                            </select>

                            <input id="swal-start" class="swal2-input" placeholder="Start Date & Time">
                            <input id="swal-end" class="swal2-input" placeholder="End Date & Time (optional)">

                            <input id="area" class="swal2-input" placeholder="Area">
                            <input id="location" class="swal2-input" placeholder="Location">
                            <input id="worktype" class="swal2-input" placeholder="Work Type">

                            <textarea id="desc" class="swal2-textarea" placeholder="Additional details"></textarea>
                            </div>
                        `,
                        didOpen: () => {
                            // initialize flatpickr on the text inputs inside Swal
                            flatpickr('#swal-start', {
                                enableTime: true,
                                dateFormat: "Y-m-d H:i",
                                defaultDate: info.startStr
                            });
                            flatpickr('#swal-end', {
                                enableTime: true,
                                dateFormat: "Y-m-d H:i",
                                defaultDate: info.endStr || null
                            });
                        },
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Add',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            const title = document.getElementById('event-title').value.trim();
                            const start = document.getElementById('swal-start').value.trim();
                            const end = document.getElementById('swal-end').value.trim();
                            const type = document.getElementById('type').value;
                            const area = document.getElementById('area').value.trim();
                            const location = document.getElementById('location').value.trim();
                            const worktype = document.getElementById('worktype').value.trim();
                            const description = document.getElementById('desc').value.trim();

                            if (!title || !start) {
                                Swal.showValidationMessage('Please enter a title and a start date/time.');
                                return false;
                            }

                            return { title, start, end, type, area, location, worktype, description };
                        }
                    });

                    if (formValues) {
                        calendar.addEvent({
                            title: formValues.title,
                            start: formValues.start,
                            end: formValues.end || null,
                            extendedProps: {
                                type: formValues.type || '',
                                area: formValues.area || '',
                                location: formValues.location || '',
                                worktype: formValues.worktype || '',
                                description: formValues.description || ''
                            }
                        });

                        Swal.fire({
                            icon: 'success',
                            title: 'Added',
                            text: 'Event added successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }

                    calendar.unselect();
                },

                // ----------------- Click on event (view + delete) -----------------
                eventClick: function (info) {
                    console.log('Event clicked:', info.event); // debug: ensure click fires

                    const props = info.event.extendedProps || {};
                    const area = props.area || '—';
                    const location = props.location || '—';
                    const worktype = props.worktype || '—';
                    const type = props.type || '—';
                    const description = props.description || '';

                    const htmlContent = `
                        <div style="text-align:left;line-height:1.4;">
                            <h3 style="margin:0 0 8px;">${escapeHtml(info.event.title)}</h3>

                            <div><strong>From:</strong> ${formatDate(info.event.start)}</div>
                            ${info.event.end ? `<div><strong>To:</strong> ${formatDate(info.event.end)}</div>` : ''}

                            <hr style="margin:8px 0;">

                            <div><strong>Type:</strong> ${escapeHtml(type)}</div>
                            <div><strong>Area:</strong> ${escapeHtml(area)}</div>
                            <div><strong>Location:</strong> ${escapeHtml(location)}</div>
                            <div><strong>Work Type:</strong> ${escapeHtml(worktype)}</div>

                            ${description ? `<hr style="margin:8px 0;"><div><strong>Description:</strong><div style="margin-top:6px;">${escapeHtml(description)}</div></div>` : ''}
                        </div>
                    `;

                    Swal.fire({
                        title: 'Event Details',
                        html: htmlContent,
                        icon: 'info',
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: 'Close',
                        denyButtonText: '🗑 Delete Event',
                        showCancelButton: false
                    }).then((result) => {
                        if (result.isDenied) {
                            Swal.fire({
                                title: 'Are you sure?',
                                text: 'This event will be permanently deleted!',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, delete it',
                                cancelButtonText: 'Cancel'
                            }).then((confirmResult) => {
                                if (confirmResult.isConfirmed) {
                                    // remove from calendar UI
                                    info.event.remove();

                                    // optional: delete on backend
                                    // fetch(`${BASE_URL}/routes/events.php`, {
                                    //   method: 'POST',
                                    //   headers: { 'Content-Type': 'application/json' },
                                    //   body: JSON.stringify({ action: 'delete', id: info.event.id })
                                    // });

                                    Swal.fire('Deleted!', 'The event has been deleted.', 'success');
                                }
                            });
                        }
                    });
                },

                // you can add other options (events, eventSources, etc.)
            });

            // render after config
            calendar.render();

            // ----------------- helper functions (place once in your script) -----------------
            function formatDate(dateObj) {
                if (!dateObj) return '—';
                return dateObj.toLocaleString('en-US', {
                    month: '2-digit',
                    day: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }
            function escapeHtml(str) {
                if (typeof str !== 'string') return str;
                return str.replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }


            function formatDate(dateObj) {
                if (!dateObj) return '—';
                return dateObj.toLocaleString('en-US', {
                    month: '2-digit',
                    day: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            function escapeHtml(str) {
                if (typeof str !== 'string') return str;
                return str
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            calendar.render();
        });
    </script>

</body>

</html>