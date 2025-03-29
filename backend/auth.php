<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once 'db_connect.php';

$current_user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'name' => $_SESSION['name'],
    'role' => $_SESSION['role']
];
?>