<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/send_email.php';
require_once '../includes/notifications_handler.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /rses/templates/student-dashboard.php?page=resubmit-assignment&assignment_id=$assignmentId&msg=missing_session");
    exit();
}

$studentId = $_SESSION['user_id'];
$assignmentId = $_POST['assignment_id'] ?? null;

if (!$assignmentId || !isset($_FILES['paper_file'])) {
    header("Location: /rses/templates/student-dashboard.php?page=resubmit-assignment&assignment_id=$assignmentId&msg=missing_data");
    exit();
}

// Check for deadline and approval lock
$lockQuery = "
    SELECT a.due_date, e.status
    FROM assignments a
    LEFT JOIN research_papers rp ON rp.assignment_id = a.assignment_id AND rp.submitted_by = ?
    LEFT JOIN evaluations e ON e.paper_id = rp.paper_id
    WHERE a.assignment_id = ?
    ORDER BY e.evaluation_id DESC
    LIMIT 1
";
$lockStmt = $connect_db->prepare($lockQuery);
$lockStmt->bind_param("ii", $studentId, $assignmentId);
$lockStmt->execute();
$lockResult = $lockStmt->get_result();
$lockRow = $lockResult->fetch_assoc();
$lockStmt->close();

$deadline = $lockRow['due_date'];
$evaluationStatus = strtolower($lockRow['status'] ?? '');

if (strtotime($deadline) < time()) {
    header("Location: /rses/templates/student-dashboard.php?page=resubmit-assignment&assignment_id=$assignmentId&msg=deadline_passed");
    exit();
}

if ($evaluationStatus === 'approved') {
    header("Location: /rses/templates/student-dashboard.php?page=resubmit-assignment&assignment_id=$assignmentId&msg=already_approved");
    exit();
}

// Validate file type
$allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$file = $_FILES['paper_file'];

if (!in_array($file['type'], $allowedTypes)) {
    header("Location: /rses/templates/student-dashboard.php?page=resubmit-assignment&assignment_id=$assignmentId&msg=invalid_file_type");
    exit();
}

// Get latest version
$versionQuery = "SELECT MAX(version) AS latest_version FROM research_papers WHERE assignment_id = ? AND submitted_by = ?";
$stmt = $connect_db->prepare($versionQuery);
$stmt->bind_param("ii", $assignmentId, $studentId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$latestVersion = $row['latest_version'] ?? 1;
$newVersion = $latestVersion + 1;

// Save file
$uploadDir = '../uploads/research_papers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$filename = basename($file['name']);
$targetPath = $uploadDir . time() . '_' . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    header("Location: /rses/templates/student-dashboard.php?page=resubmit-assignment&assignment_id=$assignmentId&msg=move_failed");
    exit();
}

// Insert new version
$insertQuery = "
    INSERT INTO research_papers (assignment_id, submitted_by, file_path, submission_date, submission_status, version)
    VALUES (?, ?, ?, NOW(), 'submitted', ?)
";
$stmt = $connect_db->prepare($insertQuery);
$stmt->bind_param("iisi", $assignmentId, $studentId, $targetPath, $newVersion);

if ($stmt->execute()) {
    // Fetch student info
    $studentStmt = $connect_db->prepare("SELECT username FROM users WHERE user_id = ?");
    $studentStmt->bind_param("i", $studentId);
    $studentStmt->execute();
    $studentResult = $studentStmt->get_result();
    $student = $studentResult->fetch_assoc();
    $studentStmt->close();

    // Fetch assignment and supervisor info
    $assignmentStmt = $connect_db->prepare("
        SELECT a.title, u.username AS supervisor_username, u.email AS supervisor_email, u.user_id AS supervisor_id
        FROM assignments a
        JOIN users u ON a.created_by = u.user_id
        WHERE a.assignment_id = ?
    ");
    $assignmentStmt->bind_param("i", $assignmentId);
    $assignmentStmt->execute();
    $assignmentResult = $assignmentStmt->get_result();
    $assignment = $assignmentResult->fetch_assoc();
    $assignmentStmt->close();

    $assignmentTitle = $assignment['title'];
    $supervisorUsername = $assignment['supervisor_username'];
    $supervisorEmail = $assignment['supervisor_email'];
    $supervisorId = $assignment['supervisor_id'];
    $submissionTime = date('F j, Y \a\t g:i A');

    // ✅ Send email to supervisor
    sendEmail(
        $supervisorEmail,
        "Assignment Resubmitted: {$assignmentTitle}",
        "
        <div style='font-family: Arial, sans-serif; font-size: 14px; color: #333;'>
            <p>Dear {$supervisorUsername},</p>
            <p>Student <strong>{$student['username']}</strong> has <strong>resubmitted</strong> the assignment titled <strong>{$assignmentTitle}</strong>.</p>
            <p>Please log in to the RSES system to review the updated version.</p>
            <p>Best regards,<br><strong>RSES System</strong></p>
        </div>
        "
    );

    // ✅ Internal notification to supervisor
    createNotification(
        $supervisorId,
        'resubmission',
        'Assignment Resubmitted',
        "{$student['username']} has resubmitted '{$assignmentTitle}' on {$submissionTime}. Please review the updated version."
    );

    // ✅ Internal confirmation to student
    createNotification(
        $studentId,
        'confirmation',
        'Resubmission Successful',
        "Your paper titled '{$assignmentTitle}' was successfully resubmitted on {$submissionTime}."
    );

    header("Location: /rses/templates/student-dashboard.php?page=resubmit-assignment&assignment_id=$assignmentId&msg=resubmission_success");
    exit();
} else {
    header("Location: /rses/templates/student-dashboard.php?page=resubmit-assignment&assignment_id=$assignmentId&msg=upload_error");
    exit();
}
?>