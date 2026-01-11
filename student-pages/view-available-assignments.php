<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: /rses/templates/login-page.php");
  exit();
}

$studentId = $_SESSION['user_id'];
$assignments = [];

// Fetch all assignments
$query = "SELECT a.assignment_id, a.title, a.category, a.due_date, a.file_path, a.created_at, s.supervisor_name
          FROM assignments a
          JOIN supervisors s ON a.created_by = s.user_id";
$result = $connect_db->query($query);

while ($row = $result->fetch_assoc()) {
  $assignments[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Assignments</title>
  <link rel="stylesheet" href="/rses/assets/css/student-styles.css">
</head>
<body>
  <section><button class="back-btn-std" type="button" onclick="window.location.href='/rses/templates/student-dashboard.php'">
        Back</button></section>
  <div class="assignment-list-container">
    <h3>Available Research Assignments</h3>
    <div class="assignment-msges">
      <?php
        if (isset($_GET['msg'])) {
          if ($_GET['msg'] === 'selection_success') {
            echo "<p class='success-msg'>Assignment selected successfully.</p>";
          } elseif ($_GET['msg'] === 'already_selected') {
            echo "<p class='warning-msg'>You’ve already selected an assignment.</p>";
          }
        }
      ?>

    </div>
    <?php if (empty($assignments)): ?>
      <p>No assignments available at the moment.</p>
    <?php else: ?>
    <section class="available-assignments-table-sec">
      <table>
        <thead>
          <tr>
            <th>NO.</th>
            <th>TITLE</th>
            <th>CATEGORY</th>
            <th>DUE DATE</th>
            <th>SUPERVISOR</th>
            <th>DOWNLOAD</th>
            <th>SELECT</th>
          </tr>
        </thead>
        <tbody>
          <?php $serial = 1; ?>
          <?php foreach ($assignments as $assignment): ?>
            <tr>
              <td><?php echo $serial++; ?></td>
              <td><?php echo htmlspecialchars($assignment['title']); ?></td>
              <td><?php echo htmlspecialchars($assignment['category']); ?></td>
              <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
              <td><?php echo htmlspecialchars($assignment['supervisor_name']); ?></td>
              <td><a class="assignment-download-link" href="<?php echo $assignment['file_path']; ?>" target="_blank">Download</a></td>
              <td>
                <form method="POST" action="/rses/student-pages/select-assignment.php" onsubmit="return confirm('Are you sure you want to select this assignment?');">
                  <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                  <button class="select-assignment-btn" type="submit">Select</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
    <?php endif; ?>
  </div>
</body>
</html>