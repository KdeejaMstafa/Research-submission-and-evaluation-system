<?php
if (!isset($connect_db)) {
  require_once dirname(__DIR__) . '/includes/db_connection.php';
}

$sql = "SELECT rp.assignment_id, rp.paper_id, rp.submission_date, rp.submitted_by, rp.submission_status,
  a.title AS assignment_title, a.due_date, a.created_at AS assignment_created_at, a.created_by AS assignment_created_by,
  e.status AS evaluation_status
FROM research_papers rp
JOIN assignments a 
  ON a.assignment_id = rp.assignment_id
LEFT JOIN (
  SELECT ev.paper_id, ev.status
  FROM evaluations ev
  INNER JOIN (
    SELECT paper_id, MAX(evaluation_id) AS latest_eval_id
    FROM evaluations
    GROUP BY paper_id
  ) latest ON latest.latest_eval_id = ev.evaluation_id
) e ON e.paper_id = rp.paper_id
ORDER BY a.due_date ASC, rp.submission_date DESC
";

$result = $connect_db->query($sql);
?>

<div class="monitor-subs-admin">
<h2>Monitor Submissions</h2>
<?php
// --- Summary counts from research_papers ---
$summary_sql = "SELECT 
  COUNT(*) AS total,
  SUM(CASE WHEN submission_status = 'submitted' THEN 1 ELSE 0 END) AS submitted_count,
  SUM(CASE WHEN submission_status = 'pending' THEN 1 ELSE 0 END) AS pending_count
FROM research_papers
";
$summary_result = $connect_db->query($summary_sql);
$summary = $summary_result->fetch_assoc();

// --- Evaluation counts from evaluations ---
$eval_sql = "SELECT 
  SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted_count,
  SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS eval_pending_count,
  SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
  SUM(CASE WHEN status = 'needs improvement' THEN 1 ELSE 0 END) AS improvement_count,
  SUM(CASE WHEN status = 'accepted and published' THEN 1 ELSE 0 END) AS published_count
FROM evaluations
";
$eval_result = $connect_db->query($eval_sql);
if (!$eval_result) {
    die("Evaluation query failed: " . $connect_db->error);
}
$eval = $eval_result->fetch_assoc();

?>

<div class="summary-bar">
  <div class="summary-item total">Total: <?= $summary['total'] ?></div>
  <div class="summary-item submitted">Submitted: <?= $summary['submitted_count'] ?></div>
  <div class="summary-item pending">Submission Pending: <?= $summary['pending_count'] ?></div>
  <div class="summary-item accepted">Accepted: <?= $eval['accepted_count'] ?></div>
  <div class="summary-item eval-pending">Evaluation Pending: <?= $eval['eval_pending_count'] ?></div>
  <div class="summary-item improvement">Needs Improvement: <?= $eval['improvement_count'] ?></div>
  <div class="summary-item rejected">Rejected: <?= $eval['rejected_count'] ?></div>
  <div class="summary-item published">Published: <?= $eval['published_count'] ?></div>
</div>

<h3>Submission Details</h3>
<?php if ($result && $result->num_rows > 0): ?>
  <table class="submission-table">
    <thead>
      <tr>
        <th>Assignment</th>
        <th>Due date</th>
        <th>Submitted by</th>
        <th>Submission date</th>
        <th>Submission status</th>
        <th>Evaluation status</th>
        <th>Created by</th>
        <th>Created at</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            // Format all date fields
            $due_date = !empty($row['due_date']) ? date("d-m-Y", strtotime($row['due_date'])) : '';
            $submission_date = !empty($row['submission_date']) ? date("d-m-Y", strtotime($row['submission_date'])) : '';
            $assignment_created_at = !empty($row['assignment_created_at']) ? date("d-m-Y", strtotime($row['assignment_created_at'])) : '';
          ?>

        <tr>
          <td>
            <?= htmlspecialchars($row['assignment_id']) ?>
            — <?= htmlspecialchars($row['assignment_title']) ?>
          </td>
          <td><?= htmlspecialchars($due_date) ?></td>
          <td><?= htmlspecialchars($row['submitted_by']) ?></td>
          <td><?= htmlspecialchars($submission_date) ?></td>
          <td class="status-badge status-<?= strtolower($row['submission_status']) ?>">
            <?= htmlspecialchars($row['submission_status']) ?>
          </td>
          <td class="status-badge eval-<?= strtolower($row['evaluation_status'] ?? 'n/a') ?>">
            <?= htmlspecialchars($row['evaluation_status'] ?? 'N/A') ?>
          </td>
          <td><?= htmlspecialchars($row['assignment_created_by']) ?></td>
          <td><?= htmlspecialchars($assignment_created_at) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
<?php else: ?>
  <p>No submissions found.</p>
<?php endif; ?>
</div>