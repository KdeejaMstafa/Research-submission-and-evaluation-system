<?php 
// In this page, the admin will either approve or reject the pending user registration request.

session_start();
require_once '../includes/db_connection.php';

// Access control: only admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role_level'] !== 'admin') {
    header("Location: ../templates/login-page.php");
    exit();
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($userId <= 0 || !in_array($action, ['approve', 'reject'])) {
        header("Location: ../templates/manage-users.php"); // redirect here if the request is invalid
        exit();
    }

    if ($action === 'approve') {
        // Approve user
        $stmt = $connect_db->prepare("UPDATE users SET status = 'approved' WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

    } elseif ($action === 'reject') {
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
    }
}
if ($action === 'approve') {
    $_SESSION['admin_message'] = "User approved successfully.";
} elseif ($action === 'reject') {
    $_SESSION['admin_message'] = "User rejected and removed.";
}


// Redirect back to manage users
header("Location: ../templates/admin-interface.php?page=manage-users");
exit();

?>