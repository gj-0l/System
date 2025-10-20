<?php

return function (PDO $pdo) {
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            `id` INT NOT NULL AUTO_INCREMENT,
            `manager_id` INT NULL,
            `token` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `email` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `type` ENUM('execution','requester','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `status` ENUM('inactive','active') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `fcm_token` TEXT COLLATE utf8mb4_unicode_ci,
            PRIMARY KEY (`id`),
            CONSTRAINT fk_users_manager
                FOREIGN KEY (`manager_id`)
                REFERENCES `users` (`id`)
                ON DELETE SET NULL
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "✅ users table created\n";
};