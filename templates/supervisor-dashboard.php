<?php
include('../includes/supervisor-navbar.php');
session_start();

require_once dirname(__DIR__) . '/includes/db_connection.php';

$fullName = $_SESSION['username'] ?? 'SUPERVISOR';
$firstName = explode(' ', trim($fullName))[0];
$supervisorUserId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>
  <link rel="stylesheet" href="/rses/assets/css/supervisor-styles.css"/>
</head>
<body>
  <div class="sup-main-page">
    <?php
      if (isset($_GET['page'])) {
        $page = $_GET['page'];

        // Route profile to /templates/
        if ($page === 'profile') {
          $file = dirname(__DIR__) . '/templates/profile.php';
        } elseif($page === 'change-password'){
          $file = dirname(__DIR__) . '/templates/change-password.php';
        } elseif($page === 'generate-report'){
          $file = dirname(__DIR__) . '/templates/generate-report.php';
        } elseif($page === 'help'){
          $file = dirname(__DIR__) . '/templates/help.php';
        }
        else {
          $file = dirname(__DIR__) . '/supervisor-pages/' . $page . '.php';
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
        <section class="sup-notif-sec">
          <h3>NOTIFICATIONS</h3>
          <div class="display-notif-sup">
            <?php
            $notifStmt = $connect_db->prepare("SELECT title, message, created_at
              FROM notifications
              WHERE recipient_id = ? AND type = 'resubmission' AND is_read = 0
              ORDER BY created_at DESC
            ");
            $notifStmt->bind_param("i", $supervisorUserId);
            $notifStmt->execute();
            $notifResult = $notifStmt->get_result();
            $notifStmt->close();
            ?>

            <?php if ($notifResult->num_rows > 0): ?>
              <ul>
                <?php while ($notif = $notifResult->fetch_assoc()): ?>
                  <li>
                    <strong><?php echo htmlspecialchars($notif['title']); ?></strong><br>
                    <?php echo htmlspecialchars($notif['message']); ?><br>
                    <small><?php echo date('F j, Y g:i A', strtotime($notif['created_at'])); ?></small>
                  </li>
                <?php endwhile; ?>
              </ul>
            <?php else: ?>
              <p>No new notifications.</p>
            <?php endif; ?>
          </div>
        </section>

        <section class="sup-options-list">
          <a href="supervisor-dashboard.php?page=create-assignment">CREATE ASSIGNMENTS</a><br>
          <a href="supervisor-dashboard.php?page=view-assignment">VIEW ASSIGNMENTS</a><br>
          <a href="supervisor-dashboard.php?page=generate-report">GENERATE REPORTS</a>
        </section>
      </div>

      <section class="sup-evaluation-table-sec">
        <h3>STUDENT SUBMISSIONS</h3>
        <table cellpadding="10" cellspacing="0">
          <thead>
            <tr>
              <th>NO.</th>
              <th>TITLE</th>
              <th>STUDENT NAME</th>
              <th>SUBMISSION DATE</th>
              <th>STATUS</th>
              <th>EVALUATION</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $serial = 1;

            $query = "SELECT rp.paper_id, rp.assignment_id, rp.submitted_by, rp.submission_date,
                     rp.submission_status, rp.file_path, rp.version,
                     a.title AS assignment_title, u.username AS student_name,
                     CASE WHEN e.evaluation_id IS NOT NULL THEN 'Evaluated' ELSE 'Pending' END AS evaluation_status
              FROM research_papers rp
              JOIN (
                SELECT assignment_id, submitted_by, MAX(version) AS latest_version
                FROM research_papers
                GROUP BY assignment_id, submitted_by
              ) latest
              ON rp.assignment_id = latest.assignment_id
              AND rp.submitted_by = latest.submitted_by
              AND rp.version = latest.latest_version
              JOIN assignments a ON rp.assignment_id = a.assignment_id
              JOIN users u ON rp.submitted_by = u.user_id
              LEFT JOIN evaluations e ON rp.paper_id = e.paper_id
              WHERE a.created_by = ?
              ORDER BY rp.submission_date DESC
            ";

            $stmt = $connect_db->prepare($query);
            $stmt->bind_param("i", $supervisorUserId);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
            ?>
              <tr>
                <td><?php echo $serial++; ?></td>
                <td><?php echo htmlspecialchars($row['assignment_title']); ?></td>
                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['submission_date']))); ?></td>
                <td><?php echo htmlspecialchars($row['evaluation_status']); ?></td>
                <td class="btns-view-and-eval">
                  <a href="<?php echo htmlspecialchars($row['file_path']); ?>" download>
                    <button class="action-btn">View</button>
                  </a>
                  <a href="supervisor-dashboard.php?page=evaluate-paper&paper_id=<?php echo $row['paper_id']; ?>">
                    <button class="action-btn">Evaluate</button>
                  </a>
                </td>
              </tr>
            <?php
            }
            $stmt->close();
            ?>
          </tbody>
        </table>
      </section>
    <?php } ?>
  </div>
</body>
</html>