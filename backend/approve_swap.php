<?php
require_once 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$swapId = $input['swap_id'];
$action = $input['action'];

try {
    $pdo->beginTransaction();
    
    // Update swap request status
    $stmt = $pdo->prepare("UPDATE swap_requests SET status = ? WHERE id = ?");
    $stmt->execute([$action, $swapId]);
    
    if ($action === 'approved') {
        // Get the swap request details
        $stmt = $pdo->prepare("SELECT schedule_id, requested_id FROM swap_requests WHERE id = ?");
        $stmt->execute([$swapId]);
        $swap = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Update the schedule with the new faculty member
        $stmt = $pdo->prepare("UPDATE schedules SET user_id = ?, status = 'swap_approved' WHERE id = ?");
        $stmt->execute([$swap['requested_id'], $swap['schedule_id']]);
    } else {
        // For rejected swaps, just set the schedule back to scheduled
        $stmt = $pdo->prepare("SELECT schedule_id FROM swap_requests WHERE id = ?");
        $stmt->execute([$swapId]);
        $swap = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("UPDATE schedules SET status = 'scheduled' WHERE id = ?");
        $stmt->execute([$swap['schedule_id']]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>