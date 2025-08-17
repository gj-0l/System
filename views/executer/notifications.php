<?php
require_once '../core/Database.php';
require_once '../config/config.php';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title>Notifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
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
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

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
    <div class="navbar">
        <div>Mobile Equipment</div>
        <div><a href="logout.php" style="color:white; text-decoration: none;">Logout</a></div>
    </div>

    <div class="container">
        <div class="section-title">
            Notifications
            <span class="notification-badge" id="notif-count">0</span>
        </div>

        <a href="<?= BASE_URL ?>/public/checklist.php" class="link">All Asset Types</a>

        <div id="notifications-container">
            <!-- Notifications will be dynamically loaded here -->
        </div>
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
                        card.className = 'notif-card';
                        card.innerHTML = `
                        <a href="${notif.url || '#'}" class="notif-header" target="_blank">
                            ${notif.title || 'Notification'}
                            <div class="notif-header">From: ${notif.sender_name || 'System'}</div>
                            <div class="notif-body">
                                ${notif.title ? `<strong>${notif.title}</strong><br>` : ''}
                                ${notif.body}
                                ${notif.is_opened == 0 ? '<span class="notif-dot"></span>' : ''}
                            </div>
                            <div class="notif-time">${new Date(notif.created_at).toLocaleString()}</div>
                        </a>
            `;
                        container.appendChild(card);
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