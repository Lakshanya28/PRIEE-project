<?php
require_once 'db_connect.php';

try {
    $query = "SELECT sr.id, sr.status, sr.reason, sr.created_at,
              s.date, sh.name as shift_name, sh.start_time, sh.end_time,
              u1.name as requester_name, u2.name as requested_name
              FROM swap_requests sr
              JOIN schedules s ON sr.schedule_id = s.id
              JOIN shifts sh ON s.shift_id = sh.id
              JOIN users u1 ON sr.requester_id = u1.id
              JOIN users u2 ON sr.requested_id = u2.id";
    
    // For faculty, only show their pending requests
    if ($_SESSION['role'] === 'faculty') {
        $query .= " WHERE sr.requester_id = :user_id AND sr.status = 'pending'";
    } else {
        // For admin, show all pending requests
        $query .= " WHERE sr.status = 'pending'";
    }
    
    $query .= " ORDER BY s.date DESC";
    
    $stmt = $pdo->prepare($query);
    
    if ($_SESSION['role'] === 'faculty') {
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
    }
    
    $stmt->execute();
    $swaps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'swaps' => $swaps]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>