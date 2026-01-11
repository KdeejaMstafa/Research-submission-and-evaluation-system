<?php
require_once '../includes/db_connection.php';
session_start();

// Validate required fields
$userId = intval($_POST['user_id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = $_POST['role_level'] ?? '';


if ($userId <= 0 || !$username || !$email || !$role) {
    $_SESSION['admin_message'] = "Missing or invalid user data.";
    header("Location: ../templates/admin-interface.php?page=manage-users");
    exit();
}

// Update users table
$updateUser = $connect_db->prepare("UPDATE users SET username = ?, email = ?, role_level = ? WHERE user_id = ?");
$updateUser->bind_param("sssi", $username, $email, $role, $userId);
$updateUser->execute();
$updateUser->close();

// Update role-specific table
if ($role === 'student') {
    $institute = trim($_POST['institute_name'] ?? '');
    $program = trim($_POST['study_program'] ?? '');

    $stmt = $connect_db->prepare("UPDATE students SET institute_name = ?, study_program = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $institute, $program, $userId);
    $stmt->execute();
    $stmt->close();

} elseif ($role === 'supervisor') {
    $affiliation = trim($_POST['affiliation'] ?? '');
    $publications = trim($_POST['no_of_publications'] ?? '');
    $expertiseArray = $_POST['expertise'] ?? [];
    $expertiseString = implode(', ', array_map('trim', $expertiseArray));

    $stmt = $connect_db->prepare("UPDATE supervisors SET affiliation = ?, no_of_publications = ?, expertise = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $affiliation, $publications, $expertiseString, $userId);
    $stmt->execute();
    $stmt->close();
}

$_SESSION['admin_message'] = "User #$userId updated successfully.";
header("Location: ../templates/admin-interface.php?page=manage-users");
exit();
?>