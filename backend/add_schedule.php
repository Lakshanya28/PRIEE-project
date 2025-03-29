<?php
require_once 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['user_id'];
$shiftId = $input['shift_id'];
$date = $input['date'];

try {
    // Check if the user already has a schedule on this date
    $stmt = $pdo->prepare("SELECT id FROM schedules WHERE user_id = ? AND date = ?");
    $stmt->execute([$userId, $date]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'User already has a schedule on this date']);
        exit();
    }
    
    // Add new schedule
    $stmt = $pdo->prepare("INSERT INTO schedules (user_id, shift_id, date) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $shiftId, $date]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>