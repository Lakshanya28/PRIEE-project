<?php
require_once 'db_connect.php';

try {
    $stmt = $pdo->query("SELECT id, name, start_time, end_time FROM shifts ORDER BY start_time");
    $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'shifts' => $shifts]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>