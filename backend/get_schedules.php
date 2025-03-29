<?php
require_once 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$startDate = $input['start_date'] ?? date('Y-m-d');
$endDate = $input['end_date'] ?? date('Y-m-d', strtotime('+6 days'));
$userId = $input['user_id'] ?? null;

try {
    $query = "SELECT s.id, s.date, s.status, 
              u.id as user_id, u.name as user_name,
              sh.id as shift_id, sh.name as shift_name, sh.start_time, sh.end_time,
              sr.id as swap_id
              FROM schedules s
              JOIN users u ON s.user_id = u.id
              JOIN shifts sh ON s.shift_id = sh.id
              LEFT JOIN swap_requests sr ON s.id = sr.schedule_id AND sr.status = 'pending'
              WHERE s.date BETWEEN :start_date AND :end_date";
    
    if ($userId) {
        $query .= " AND s.user_id = :user_id";
    }
    
    $query .= " ORDER BY s.date, sh.start_time";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    
    if ($userId) {
        $stmt->bindParam(':user_id', $userId);
    }
    
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'schedules' => $schedules]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>