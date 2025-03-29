<?php
require_once 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'];
$username = $input['username'];
$email = $input['email'];
$password = $input['password'];
$role = $input['role'] ?? 'faculty';

try {
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit();
    }
    
    // Add new user
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, email, role) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $name, $email, $role]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>