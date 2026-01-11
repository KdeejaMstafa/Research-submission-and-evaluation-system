<?php
session_start();
if (!isset($connect_db)) {
  require_once dirname(__DIR__) . '/includes/db_connection.php';
}

require_once dirname(__DIR__) . '/includes/notifications_handler.php';
$adminId = $_SESSION['user_id'] ?? 1;
$systemNotifications = getSystemNotifications($adminId);


// --- Counts for overview ---
$totalUsers = $connect_db->query("SELECT COUNT(*) AS counts FROM users")->fetch_assoc()['counts'];
$totalStudents = $connect_db->query("SELECT COUNT(*) AS counts FROM users WHERE role_level='student'")->fetch_assoc()['counts'];
$totalSupervisors = $connect_db->query("SELECT COUNT(*) AS counts FROM users WHERE role_level='supervisor'")->fetch_assoc()['counts'];

// --- Pending requests ---
$pendingRequests = $connect_db->query("SELECT COUNT(*) AS counts FROM users WHERE status='pending'")->fetch_assoc()['counts'];

// --- Recent registrations ---
$recentRegistrations = $connect_db->query("SELECT username, role_level FROM users ORDER BY user_id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/css/admin-style.css" />
</head>
<body>
  <nav class="admin-navbar">
    <div class="admin-profile-div">
      <img src="/rses/assets/images/admin-profile-img.png" alt="Admin Icon" />
      <div><a href="/rses/templates/admin-interface.php?page=profile">PROFILE</a></div>
    </div>

    <div class="admin-nav-links">
      <ul>
        <li><a href="admin-interface.php">HOME</a></li>
        <li><a href="admin-interface.php?page=monitor-submissions">MONITOR SUBMISSIONS</a></li>
        <li><a href="/rses/templates/admin-interface.php?page=manage-users">MANAGE USERS</a></li>
        <li><a href="admin-interface.php?page=generate-report">GENERATE REPORTS</a></li>
        <li><a href="../handlers/logout.php" onclick="return confirm('Are you sure you want to log out?')">LOGOUT</a></li>
      </ul>
    </div>

    <div class="logo-div">
      <img src="/rses/assets/images/research-system-logo.png" alt="Official Logo" />
    </div>
  </nav>

  <div class="admin-main-body">
    <?php
    if (isset($_GET['page'])) {
      $page = $_GET['page'];

      // Route profile and change-password to templates
      if ($page === 'profile') {
        $file = dirname(__DIR__) . '/templates/profile.php';
      } elseif ($page === 'change-password') {
        $file = dirname(__DIR__) . '/templates/change-password.php';
      } elseif ($page === 'generate-report') {
        $file = dirname(__DIR__) . '/templates/generate-report.php';
      } else {
        // Default routing to admin-pages
        $file = dirname(__DIR__) . '/admin-pages/' . $page . '.php';
      }

      if (file_exists($file)) {
        include $file;
      } else {
        echo "<p style='color:red;'>Page not found: " . htmlspecialchars($file) . "</p>";
      }
    } else {
      echo "<h3>Welcome Admin!</h3>";
      ?>
    <div class="admin-dashboard">
      <div class="admin-notif-div">
        <h3>System Notifications</h3>
        <?php if (!empty($systemNotifications)): ?>
          <ul class="notif-list">
            <?php foreach ($systemNotifications as $notif): ?>
              <li class="<?php echo $notif['is_read'] ? 'read' : 'unread'; ?>">
                <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                <p><?php echo htmlspecialchars($notif['message']); ?></p>
                <small><?php echo date("d M Y H:i", strtotime($notif['created_at'])); ?></small>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>No system issues reported.</p>
        <?php endif; ?>
      </div>
      
        <!-- Statistics box -->
      <div class="admin-stats-div">
        <section>
        <h3>System Overview</h3>
        <p class="stat-box"><strong>Total Users:</strong> <?= $totalUsers ?></p>
        <p class="stat-box"><strong>Students:</strong> <?= $totalStudents ?></p>
        <p class="stat-box"><strong>Supervisors:</strong> <?= $totalSupervisors ?></p>
        <p class="stat-box"><strong>Pending Requests:</strong> <?= $pendingRequests ?></p>
        </section>

        <section>
        <h3>Recent Registrations</h3>
        <ul class="recent-regs-count">
          <?php while($row = $recentRegistrations->fetch_assoc()): ?>
            <li><?= htmlspecialchars($row['username']) ?> (<?= htmlspecialchars($row['role_level']) ?>)</li><hr>
          <?php endwhile; ?>
        </ul>
          </section>
      </div>

    </div>
      <?php
    }
    ?>
  </div>
</body>
</html>