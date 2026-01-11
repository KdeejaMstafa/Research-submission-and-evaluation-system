<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/includes/db_connection.php';

// Redirect if not logged in or not student
if (!isset($_SESSION['user_id']) || ($_SESSION['role_level'] ?? null) !== 'student') {
    header("Location: /rses/templates/login-page.php");
    exit();
}

$studentId = $_SESSION['user_id'];

// Step 1: Find the assignment the student selected
$sqlAssignment = "
SELECT 
    rp.paper_id,
    rp.assignment_id,
    rp.submission_date,
    rp.submission_status,
    rp.submitted_by,
    a.title AS assignment_title,
    a.due_date,
    u.username AS supervisor_name
FROM research_papers rp
JOIN assignments a 
    ON a.assignment_id = rp.assignment_id
JOIN users u 
    ON a.created_by = u.user_id
WHERE rp.submitted_by = ?
ORDER BY a.due_date ASC, rp.submission_date DESC
";

$stmt = $connect_db->prepare($sqlAssignment);
if (!$stmt) {
    die("Prepare failed: (" . $connect_db->errno . ") " . $connect_db->error);
}
$stmt->bind_param("i", $studentId);
$stmt->execute();
$assignmentResult = $stmt->get_result();
$stmt->close();

$assignment = $assignmentResult->fetch_assoc();

// Step 2: Get all versions of submissions for this assignment + feedback
$sqlVersions = "
SELECT rp.paper_id,
       rp.submission_date,
       rp.submission_status,
       e.feedback,
       e.evaluation_date
FROM research_papers rp
LEFT JOIN evaluations e 
       ON rp.paper_id = e.paper_id
WHERE rp.submitted_by = ? AND rp.assignment_id = ?
ORDER BY rp.submission_date DESC
";

$stmt = $connect_db->prepare($sqlVersions);
if ($assignment) {
    $stmt->bind_param("ii", $studentId, $assignment['assignment_id']);
    $stmt->execute();
    $versionsResult = $stmt->get_result();
    $stmt->close();
} else {
    $versionsResult = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Assignment</title>
  <link rel="stylesheet" href="/rses/assets/css/student-styles.css">
</head>
<body>
  <div class="assignment-details-container">
    <?php if ($assignment): ?>
      <h2><?= htmlspecialchars($assignment['assignment_title']) ?></h2>
      <p><strong>Supervisor:</strong> <?= htmlspecialchars($assignment['supervisor_name']) ?></p>
      <p><strong>Due Date:</strong> <?= date("d-m-Y", strtotime($assignment['due_date'])) ?></p>

      <h3>Your Submissions</h3>
      <?php if ($versionsResult && $versionsResult->num_rows > 0): ?>
        <table class="assignment-details-table">
          <thead>
            <tr>
              <th>Version</th>
              <th>Submission Date</th>
              <th>Status</th>
              <th>Feedback</th>
              <th>Evaluation Date</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $versionCount = 1;
              while ($row = $versionsResult->fetch_assoc()): 
            ?>
              <tr>
                <td>Version <?= $versionCount++ ?></td>
                <td><?= date("d-m-Y H:i", strtotime($row['submission_date'])) ?></td>
                <td class="status-badge status-<?= strtolower($row['submission_status']) ?>">
                  <?= htmlspecialchars($row['submission_status']) ?>
                </td>
                <td>
                  <?= $row['feedback'] 
                       ? htmlspecialchars($row['feedback']) 
                       : "<em>No feedback yet</em>" ?>
                </td>
                <td>
                  <?= $row['evaluation_date'] 
                       ? date("d-m-Y H:i", strtotime($row['evaluation_date'])) 
                       : "-" ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No submissions yet for this assignment.</p>
      <?php endif; ?>
    <?php else: ?>
      <p>You have not selected any assignment yet.</p>
    <?php endif; ?>
  </div>
</body>
</html>