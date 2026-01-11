<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_level'] !== 'admin') {
    header("Location: ../templates/login-page.php");
    exit();
}

$userId = intval($_POST['user_id'] ?? 0);
if ($userId <= 0) {
    $_SESSION['admin_message'] = "Invalid user ID.";
    header("Location: ../templates/admin-interface.php?page=manage-users");
    exit();
}

// Get role before deletion
$roleStmt = $connect_db->prepare("SELECT role_level FROM users WHERE user_id = ?");
$roleStmt->bind_param("i", $userId);
$roleStmt->execute();
$roleStmt->bind_result($role);
$roleStmt->fetch();
$roleStmt->close();

// Delete from role-specific table
if ($role === 'student') {
    $deleteRoleStmt = $connect_db->prepare("DELETE FROM students WHERE user_id = ?");
} elseif ($role === 'supervisor') {
    $deleteRoleStmt = $connect_db->prepare("DELETE FROM supervisors WHERE user_id = ?");
}

if (isset($deleteRoleStmt)) {
    $deleteRoleStmt->bind_param("i", $userId);
    $deleteRoleStmt->execute();
    $deleteRoleStmt->close();
}

// Delete from users table
$deleteUserStmt = $connect_db->prepare("DELETE FROM users WHERE user_id = ?");
$deleteUserStmt->bind_param("i", $userId);
$deleteUserStmt->execute();
$deleteUserStmt->close();

$_SESSION['admin_message'] = "User deleted successfully.";
header("Location: ../templates/admin-interface.php?page=manage-users");
exit();
?>