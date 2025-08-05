<?php
// migration.php
// تأكد مسار ملف الـ config صحيح؛ إذا اسمه مختلف غيّره هنا
require_once __DIR__ . '/core/Database.php';


$migrationsDir = __DIR__ . '/migrations';

// احصل على اتصال PDO من الـ Singleton Database
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Throwable $e) {
    die("Failed to get DB connection: " . $e->getMessage() . PHP_EOL);
}

// اقرأ كل ملفات الـ migrations ورتبها بالحروف/تاريخ
$files = glob($migrationsDir . '/*.php');
sort($files);

// نفّذ كل ملف إذا كان يرجع callable (closure) ويقبّل PDO
foreach ($files as $file) {
    $basename = basename($file);
    $migration = require $file;

    if (!is_callable($migration)) {
        echo "Skipped (not callable): {$basename}" . PHP_EOL;
        continue;
    }

    try {
        // كل ملف migration يجب أن يكون function(PDO $pdo) { ... }
        $migration($pdo);
        echo "✅ Migrated: {$basename}" . PHP_EOL;
    } catch (Throwable $e) {
        echo "❌ Error in {$basename}: " . $e->getMessage() . PHP_EOL;
        // يمكنك التعليق على السطر التالي إذا كنت تريد الاستمرار عند الخطأ
        exit(1);
    }
}
