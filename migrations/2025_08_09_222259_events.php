<?php

return function (PDO $pdo) {
    $sql = "
        CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,

        equipment_id INT NULL,         -- 🔴 المعدة المرتبطة بالحدث
        title VARCHAR(255) NOT NULL,
        start DATETIME NOT NULL,
        end DATETIME NULL,

        executer_start DATETIME NULL,     -- وقت بدء التنفيذ
        executer_end DATETIME NULL,       -- وقت انتهاء التنفيذ
        executer_cancelled TINYINT(1) DEFAULT 0, -- هل تم إلغاء التنفيذ؟
        cancellation_reason TEXT NULL,    -- سبب الإلغاء
        cancellation_date DATETIME NULL,  -- تاريخ الإلغاء
        executer_id INT NULL,             -- المستخدم الذي نفذ أو ألغى التنفيذ

        created_by INT NULL,              -- المستخدم الذي أنشأ الحدث

        area VARCHAR(100) NULL,
        location VARCHAR(150) NULL,
        worktype VARCHAR(100) NULL,
        description TEXT NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        -- 🔗 العلاقات
        CONSTRAINT fk_event_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE SET NULL,
        CONSTRAINT fk_event_executer  FOREIGN KEY (executer_id)  REFERENCES users(id)     ON DELETE SET NULL,
        CONSTRAINT fk_event_creator   FOREIGN KEY (created_by)   REFERENCES users(id)     ON DELETE SET NULL

    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    ";
    $pdo->exec($sql);
    echo "✅ events table created\n";
};