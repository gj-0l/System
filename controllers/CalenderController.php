<?php
// controllers/CalendarController.php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';

class CalendarController
{

    // Get events (optionally by date range or user)
    public static function getEvents($start = null, $end = null, $executer_start = null)
    {
        $db = Database::getInstance()->getConnection();

        // If start/end provided, filter between them -- FullCalendar sends ISO dates
        if ($start && $end) {
            $query = "
            SELECT events.*, 
                equipment.equipment_name, 
                creator.name AS created_by_name
            FROM events 
            LEFT JOIN equipment ON events.equipment_id = equipment.id
            LEFT JOIN users AS creator ON events.created_by = creator.id
            WHERE events.start >= ? 
              AND events.start <= ?
        ";
            if ($executer_start) {
                $query .= " AND events.executer_start IS NOT NULL ";
            }
            $stmt = $db->prepare($query);
            $stmt->execute([$start, $end]);
        } else {
            $stmt = $db->query("
            SELECT 
                events.*, 
                equipment.equipment_name, 
                creator.name AS created_by_name
            FROM events
            LEFT JOIN equipment ON events.equipment_id = equipment.id
            LEFT JOIN users AS creator ON events.created_by = creator.id
            ORDER BY events.start ASC

        ");
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map DB rows to FullCalendar event object shape
        $events = array_map(function ($r) {
            return [
                'id' => $r['id'],
                'token' => $r['token'] ?? null,
                'title' => $r['title'] ?? null,
                'status' => $r['status'] ?? null,
                'start' => $r['start'] ?? null,
                'executer_start' => $r['executer_start'],
                'end' => $r['end'] ?? null,
                'executer_end' => $r['executer_end'] ?? null,
                'extendedProps' => [
                    'equipment_name' => $r['equipment_name'] ?? null,
                    'area' => $r['area'] ?? null,
                    'location' => $r['location'] ?? null,
                    'worktype' => $r['worktype'] ?? null,
                    'description' => $r['description'] ?? null,
                    'created_by' => $r['created_by'] ?? null,
                    'created_by_name' => $r['created_by_name'] ?? null
                ]
            ];
        }, $rows);

        return $events;
    }


    //get events count by created_date
    // get events count by created_date
    public static function getEventsCountByDate($date = null)
    {
        $db = Database::getInstance()->getConnection();

        if ($date === null) {
            $date = date('Y-m-d'); // إذا ما مرر تاريخ، يجيب تاريخ اليوم
        }

        $stmt = $db->prepare("
        SELECT COUNT(*) as event_count
        FROM events 
        WHERE DATE(created_at) = ?
    ");
        $stmt->execute([$date]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['event_count'] : 0;
    }


    // Add event (returns inserted id)
    public static function addEvent($data)
    {
        $db = Database::getInstance()->getConnection();

        if (empty($data['title']) || empty($data['start']) || empty($data['equipment_id'])) {
            return ['success' => false, 'message' => 'Title, Equipment, and start date are required'];
        }

        if (empty($data['token'])) {
            return ['success' => false, 'message' => 'Something went wrong. Please try again.'];
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
            equipment_id, token, title, start, `end`, created_by,
            area, location, worktype, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $created_by = $_SESSION['user_id'] ?? null;

        $stmt->execute([
            $data['equipment_id'],
            $data['token'],
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

    // get event by token
    public static function getEventByToken($token)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
        SELECT events.*, equipment.equipment_name, users.name AS created_by, users.id AS created_by_id
        FROM events 
        LEFT JOIN equipment ON events.equipment_id = equipment.id
        LEFT JOIN users ON events.created_by = users.id
        WHERE events.token = ?
    ");
        $stmt->execute([$token]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            return [
                'id' => $event['id'],
                'token' => $event['token'],
                'title' => $event['title'],
                'start' => $event['start'],
                'end' => $event['end'],
                'equipment_name' => $event['equipment_name'],
                'area' => $event['area'],
                'location' => $event['location'],
                'worktype' => $event['worktype'],
                'description' => $event['description'],
                'created_by' => $event['created_by'],
                'created_by_id' => $event['created_by_id'], // ✅ هنا
                'status' => $event['status']
            ];
        } else {
            return null;
        }
    }


    // Start event
    // Start event
    public static function startEvent($eventId, $executerId)
    {
        $db = Database::getInstance()->getConnection();

        // تحقق من حالة الحدث
        $stmt = $db->prepare("SELECT status, token FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            return ['success' => false, 'message' => 'Event not found'];
        }

        if ($event['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Only pending events can be started'];
        }

        $stmt = $db->prepare("
        UPDATE events 
        SET executer_id = ?, 
            executer_start = NOW(), 
            status = 'started'
        WHERE id = ? AND status = 'pending'
    ");
        $success = $stmt->execute([$executerId, $eventId]);

        return ['success' => $success, 'message' => $success ? 'Event started successfully' : 'Failed to start event', 'token' => $event['token']];
    }

    // End event
    public static function endEvent($eventId)
    {
        $db = Database::getInstance()->getConnection();

        // تحقق من حالة الحدث
        $stmt = $db->prepare("SELECT status FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            return ['success' => false, 'message' => 'Event not found'];
        }

        if ($event['status'] !== 'started') {
            return ['success' => false, 'message' => 'Only started events can be ended'];
        }

        $stmt = $db->prepare("
        UPDATE events 
        SET executer_end = NOW(), 
            status = 'finished'
        WHERE id = ? AND status = 'started'
    ");
        $success = $stmt->execute([$eventId]);

        return ['success' => $success, 'message' => $success ? 'Event ended successfully' : 'Failed to end event'];
    }

    // Cancel event
    public static function cancelEvent($eventId, $executerId, $reason = null)
    {
        $db = Database::getInstance()->getConnection();

        // جيب حالة الحدث الحالية
        $stmt = $db->prepare("SELECT status, token FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            return ['success' => false, 'message' => 'Event not found'];
        }

        if (!$reason) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Cancellation reason is required'];
        }

        $status = $event['status'];
        $now = date('Y-m-d H:i:s');

        if ($status === 'pending') {
            $stmt = $db->prepare("
            UPDATE events 
            SET status = 'rejected',
                executer_id = :executer_id,
                executer_cancelled = 1,
                cancellation_date = :cancellation_date,
                cancellation_reason = :cancellation_reason
            WHERE id = :id
        ");
            $stmt->execute([
                ':executer_id' => $executerId,
                ':cancellation_date' => $now,
                ':cancellation_reason' => $reason,
                ':id' => $eventId
            ]);

        } elseif ($status === 'started') {
            $stmt = $db->prepare("
            UPDATE events 
            SET status = 'cancelled',
                executer_id = :executer_id,
                executer_cancelled = 1,
                cancellation_date = :cancellation_date,
                cancellation_reason = :cancellation_reason,
                executer_end = :executer_end
            WHERE id = :id
        ");
            $stmt->execute([
                ':executer_id' => $executerId,
                ':cancellation_date' => $now,
                ':cancellation_reason' => $reason,
                ':executer_end' => $now,
                ':id' => $eventId
            ]);

        } else {
            return ['success' => false, 'message' => 'Only pending or started events can be cancelled'];
        }

        return ['success' => true, 'message' => 'Event cancelled successfully', 'token' => $event['token']];
    }


    // Delete event by id
    public static function deleteEvent($id, $userId)
    {
        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
            DELETE FROM events 
            WHERE id = ? 
              AND status = 'pending' 
              AND created_by = ?
        ");
            $stmt->execute([$id, $userId]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'تم حذف الحدث بنجاح'];
            } else {
                return ['success' => false, 'message' => 'غير مسموح لك بحذف هذا الحدث'];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'خطأ غير متوقع: ' . $e->getMessage()
            ];
        }
    }

}
