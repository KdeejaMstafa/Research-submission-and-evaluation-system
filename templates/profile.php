<!-- this page contains php code for the profile, the html for profile, and the styles for profile.-->
<?php
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header("Location: /rses/template/login-page.php");
    exit();
}
if (!isset($connect_db)) {
  echo "<p style='color:red;'>Database connection not available.</p>";
  return;
}
$userStmt = $connect_db->prepare("SELECT username, email, role_level FROM users WHERE user_id = ?");
if (!$userStmt) {
  echo "<p style='color:red;'>Prepare failed: " . $connect_db->error . "</p>";
  return;
}
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

$role = $user['role_level'];
$extra = [];

if ($role === 'supervisor') {
    $stmt = $connect_db->prepare("SELECT supervisor_id, expertise, affiliation, no_of_publications FROM supervisors WHERE user_id = ?");
} elseif ($role === 'student') {
    $stmt = $connect_db->prepare("SELECT student_id, year_of_study, institute_name, study_program FROM students WHERE user_id = ?");
} elseif($role === 'admin'){
    $stmt = $connect_db->prepare("SELECT admin_id, admin_name, phone_number FROM admins WHERE user_id = ?");
} else {
    $stmt = null;
}

if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $extra = $result->fetch_assoc();
    $stmt->close();
}
?>

<style>
.profile-container {
  background-color: #faf7e9;
  border-radius: 4px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  padding: 2em;
  max-width: 100%;
  margin: 20px 0;
  font-family: "open sans", 'Courier New', Courier, monospace;
}

.profile-header {
  display: flex;
  align-items: flex-start;
  gap: 30px;
}

.profile-image img {
  width: 140px;
  height: 140px;
  object-fit: cover;
  border-radius: 50%;
  border: 4px solid #003f5c;
  background-color: #196385;
  padding: 0.5em;
  margin-top: 10%;
}

.profile-info {
  flex: 1;
}

.profile-info .info-row {
  display: flex;
  gap: 20px;
  margin-bottom: 10px;
}

.profile-info .info-row h4 {
  width: 140px;
  margin: 0;
  font-size: 14px;
  color: #003f5c;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.profile-info .info-row p {
  margin: 0;
  font-size: 16px;
  color: #222;
}
#username-h3{
 color: #003f5c;
 margin: 15px 0 2px 0;
}
.change-password-wrapper {
  text-align: left;
  font-family: "open sans", 'Courier New', Courier, monospace;
}

.change-password-btn {
  background-color: #003f5c;
  color: white;
  padding: 10px 20px;
  border-radius: 4px;
  text-decoration: none;
  display: inline-block;
  transition: background-color 0.4s ease;
}

.change-password-btn:hover {
  background-color: #940707ff;
}
#admin-prof-img{
  background-color: transparent;
  border: none;
}
/* ---------- RESPONSIVE DESIGN FOR PROFILE PAGE ---------- */

/* Tablet screens */
@media (max-width: 1024px) {
  .profile-container {
    padding: 1.5em;
    margin: 15px 0;
  }

  .profile-header {
    gap: 20px;
    align-items: center;
  }

  .profile-image img {
    width: 120px;
    height: 120px;
    margin-top: 0; /* remove large top margin */
  }

  .profile-info .info-row {
    gap: 15px;
  }

  .profile-info .info-row h4 {
    width: 120px;
    font-size: 13px;
  }

  .profile-info .info-row p {
    font-size: 15px;
  }
}

/* Mobile screens */
@media (max-width: 600px) {
  .profile-container {
    padding: 1em;
    margin: 10px 0;
  }

  /* Stack image above info */
  .profile-header {
    flex-direction: column;
    align-items: center;
    gap: 1em;
  }

  .profile-image img {
    width: 100px;
    height: 100px;
    margin-top: 0;
  }

  .profile-info {
    width: 100%;
  }

  /* Info rows stack vertically */
  .profile-info .info-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25em;
    margin-bottom: 1em;
  }

  .profile-info .info-row h4 {
    width: auto;
    font-size: 13px;
    text-align: left;
  }

  .profile-info .info-row p {
    font-size: 14px;
    text-align: left;
    word-break: break-word;
  }

  #username-h3 {
    text-align: center;
    font-size: 16px;
  }

  .change-password-wrapper {
    text-align: center;
    margin-top: 1em;
  }

  .change-password-btn {
    display: inline-block;
    padding: 8px 16px;
    font-size: 14px;
  }
}
</style>

