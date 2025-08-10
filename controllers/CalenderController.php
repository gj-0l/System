<?php
// controllers/CalendarController.php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';

class CalendarController
{

    // Get events (optionally by date range or user)
    public static function getEvents($start = null, $end = null)
    {
        $db = Database::getInstance()->getConnection();

        // If start/end provided, filter between them -- FullCalendar sends ISO dates
        if ($start && $end) {
            $stmt = $db->prepare("
            SELECT events.*, equipment.equipment_name, users.name AS created_by
            FROM events 
            LEFT JOIN equipment ON events.equipment_id = equipment.id
            LEFT JOIN users ON events.created_by = users.id
            WHERE events.start >= ? AND events.start <= ?
            ORDER BY events.start ASC
        ");
            $stmt->execute([$start, $end]);
        } else {
            $stmt = $db->query("
            SELECT events.*, equipment.equipment_name, users.name AS created_by
            FROM events 
            LEFT JOIN equipment ON events.equipment_id = equipment.id
            LEFT JOIN users ON events.created_by = users.id
            ORDER BY events.start ASC
        ");
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map DB rows to FullCalendar event object shape
        $events = array_map(function ($r) {
            return [
                'id' => $r['id'],
                'title' => $r['title'],
                'start' => $r['start'],
                'end' => $r['end'],
                'extendedProps' => [
                    'equipment_name' => $r['equipment_name'],
                    'area' => $r['area'],
                    'location' => $r['location'],
                    'worktype' => $r['worktype'],
                    'description' => $r['description'],
                    'created_by' => $r['created_by'] // ✅ تمت إضافته هنا
                ]
            ];
        }, $rows);

        return $events;
    }


    // Add event (returns inserted id)
    public static function addEvent($data)
    {
        $db = Database::getInstance()->getConnection();

        if (empty($data['title']) || empty($data['start']) || empty($data['equipment_id'])) {
            return ['success' => false, 'message' => 'Title, Equipment, and start date are required'];
        }

        $start = $data['start'];
        $end = $data['end'] ?: $data['start']; // If no end, use start as fallback

        // ✅ تحقق من أن تاريخ الحجز هو اليوم فقط
        $startDateOnly = date('Y-m-d', strtotime($start));
        $today = date('Y-m-d');

        if ($startDateOnly !== $today) {
            return ['success' => false, 'message' => 'You can only book for today\'s date.'];
        }

        // ✅ تحقق من عدم وجود تداخل في الفترة الزمنية مع حجوزات سابقة لنفس المعدة
        $checkStmt = $db->prepare("
        SELECT COUNT(*) FROM events
        WHERE equipment_id = :equipment_id
        AND executer_cancelled = 0
        AND executer_end IS NULL
        AND NOT (
            events.end <= :new_start OR
            events.start >= :new_end
        )
    ");

        $checkStmt->execute([
            ':equipment_id' => $data['equipment_id'],
            ':new_start' => $start,
            ':new_end' => $end
        ]);

        $conflictCount = $checkStmt->fetchColumn();

        if ($conflictCount > 0) {
            return ['success' => false, 'message' => 'This equipment is already booked during the selected time.'];
        }

        // ✅ Proceed with insertion
        $stmt = $db->prepare("
        INSERT INTO events (
            equipment_id, title, start, `end`, created_by,
            area, location, worktype, description
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

        $created_by = $_SESSION['user_id'] ?? null;

        $stmt->execute([
            $data['equipment_id'],
            $data['title'],
            $start,
            $end,
            $created_by,
            $data['area'] ?? null,
            $data['location'] ?? null,
            $data['worktype'] ?? null,
            $data['description'] ?? null
        ]);

        $id = $db->lastInsertId();

        return ['success' => true, 'id' => $id];
    }




    // Delete event by id
    public static function deleteEvent($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => true];
    }
}
