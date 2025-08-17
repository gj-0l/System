<?php

return function (PDO $pdo) {
    $sql = "
        CREATE TABLE IF NOT EXISTS checklist_items (
            `id` int NOT NULL,
            `equipment_id` int NOT NULL,
            `test_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `initial_action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `default_status` enum('accepted','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'accepted',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sql);
    echo "✅ checklist_items table created\n";
};