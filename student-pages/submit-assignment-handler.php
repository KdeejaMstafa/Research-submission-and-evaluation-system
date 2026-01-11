<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_connection.php';
require_once '../includes/send_email.php';
require_once __DIR__ . '/../includes/notifications_handler.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /rses/templates/student-dashboard.php?page=submit-assignment&msg=missing_session");
    exit();
}

if (!isset($_POST['assignment_id'], $_FILES['paper_file'])) {
    header("Location: /rses/templates/student-dashboard.php?page=submit-assignment&msg=missing_data");
    exit();
}

$studentId = $_SESSION['user_id'];
$assignmentId = (int) $_POST['assignment_id'];
$file = $_FILES['paper_file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    header("Location: /rses/templates/student-dashboard.php?page=submit-assignment&assignment_id=$assignmentId&msg=upload_error");
    exit();
}

$allowedTypes = ['pdf', 'doc', 'docx'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedTypes)) {
    header("Location: /rses/templates/student-dashboard.php?page=submit-assignment&assignment_id=$assignmentId&msg=invalid_file_type");
    exit();
}

$uploadDir = '../uploads/research_papers/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$filename = 'paper_' . $studentId . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    header("Location: /rses/templates/student-dashboard.php?page=submit-assignment&assignment_id=$assignmentId&msg=move_failed");
    exit();
}

// Determine next version number
$versionStmt = $connect_db->prepare("SELECT MAX(version) AS latest_version FROM research_papers WHERE assignment_id = ? AND submitted_by = ?");
$versionStmt->bind_param("ii", $assignmentId, $studentId);
$versionStmt->execute();
$versionResult = $versionStmt->get_result();
$latestVersion = $versionResult->fetch_assoc()['latest_version'] ?? 0;
$versionStmt->close();

$newVersion = $latestVersion + 1;

// Insert submission
$stmt = $connect_db->prepare("INSERT INTO research_papers (assignment_id, submitted_by, submission_date, file_path, submission_status, version) VALUES (?, ?, NOW(), ?, 'submitted', ?)");
$stmt->bind_param("iisi", $assignmentId, $studentId, $targetPath, $newVersion);
$stmt->execute();
$stmt->close();

// Fetch student info
$studentStmt = $connect_db->prepare("SELECT username FROM users WHERE user_id = ?");
$studentStmt->bind_param("i", $studentId);
$studentStmt->execute();
$studentResult = $studentStmt->get_result();
$student = $studentResult->fetch_assoc();
$studentStmt->close();

// Fetch assignment and supervisor info
$assignmentStmt = $connect_db->prepare("SELECT a.title, a.created_by AS supervisor_user_id, u.username AS supervisor_username, u.email AS supervisor_email FROM assignments a JOIN users u ON a.created_by = u.user_id WHERE a.assignment_id = ?");
$assignmentStmt->bind_param("i", $assignmentId);
$assignmentStmt->execute();
$assignmentResult = $assignmentStmt->get_result();
$assignment = $assignmentResult->fetch_assoc();
$assignmentStmt->close();

$assignmentTitle = $assignment['title'];
$supervisorId = $assignment['supervisor_user_id'];
$supervisorUsername = $assignment['supervisor_username'];
$supervisorEmail = $assignment['supervisor_email'];
$submissionTime = date('F j, Y \a\t g:i A');

// Send alert email to supervisor
sendEmail(
  $supervisorEmail,
  "Assignment Submitted: {$assignmentTitle}",
  "
    <div style='font-family: Arial, sans-serif; font-size: 14px; color: #333;'>
      <p>Dear {$supervisorUsername},</p>
      <p>Student <strong>{$student['username']}</strong> has submitted the assignment titled <strong>{$assignmentTitle}</strong>.</p>
      <p>Please log in to the RSES system to evaluate it.</p>
      <p>Best regards,<br><strong>RSES System</strong></p>
    </div>
  "
);


// Create internal notification for student
createNotification(
  $studentId,
  'confirmation',
  'Submission Received',
  "Your paper titled '{$assignmentTitle}' was successfully submitted on {$submissionTime}."
);

// Redirect to confirmation
header("Location: /rses/templates/student-dashboard.php?page=submit-assignment&assignment_id=$assignmentId&msg=submission_success");
exit();