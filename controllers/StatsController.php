<?php
// controllers/StatsController.php
require_once __DIR__ . '/../core/Database.php';

class StatsController
{
    /**
     * Get statistics for a specific day
     */
    public static function getDailyStats($date)
    {
        $db = Database::getInstance()->getConnection();

        // 1. Get all equipment
        $stmt = $db->query("SELECT id, equipment_name, equipment_code, description FROM equipment ORDER BY id ASC");
        $equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Get checklist status for each equipment on $date
        $sqlChecklist = "
            SELECT 
                e.id,
                SUM(CASE WHEN cr.status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
                COUNT(cr.id) AS total_checks
            FROM equipment e
            LEFT JOIN checklist_items ci ON ci.equipment_id = e.id
            LEFT JOIN checklist_results cr ON cr.checklist_id = ci.id AND DATE(cr.date) = ?
            GROUP BY e.id
        ";
        $stmtChecklist = $db->prepare($sqlChecklist);
        $stmtChecklist->execute([$date]);
        $checklistData = [];
        while ($row = $stmtChecklist->fetch(PDO::FETCH_ASSOC)) {
            $checklistData[$row['id']] = $row;
        }

        // 3. Get active events for each equipment on $date
        $sqlEvents = "
            SELECT 
                equipment_id,
                COUNT(*) AS event_count
            FROM events
            WHERE DATE(start) = ? AND executer_cancelled = 0
            GROUP BY equipment_id
        ";
        $stmtEvents = $db->prepare($sqlEvents);
        $stmtEvents->execute([$date]);
        $eventData = [];
        while ($row = $stmtEvents->fetch(PDO::FETCH_ASSOC)) {
            $eventData[$row['equipment_id']] = (int) $row['event_count'];
        }

        // 4. Get events counts on $date (created on or scheduled for)
        // Requests created on this day
        $stmtCreated = $db->prepare("SELECT COUNT(*) FROM events WHERE DATE(created_at) = ?");
        $stmtCreated->execute([$date]);
        $requestsCreated = (int)$stmtCreated->fetchColumn();

        // Requests scheduled for this day
        $stmtScheduled = $db->prepare("SELECT COUNT(*) FROM events WHERE DATE(start) = ? AND executer_cancelled = 0");
        $stmtScheduled->execute([$date]);
        $requestsScheduled = (int)$stmtScheduled->fetchColumn();

        // Combine everything
        $outOfSystemList = [];
        $reservedList = [];
        $availableList = [];
        $allEquipmentStats = [];

        foreach ($equipments as $eq) {
            $eqId = $eq['id'];
            $rejectedCount = isset($checklistData[$eqId]) ? (int)$checklistData[$eqId]['rejected_count'] : 0;
            $totalChecks = isset($checklistData[$eqId]) ? (int)$checklistData[$eqId]['total_checks'] : 0;
            $hasEvents = isset($eventData[$eqId]) && $eventData[$eqId] > 0;
            
            $status = 'available'; // default
            
            if ($rejectedCount > 0) {
                $status = 'out_of_system';
            } elseif ($hasEvents) {
                $status = 'reserved';
            }
            
            $eqInfo = [
                'id' => $eqId,
                'equipment_name' => $eq['equipment_name'],
                'equipment_code' => $eq['equipment_code'],
                'description' => $eq['description'],
                'status' => $status,
                'events_count' => isset($eventData[$eqId]) ? $eventData[$eqId] : 0,
                'rejected_count' => $rejectedCount,
                'total_checks' => $totalChecks
            ];
            
            if ($status === 'out_of_system') {
                $outOfSystemList[] = $eqInfo;
            } elseif ($status === 'reserved') {
                $reservedList[] = $eqInfo;
            } else {
                $availableList[] = $eqInfo;
            }
            
            $allEquipmentStats[] = $eqInfo;
        }

        return [
            'date' => $date,
            'requests_created' => $requestsCreated,
            'requests_scheduled' => $requestsScheduled,
            'counts' => [
                'out_of_system' => count($outOfSystemList),
                'reserved' => count($reservedList),
                'available' => count($availableList),
                'total_equipment' => count($equipments)
            ],
            'lists' => [
                'out_of_system' => $outOfSystemList,
                'reserved' => $reservedList,
                'available' => $availableList
            ],
            'all_equipment' => $allEquipmentStats
        ];
    }

    /**
     * Get monthly statistics for export
     */
    public static function getMonthlyStats($month)
    {
        $db = Database::getInstance()->getConnection();

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $year = substr($month, 0, 4);
        $m = substr($month, 5, 2);

        $daysInMonth = (int)date('t', strtotime("$month-01"));

        // 1. Daily requests count (created and scheduled) for the month
        $sqlCreated = "
            SELECT DATE(created_at) AS date_str, COUNT(*) AS count 
            FROM events 
            WHERE created_at LIKE ?
            GROUP BY DATE(created_at)
        ";
        $stmtCreated = $db->prepare($sqlCreated);
        $stmtCreated->execute(["$month-%"]);
        $createdMap = $stmtCreated->fetchAll(PDO::FETCH_KEY_PAIR);

        $sqlScheduled = "
            SELECT DATE(start) AS date_str, COUNT(*) AS count
            FROM events
            WHERE start LIKE ? AND executer_cancelled = 0
            GROUP BY DATE(start)
        ";
        $stmtScheduled = $db->prepare($sqlScheduled);
        $stmtScheduled->execute(["$month-%"]);
        $scheduledMap = $stmtScheduled->fetchAll(PDO::FETCH_KEY_PAIR);

        // 2. Equipment out of system (checklist results = 'rejected') for the month
        $sqlRejections = "
            SELECT cr.date, e.id AS equipment_id, e.equipment_name, e.equipment_code, cr.status
            FROM checklist_results cr
            INNER JOIN checklist_items ci ON cr.checklist_id = ci.id
            INNER JOIN equipment e ON ci.equipment_id = e.id
            WHERE cr.date LIKE ? AND cr.status = 'rejected'
            GROUP BY cr.date, e.id
        ";
        $stmtRejections = $db->prepare($sqlRejections);
        $stmtRejections->execute(["$month-%"]);
        $rejectionsList = $stmtRejections->fetchAll(PDO::FETCH_ASSOC);

        // Map of date => list of rejected equipment IDs
        $rejectedByDate = [];
        foreach ($rejectionsList as $rej) {
            $d = $rej['date'];
            if (!isset($rejectedByDate[$d])) {
                $rejectedByDate[$d] = [];
            }
            $rejectedByDate[$d][] = $rej['equipment_id'];
        }

        // 3. Active events by date and equipment for the month
        $sqlMonthlyEvents = "
            SELECT DATE(start) AS date_str, equipment_id, COUNT(*) AS count
            FROM events
            WHERE start LIKE ? AND executer_cancelled = 0
            GROUP BY DATE(start), equipment_id
        ";
        $stmtMonthlyEvents = $db->prepare($sqlMonthlyEvents);
        $stmtMonthlyEvents->execute(["$month-%"]);
        $eventsByDateAndEq = [];
        while ($row = $stmtMonthlyEvents->fetch(PDO::FETCH_ASSOC)) {
            $d = $row['date_str'];
            $eqId = $row['equipment_id'];
            if (!isset($eventsByDateAndEq[$d])) {
                $eventsByDateAndEq[$d] = [];
            }
            $eventsByDateAndEq[$d][$eqId] = (int)$row['count'];
        }

        // 4. Get all equipment
        $stmtAllEq = $db->query("SELECT id, equipment_name, equipment_code FROM equipment ORDER BY id ASC");
        $allEquipment = $stmtAllEq->fetchAll(PDO::FETCH_ASSOC);
        $totalEqCount = count($allEquipment);

        // Build daily records for the whole month
        $dailyRecords = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf("%s-%02d", $month, $day);

            $reqCreated = isset($createdMap[$dateStr]) ? (int)$createdMap[$dateStr] : 0;
            $reqScheduled = isset($scheduledMap[$dateStr]) ? (int)$scheduledMap[$dateStr] : 0;

            $rejectedEqIds = isset($rejectedByDate[$dateStr]) ? $rejectedByDate[$dateStr] : [];
            $rejectedCount = count($rejectedEqIds);

            $reservedCount = 0;
            $availableCount = 0;

            foreach ($allEquipment as $eq) {
                $eqId = $eq['id'];
                if (in_array($eqId, $rejectedEqIds)) {
                    continue;
                }

                $hasEvents = isset($eventsByDateAndEq[$dateStr][$eqId]) && $eventsByDateAndEq[$dateStr][$eqId] > 0;
                if ($hasEvents) {
                    $reservedCount++;
                } else {
                    $availableCount++;
                }
            }

            $dailyRecords[] = [
                'date' => $dateStr,
                'requests_created' => $reqCreated,
                'requests_scheduled' => $reqScheduled,
                'out_of_system' => $rejectedCount,
                'reserved' => $reservedCount,
                'available' => $availableCount,
                'total_equipment' => $totalEqCount
            ];
        }

        return [
            'month' => $month,
            'daily_records' => $dailyRecords,
            'out_of_service_log' => $rejectionsList,
            'equipment_list' => $allEquipment
        ];
    }
}
