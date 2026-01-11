<!-- styling of the table is pending -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_connection.php';

$studentId = $_SESSION['user_id'];

$query = "SELECT rp.paper_id,
         rp.assignment_id,
         rp.submission_date,
         rp.file_path,
         a.title AS assignment_title,
         e.status AS evaluation_status,
         e.rating,
         e.feedback,
         e.commented_file_path,
         e.evaluation_date
  FROM research_papers rp
  JOIN assignments a ON rp.assignment_id = a.assignment_id
  JOIN evaluations e ON rp.paper_id = e.paper_id
  WHERE rp.submitted_by = ?
    AND rp.submission_date = (
      SELECT MAX(rp2.submission_date)
      FROM research_papers rp2
      JOIN evaluations e2 ON rp2.paper_id = e2.paper_id
      WHERE rp2.assignment_id = rp.assignment_id
        AND rp2.submitted_by = rp.submitted_by
    )
  ORDER BY e.evaluation_date DESC
";

$stmt = $connect_db->prepare($query);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/rses/assets/css/student-styles.css"/>
</head>
<body>
<div class="view-eval-page-div">
  <h3>Your Evaluated Assignments</h3>

  <?php if (!empty($rows)): ?>
    <div class="eval-date">
      <p>Dated: <?php echo date('d-m-Y', strtotime($rows[0]['evaluation_date'])); ?></p>
    </div>
  <?php endif; ?>

  <table class="evaluation-table">
    <?php foreach ($rows as $row): ?>
      <tr>
        <th>ASSIGNMENT TITLE</th>
        <td><?php echo htmlspecialchars($row['assignment_title']); ?></td>
      </tr>
      <tr>
        <th>SUBMITTED ON</th>
        <td><?php echo date('d-m-Y', strtotime($row['submission_date'])); ?></td>
      </tr>
      <tr>
        <th>EVALUATION STATUS</th>
        <td><span class="status-style"><?php echo ucfirst($row['evaluation_status']); ?></span></td>
      </tr>
      <tr>
        <th>RATING</th>
        <td><?php echo $row['rating'] !== null ? $row['rating'] . ' / 5' : '-'; ?></td>
      </tr>
      <tr>
        <th>FEEDBACK</th>
        <td><?php echo $row['feedback'] ? nl2br(htmlspecialchars($row['feedback'])) : '-'; ?></td>
      </tr>
      <tr>
        <th>COMMENTED FILE</th>
        <td>
          <?php if ($row['commented_file_path']): ?>
            <a href="<?php echo htmlspecialchars($row['commented_file_path']); ?>" download>Download</a>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
</body>
</html>