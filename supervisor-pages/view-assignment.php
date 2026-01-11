<?php
require_once '../includes/db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: /rses/templates/login-page.php");
  exit();
}

$supervisorId = $_SESSION['user_id'];
$assignments = [];

// Fetch assignments created by this supervisor
$stmt = $connect_db->prepare("SELECT assignment_id, title, category, file_path, due_date, created_at FROM assignments WHERE created_by = ?");
$stmt->bind_param("i", $supervisorId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  $assignments[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Assignments</title>
  <link rel="stylesheet" href="/rses/assets/css/supervisor-styles.css">
</head>
<body>
  <div class="assignment-list-container">
      <button class="back-btn-sup" type="button" onclick="window.location.href='/rses/templates/supervisor-dashboard.php'">
        Back</button>
      <h2>Your Created Assignments</h2>

      <?php if (empty($assignments)): ?>
        <p>No assignments found.</p>
      <?php else: ?>
        <table class="assignment-table">
          <thead>
            <tr>
              <th>No.</th>
              <th>Title</th>
              <th>Category</th>
              <th>Due Date</th>
              <th>Created At</th>
              <th>Download</th>
            </tr>
          </thead>
          <tbody>
            <?php $serial = 1; ?>
            <?php foreach ($assignments as $assignment): ?>
              <?php
                // Format dates as d-m-y
                $dueDate = !empty($assignment['due_date']) ? date("d-m-Y", strtotime($assignment['due_date'])) : '';
                $createdAt = !empty($assignment['created_at']) ? date("d-m-Y", strtotime($assignment['created_at'])) : '';
              ?>
              <tr>
                <td><?php echo $serial++; ?></td>
                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                <td><?php echo htmlspecialchars($assignment['category']); ?></td>
                <td><?php echo htmlspecialchars($dueDate); ?></td>
                <td><?php echo htmlspecialchars($createdAt); ?></td>
                <td style="width: 120px;">
                  <a href="<?php echo $assignment['file_path']; ?>" target="_blank">Download</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </div>
</body>
</html>