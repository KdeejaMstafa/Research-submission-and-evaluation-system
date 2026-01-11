<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['assignment_id'])) {
  header("Location: /rses/templates/login-page.php");
  exit();
}

$studentId = $_SESSION['user_id'];
$assignmentId = $_POST['assignment_id'];

// Check if student already selected an assignment
$check = $connect_db->prepare("SELECT * FROM selected_assignments WHERE student_id = ?");
$check->bind_param("i", $studentId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
  // Already selected — redirect with message
  header("Location: /rses/templates/student-dashboard.php?page=view-available-assignments&msg=already_selected");

  exit();
}

$check->close();

// Insert selection
$stmt = $connect_db->prepare("INSERT INTO selected_assignments (student_id, assignment_id) VALUES (?, ?)");
$stmt->bind_param("ii", $studentId, $assignmentId);
$stmt->execute();
$stmt->close();
header("Location: /rses/templates/student-dashboard.php?page=view-available-assignments&msg=selection_success");
exit();
?>