<div class="profile-container">
  <div class="profile-header">
    <div class="profile-image">
      <?php if ($role === 'student'): ?>
        <img src="/rses/assets/images/student-icon.png" alt="Student Profile Picture">
      <?php elseif ($role === 'supervisor'): ?>
        <img src="/rses/assets/images/supervisor-profile-img.png" alt="Supervisor Profile Picture">
      <?php else: ?>
        <img id="admin-prof-img"src="/rses/assets/images/admin-profile-img.png" alt="Admin Profile Picture">
      <?php endif; ?>
    </div>

    <div class="profile-info">
      <div class="info-row">
        <h3 id="username-h3">
          <?php if ($role === 'admin'): ?>
            <?= htmlspecialchars($extra['admin_name'] ?? 'System Administrator') ?>
          <?php else: ?>
            <?= htmlspecialchars($user['username']) ?>
          <?php endif; ?>
        </h3>

      </div>
      <div class="info-row">
        <h4>Email</h4>
        <p><?= htmlspecialchars($user['email']) ?></p>
      </div>
      <?php if ($role === 'student'): ?>
        <div class="info-row">
          <h4>Student ID</h4>
          <p><?= htmlspecialchars($extra['student_id'] ?? 'N/A') ?></p>
        </div>
      <?php elseif ($role === 'supervisor'): ?>
        <div class="info-row">
          <h4>Supervisor ID</h4>
          <p><?= htmlspecialchars($extra['supervisor_id'] ?? 'N/A') ?></p>
        </div>
      <?php elseif ($role === 'admin'): ?>
        <div class="info-row">
          <h4>Admin's User ID</h4>
          <p><?= htmlspecialchars($userId) ?></p>
        </div>
      <?php endif; ?>

      <div class="info-row">
        <h4>Role</h4>
        <p><?= ucfirst($user['role_level']) ?></p>
      </div>

      <?php if ($role === 'supervisor'): ?>
        <div class="info-row">
          <h4>Expertise</h4>
          <p><?= htmlspecialchars($extra['expertise']) ?></p>
        </div>
        <div class="info-row">
          <h4>Affiliation</h4>
          <p><?= htmlspecialchars($extra['affiliation']) ?></p>
        </div>
        <div class="info-row">
          <h4>Number Of Publications</h4>
          <p><?= htmlspecialchars($extra['no_of_publications']) ?></p>
        </div>
      <?php elseif ($role === 'student'): ?>
        <div class="info-row">
          <h4>Study Year</h4>
          <p><?= htmlspecialchars($extra['year_of_study']) ?></p>
        </div>
        <div class="info-row">
          <h4>Institute</h4>
          <p><?= htmlspecialchars($extra['institute_name']) ?></p>
        </div>
        <div class="info-row">
          <h4>Study Program</h4>
          <p><?= htmlspecialchars($extra['study_program']) ?></p>
        </div>


      <?php elseif ($role === 'admin'): ?>
        <div class="info-row">
          <h4>Admin ID</h4>
          <p><?= htmlspecialchars($extra['admin_id']) ?></p>
        </div>
        <div class="info-row">
          <h4>Phone Number</h4>
          <p><?= htmlspecialchars($extra['phone_number']) ?></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$role_level = $_SESSION['role_level'] ?? null;

if ($role_level === 'admin') {
  $dashboard = 'admin-interface.php';
} elseif ($role_level === 'supervisor') {
  $dashboard = 'supervisor-dashboard.php';
} elseif ($role_level === 'student') {
  $dashboard = 'student-dashboard.php';
} else {
  $dashboard = 'login.php'; // fallback
}
?>

<div class="change-password-wrapper">
  <a href="<?= $dashboard ?>?page=change-password" class="change-password-btn">Change Password</a>
</div>