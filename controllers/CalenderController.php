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
                SELECT events.*, equipment.equipment_name 
                FROM events 
                LEFT JOIN equipment ON events.equipment_id = equipment.id 
                WHERE events.start >= ? AND events.start <= ? 
                ORDER BY events.start ASC
            ");

            $stmt->execute([$start, $end]);
        } else {
            $stmt = $db->query("
                SELECT events.*, equipment.equipment_name 
                FROM events 
                LEFT JOIN equipment ON events.equipment_id = equipment.id 
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
                    'description' => $r['description']
                ]
            ];
        }, $rows);


        return $events;
    }

    // Add event (returns inserted id)
    public static function addEvent($data)
    {
        $db = Database::getInstance()->getConnection();
        // Validation (basic)
        if (empty($data['title']) || empty($data['start'])) {
            return ['success' => false, 'message' => 'Title and start are required'];
        }

        $stmt = $db->prepare("INSERT INTO events (equipment_id, title, start, `end`, created_by, area, location, worktype, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // you may replace created_by with session user id
        $created_by = $_SESSION['user_id'] ?? null;
        $stmt->execute([
            $data['equipment_id'],
            $data['title'],
            $data['start'],
            $data['end'] ?: null,
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
