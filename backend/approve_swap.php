<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$swapId = $input['swap_id'] ?? null;
$action = $input['action'] ?? null;
$scheduleId = $input['schedule_id'] ?? null;

try {
    // Start transaction
    $pdo->beginTransaction();

    // 1. Update the swap request status
    $stmt = $pdo->prepare("UPDATE swap_requests SET status = ? WHERE id = ?");
    $stmt->execute([$action, $swapId]);

    // 2. Get the swap request details
    $stmt = $pdo->prepare("SELECT * FROM swap_requests WHERE id = ?");
    $stmt->execute([$swapId]);
    $swap = $stmt->fetch();

    if (!$swap) {
        throw new Exception("Swap request not found");
    }

    if ($action === 'approved') {
        // 3. Update the original schedule (change faculty)
        $stmt = $pdo->prepare("UPDATE schedules 
                              SET user_id = ?, status = 'swap_approved' 
                              WHERE id = ?");
        $stmt->execute([$swap['requested_id'], $scheduleId]);

        // 4. Update the requested faculty's schedule if they had one on this date
        $stmt = $pdo->prepare("SELECT id FROM schedules 
                              WHERE user_id = ? 
                              AND date = (SELECT date FROM schedules WHERE id = ?)");
        $stmt->execute([$swap['requested_id'], $scheduleId]);
        $existingSchedule = $stmt->fetch();

        if ($existingSchedule) {
            // If the requested faculty already had a shift, assign the requester to it
            $stmt = $pdo->prepare("UPDATE schedules 
                                  SET user_id = ?, status = 'swap_approved' 
                                  WHERE id = ?");
            $stmt->execute([$swap['requester_id'], $existingSchedule['id']]);
        }
    } else {
        // For rejected swaps, just reset the schedule status
        $stmt = $pdo->prepare("UPDATE schedules SET status = 'scheduled' WHERE id = ?");
        $stmt->execute([$scheduleId]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>