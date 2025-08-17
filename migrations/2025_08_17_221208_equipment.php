<?php

return function (PDO $pdo) {
    $sql = "
        CREATE TABLE IF NOT EXISTS equipment (
            `id` int NOT NULL,
            `equipment_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `equipment_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sql);
    echo "✅ equipment table created\n";
};