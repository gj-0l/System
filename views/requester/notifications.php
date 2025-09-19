<?php
require_once '../core/Database.php';
require_once '../config/config.php';

require_once __DIR__ . '/../../tools/sidebar.php';
require_once __DIR__ . '/../../tools/navbar.php';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Notifications</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .section-title {
            font-size: 26px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #0f172a;
            display: flex;
            align-items: center;
        }

        .notification-badge {
            background: #ef4444;
            color: white;
            border-radius: 9999px;
            padding: 4px 10px;
            font-size: 14px;
            margin-left: 10px;
        }

        .notif-card {
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .notif-card:last-child {
            border-bottom: none;
        }

        .notif-header {
            font-weight: 600;
            color: #334155;
            text-decoration: none;
        }

        .notif-body {
            font-size: 15px;
            color: #475569;
        }

        .notif-time {
            font-size: 13px;
            color: #94a3b8;
        }

        .notif-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            margin-left: 5px;
        }

        .notif-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-decoration: none;
            color: inherit;
        }

        .notif-left {
            flex-shrink: 0;
        }

        .notif-img {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }

        .notif-right {
            flex: 1;
        }

        .notif-header {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .notif-sender {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 6px;
        }

        .notif-body {
            margin-bottom: 6px;
        }

        .notif-time {
            font-size: 0.8em;
            color: #999;
        }


        .link {
            text-decoration: underline;
            color: #0f766e;
            margin-bottom: 20px;
            display: inline-block;
            font-weight: 500;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 20px;
            }

            .section-title {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <?php renderNavbar('Notifications', '/public/requester.php'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar(''); ?>

        <main class="p-6 ml-4 md:pl-64">
            <div class="section-title">
                Notifications
                <span class="notification-badge" id="notif-count">0</span>
            </div>

            <div id="notifications-container">
                <!-- Notifications will be dynamically loaded here -->
            </div>
        </main>
    </div>

    <script>
        fetch('../routes/notifications.php?action=get_notifications')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('notifications-container');
                    const badge = document.getElementById('notif-count');
                    const notifications = data.notifications;

                    badge.textContent = notifications.length;

                    if (notifications.length === 0) {
                        container.innerHTML = `<p style="color:#888">No notifications found.</p>`;
                    }

                    notifications.forEach(notif => {
                        const card = document.createElement('div');
                        card.className = `notif-card ${notif.is_opened == 1 ? 'opened' : ''}`;
                        card.innerHTML = `
                        <div class="notif-item" data-id="${notif.id}" data-url="${notif.url || '#'}">
                            <div class="notif-left">
                                <img src="../assets/images/logo.png" 
                                     width="40" height="40" alt="icon" class="notif-img">
                            </div>
                            <div class="notif-right">
                                <div class="notif-header">
                                    <strong>${notif.title || 'Notification'}</strong>
                                </div>
                                <div class="notif-sender">From: ${notif.sender_name || 'System'}</div>
                                <div class="notif-body">
                                    ${notif.body || ''}
                                    ${notif.is_opened == 0 ? '<span class="notif-dot"></span>' : ''}
                                </div>
                                <div class="notif-time">
                                    ${new Date(notif.created_at).toLocaleString()}
                                </div>
                            </div>
                        </div>
                    `;
                        container.appendChild(card);
                    });

                    // 👇 إضافة حدث الضغط
                    document.querySelectorAll('.notif-item').forEach(item => {
                        item.addEventListener('click', function () {
                            const notification_id = this.getAttribute('data-id');
                            const targetUrl = this.getAttribute('data-url');

                            fetch('../routes/notifications.php?action=mark_as_opened', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ notification_id: notification_id })
                            })
                                .then(res => res.json())
                                .then(result => {
                                    if (result.success) {
                                        this.classList.add('opened');      // يميز كمفتوح
                                        this.querySelector('.notif-dot')?.remove(); // يشيل النقطة الحمراء
                                    }
                                    window.location.href = targetUrl;
                                })
                                .catch(err => {
                                    console.error('Error marking as opened:', err);
                                    window.location.href = targetUrl;
                                });
                        });
                    });

                } else {
                    alert(data.message || 'Unable to load notifications.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    </script>

</body>

</html>