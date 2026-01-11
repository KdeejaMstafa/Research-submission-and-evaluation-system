<?php
session_start();
require_once dirname(__DIR__) . '/includes/db_connection.php';
require_once dirname(__DIR__) . '/includes/send_email.php';

$paperId     = (int)$_POST['paper_id'];
$evaluatedBy = (int)$_POST['evaluated_by']; // supervisor_id
$status      = $_POST['status'];
$rating      = $_POST['rating'] ?? null;
$feedback    = $_POST['feedback'] ?? '';
$commentedFilePath = null;

// Handle file upload
if (!empty($_FILES['commented_file']['name'])) {
    $uploadDir  = '../uploads/commented_files/';
    $filename   = time() . '_' . basename($_FILES['commented_file']['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['commented_file']['tmp_name'], $targetPath)) {
        $commentedFilePath = $targetPath;
    }
}

// Insert evaluation
$evalQuery = "INSERT INTO evaluations 
    (paper_id, evaluated_by, evaluation_date, status, rating, feedback, commented_file_path)
    VALUES (?, ?, NOW(), ?, ?, ?, ?)";
$evalStmt = $connect_db->prepare($evalQuery);
if ($evalStmt === false) {
    die("Prepare failed for evaluation: " . $connect_db->error);
}
$evalStmt->bind_param("iissss", $paperId, $evaluatedBy, $status, $rating, $feedback, $commentedFilePath);
if (!$evalStmt->execute()) {
    die("Execute failed for evaluation: " . $evalStmt->error);
}
$evalStmt->close();

// Fetch student + supervisor info in one query
$infoQuery = "SELECT 
                 u.username AS student_name,
                 u.email    AS student_email,
                 s.username AS supervisor_name,
                 s.email    AS supervisor_email,
                 a.title    AS assignment_title
              FROM research_papers rp
              JOIN users u ON rp.submitted_by = u.user_id
              JOIN assignments a ON rp.assignment_id = a.assignment_id
              JOIN users s ON a.created_by = s.user_id
              WHERE rp.paper_id = ?";
$infoStmt = $connect_db->prepare($infoQuery);
$infoStmt->bind_param("i", $paperId);
$infoStmt->execute();
$info = $infoStmt->get_result()->fetch_assoc();
$infoStmt->close();


// If accepted and published → insert into published_papers
    if (trim(strtolower($status)) === 'accepted and published') {
        $publishedQuery = "INSERT INTO published_papers 
            (paper_id, assignment_id, title, category, author_id, supervisor_id, publication_date, file_path)
        SELECT rp.paper_id, rp.assignment_id, a.title, a.category, rp.submitted_by, ?, NOW(), rp.file_path
        FROM research_papers rp
        JOIN assignments a ON rp.assignment_id = a.assignment_id
        WHERE rp.paper_id = ?";

        $publishedStmt = $connect_db->prepare($publishedQuery);
        if ($publishedStmt === false) {
            die("Prepare failed for published insert: " . $connect_db->error);
        }
        $publishedStmt->bind_param("ii", $evaluatedBy, $paperId);
        if (!$publishedStmt->execute()) {
            die("Insert into published_papers failed: " . $publishedStmt->error);
        }
        $publishedStmt->close();

        if ($info) {
                $subject = "Your Paper Has Been Published: {$info['assignment_title']}";
                $body = "
                    <div style='font-family: Arial, sans-serif; font-size: 14px; color: #333;'>
                    <p>Dear {$info['student_name']},</p>
                    <p>Congratulations! Your paper titled <strong>{$info['assignment_title']}</strong> has been accepted and published.</p>
                    <p>This publication was approved by Supervisor <strong>{$info['supervisor_name']}</strong>.</p>
                    <p>You can now view it in the Published Papers section of the RSES system.</p>
                    <p>Best regards,<br><strong>RSES System</strong></p>
                    </div>
                ";
                sendEmail($info['student_email'], $subject, $body);
            }
 } else { // send evaluation email
                if ($info) {
                    $subject = "Paper Evaluated: {$info['assignment_title']}";
                    $body = "
                        <div style='font-family: Arial, sans-serif; font-size: 14px; color: #333;'>
                        <p>Dear {$info['student_name']},</p>
                        <p>Your paper titled <strong>{$info['assignment_title']}</strong> has been evaluated.</p>
                        <p>Please log in to the RSES system to view the feedback and rating.</p>
                        <p>Best regards,<br><strong>RSES System</strong></p>
                        </div>
                    ";
                    sendEmail($info['student_email'], $subject, $body);
                }
        }
// Redirect back with success
header("Location: /rses/templates/supervisor-dashboard.php?page=evaluate-paper&paper_id=$paperId&success=1");
exit;
?>