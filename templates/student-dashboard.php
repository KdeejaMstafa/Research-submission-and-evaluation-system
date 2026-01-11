<?php
session_start();
include('../includes/student-navbar.php');
require_once dirname(__DIR__) . '/includes/db_connection.php';

$fullName = $_SESSION['username'] ?? 'STUDENT';
$firstName = explode(' ', trim($fullName))[0];
$studentId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <link rel="stylesheet" href="/rses/assets/css/student-styles.css" />
</head>
<body>
  <div class="std-main-page">
    <?php
    if (isset($_GET['page'])) {
      $page = $_GET['page'];

      // Route profile to /templates/
      if ($page === 'profile') {
        $file = dirname(__DIR__) . '/templates/profile.php';
      } elseif($page === 'change-password'){
        $file = dirname(__DIR__) . '/templates/change-password.php';
      } elseif($page === 'help'){
        $file = dirname(__DIR__) . '/templates/help.php';
      }
      else {
        $file = dirname(__DIR__) . '/student-pages/' . $page . '.php';
      }

      if (file_exists($file)) {
        include $file;
      } else {
        echo "<p>Page not found: " . htmlspecialchars($file) . "</p>";
      }
    } else {
    ?>
    
    <h3>Welcome <?php echo htmlspecialchars($firstName); ?>!</h3>

    <div class="top-elements">
        <section class="std-notif-sec">
        <h3>NOTIFICATIONS</h3>
        <div class="display-notif-std">
            <?php
            $notifStmt = $connect_db->prepare("SELECT title, message, created_at
            FROM notifications
            WHERE recipient_id = ? AND is_read = 0
            ORDER BY created_at DESC
            LIMIT 5
            ");
            $notifStmt->bind_param("i", $studentId);
            $notifStmt->execute();
            $notifResult = $notifStmt->get_result();
            $notifStmt->close();
            ?>

            <?php if ($notifResult->num_rows > 0): ?>
            <ul>
                <?php while ($notif = $notifResult->fetch_assoc()): ?>
                <li>
                    <strong><?= htmlspecialchars($notif['title']) ?></strong><br>
                    <?= htmlspecialchars($notif['message']) ?><br>
                    <small><?= date('F j, Y g:i A', strtotime($notif['created_at'])) ?></small>
                </li>
                <?php endwhile; ?>
            </ul>
            <?php else: ?>
            <p>No new notifications.</p>
            <?php endif; ?>
        </div>
        </section>

      <section class="std-options-list">
        <a href="student-dashboard.php?page=view-available-assignments">VIEW AVAILABLE ASSIGNMENTS</a><br>
        <a href="student-dashboard.php?page=view-evaluation">VIEW EVALUATION STATUS</a>
      </section>
    </div>

    <section class="student-submission-table-sec">
      <h3>ASSIGNMENTS</h3>
      <table cellpadding="10" cellspacing="0">
        <thead>
          <tr>
            <th>NO.</th>
            <th>TITLE</th>
            <th>START DATE</th>
            <th>DUE DATE</th>
            <th>SUBMIT</th>
            <th>STATUS</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = "SELECT a.assignment_id, a.title, a.created_at AS start_date, a.due_date AS end_date
                    FROM selected_assignments sa
                    JOIN assignments a ON sa.assignment_id = a.assignment_id
                    WHERE sa.student_id = ?";
          $stmt = $connect_db->prepare($query);
          $stmt->bind_param("i", $studentId);
          $stmt->execute();
          $result = $stmt->get_result();

          $serial = 1;
          while ($row = $result->fetch_assoc()) {
            $assignmentId = $row['assignment_id'];

            $statusQuery = "SELECT submission_status FROM research_papers WHERE assignment_id = ? AND submitted_by = ? LIMIT 1";
            $statusStmt = $connect_db->prepare($statusQuery);
            $statusStmt->bind_param("ii", $assignmentId, $studentId);
            $statusStmt->execute();
            $statusResult = $statusStmt->get_result();
            $statusRow = $statusResult->fetch_assoc();
            $status = $statusRow['submission_status'] ?? 'Pending';
            $statusStmt->close();
          ?>
            <tr>
              <td><?php echo $serial++; ?></td>
              <td><?php echo htmlspecialchars($row['title']); ?></td>
              <td><?php echo htmlspecialchars($row['start_date']); ?></td>
              <td><?php echo htmlspecialchars($row['end_date']); ?></td>
              <td>
                <?php if (strtolower($status) === 'submitted'): ?>
                  <a href="student-dashboard.php?page=resubmit-assignment&assignment_id=<?php echo $assignmentId; ?>" class="submit-btn">Resubmit</a>
                <?php else: ?>
                  <a href="student-dashboard.php?page=submit-assignment&assignment_id=<?php echo $assignmentId; ?>" class="submit-btn">Submit</a>
                <?php endif; ?>
              </td>
              <td class="<?php echo (strtolower($status) === 'submitted') ? 'status-submitted' : 'status-pending'; ?>">
                <?php echo htmlspecialchars(ucfirst($status)); ?>
              </td>
            </tr>
          <?php }
          $stmt->close();
          ?>
        </tbody>
      </table>
    </section>
    <?php } ?>
  </div>
</body>
</html>