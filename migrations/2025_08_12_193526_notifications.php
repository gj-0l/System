<?php

return function (PDO $pdo) {
    $sql = "
        CREATE TABLE notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            body TEXT,
            user_id INT NOT NULL,
            url VARCHAR(255),
            is_opened TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            sender_id INT DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
        );
    ";
    $pdo->exec($sql);
    echo "✅ notifications table created\n";
};