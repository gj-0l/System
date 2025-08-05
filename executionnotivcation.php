<?php
require_once 'core/Database.php';
require_once 'config/config.php';
$db = Database::getInstance()->getConnection();

// $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
// $stmt->execute(['email' => 'hader12+1@gmail.com']);
// $users = $stmt->fetchAll();

// print_r($users);
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
      <span class="notification-badge">3</span>
    </div>

    <a href="<?= BASE_URL ?>/public/checklist.php" class="link">All Asset Types</a>

    <div class="notif-card">
      <div class="notif-header">From: Driver</div>
      <div class="notif-body">
        Reacted to your recent post <strong>Driver End</strong>
        <span class="notif-dot"></span>
      </div>
      <div class="notif-time">Just now</div>
    </div>

    <div class="notif-card">
      <div class="notif-header">From: Driver</div>
      <div class="notif-body">
        Reacted to your recent post <strong>Driver Start</strong>
        <span class="notif-dot"></span>
      </div>
      <div class="notif-time">Just now</div>
    </div>

    <div class="notif-card">
      <div class="notif-header">From: Driver</div>
      <div class="notif-body">
        Asset type: <strong>Test 2</strong><br>
        Description: <strong>Test des22</strong> accepted
        <span class="notif-dot"></span>
      </div>
      <div class="notif-time">7 minutes ago</div>
    </div>
  </div>

</body>

</html>