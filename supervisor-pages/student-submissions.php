<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_connection.php';

// Redirect if not logged in or not supervisor
if (!isset($_SESSION['user_id']) || $_SESSION['role_level'] !== 'supervisor') {
    header("Location: /rses/templates/login-page.php");
    exit();
}

$supervisorId = $_SESSION['user_id'];

// Query: get submissions from students assigned to this supervisor
$sql = "SELECT 
    rp.paper_id,
    rp.assignment_id,
    rp.submission_date,
    rp.submission_status,
    rp.submitted_by,
    a.title AS assignment_title,
    a.due_date
FROM research_papers rp
JOIN assignments a 
    ON a.assignment_id = rp.assignment_id
WHERE a.created_by = ?
ORDER BY a.due_date ASC, rp.submission_date DESC;
";

$stmt = $connect_db->prepare($sql);
$stmt->bind_param("i", $supervisorId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Submissions</title>
  <link rel="stylesheet" href="/rses/assets/css/supervisor-styles.css">
</head>
<body>
  <div class="assignment-list-container">
    <button class="back-btn-sup" type="button" onclick="window.location.href='/rses/templates/supervisor-dashboard.php'">
        Back</button>
    <h2>Student Submissions</h2>

    <?php if ($result && $result->num_rows > 0): ?>
      <table class="assignment-table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Assignment</th>
            <th>Due Date</th>
            <th>Submission Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php
              // Format dates as d-m-Y
              $dueDate = !empty($row['due_date']) ? date("d-m-Y", strtotime($row['due_date'])) : '';
              $submissionDate = !empty($row['submission_date']) ? date("d-m-Y", strtotime($row['submission_date'])) : '';
            ?>
            <tr>
              <td><?= htmlspecialchars($row['submitted_by']) ?></td>
              <td><?= htmlspecialchars($row['assignment_title']) ?></td>
              <td><?= htmlspecialchars($dueDate) ?></td>
              <td><?= htmlspecialchars($submissionDate) ?></td>
              <td class="status-badge status-<?= strtolower($row['submission_status']) ?>">
                <?= htmlspecialchars($row['submission_status']) ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No submissions found for your students.</p>
    <?php endif; ?>
  </div>
</body>
</html>