<?php

return function (PDO $pdo) {
    $sql = "
        CREATE TABLE IF NOT EXISTS checklist_results (
            `id` int NOT NULL,
            `checklist_id` int NOT NULL,
            `date` date NOT NULL,
            `status` enum('accepted','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'rejected'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sql);
    echo "✅ checklist_results table created\n";
};