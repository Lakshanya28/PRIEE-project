<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['schedule_id'], $input['requested_id'], $input['reason'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$scheduleId = $input['schedule_id'];
$requestedId = $input['requested_id'];
$reason = $input['reason'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Verify the schedule exists and belongs to the requester
    $stmt = $pdo->prepare("SELECT id FROM schedules WHERE id = ? AND user_id = ?");
    $stmt->execute([$scheduleId, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Schedule not found or not owned by requester');
    }
    
    // Update schedule status
    $stmt = $pdo->prepare("UPDATE schedules SET status = 'swap_requested' WHERE id = ?");
    $stmt->execute([$scheduleId]);
    
    // Create swap request
    $stmt = $pdo->prepare("INSERT INTO swap_requests (requester_id, requested_id, schedule_id, reason) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $requestedId, $scheduleId, $reason]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>