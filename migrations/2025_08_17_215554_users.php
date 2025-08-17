<?php

return function (PDO $pdo) {
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            `id` int NOT NULL,
            `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `type` enum('execution','requester','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `fcm_token` text COLLATE utf8mb4_unicode_ci
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sql);
    echo "✅ users table created\n";
};