<?php
require_once '../includes/db_connection.php';

$userId = intval($_GET['edit_user_id'] ?? 0);
if ($userId <= 0) {
  echo "<p>Invalid user ID.</p>";
  return;
}

// fetch basic user info
$sqlstmt = $connect_db->prepare("SELECT username, email, role_level FROM users WHERE user_id = ?");
$sqlstmt->bind_param("i", $userId);
$sqlstmt->execute();
$sqlstmt->bind_result($username, $email, $role);
$sqlstmt->fetch();
$sqlstmt->close();

//fetch role specific info
$extra = [];
if ($role === 'student') {
  $sqlstmt = $connect_db->prepare("SELECT institute_name, study_program FROM students WHERE user_id = ?");
  $sqlstmt->bind_param("i", $userId);
  $sqlstmt->execute();
  $sqlstmt->bind_result($institute, $program);
  $sqlstmt->fetch();
  $sqlstmt->close();
  $extra = ['institute_name' => $institute, 'study_program' => $program];
} elseif ($role === 'supervisor') {
  $sqlstmt = $connect_db->prepare("SELECT affiliation, no_of_publications, expertise FROM supervisors WHERE user_id = ?");
  $sqlstmt->bind_param("i", $userId);
  $sqlstmt->execute();
  $sqlstmt->bind_result($affiliation, $publications, $expertise);
  $sqlstmt->fetch();
  $sqlstmt->close();
  $extra = ['affiliation' => $affiliation, 'no_of_publications' => $publications, 'expertise' => $expertise];
}
$expertiseOptions = [
  'AI','Robotics','Data Science','Cybersecurity','Software Engineering',
  'Biotech','Quantum Computing', 'Financial Technology', 'IoT'
];

$selectedExpertise = ($role === 'supervisor' && isset($extra['expertise']))
    ? array_map('trim', explode(',', $extra['expertise']))
    : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css"/>
</head>
<body>
    <div class="edit-user-main">
    <h2>Edit User: <?= htmlspecialchars($username) ?></h2>
    <form method="POST" action="../admin-pages/update-user.php" class="edit-user-form">
        <input type="hidden" name="user_id" value="<?= $userId ?>" />
        <div>
         <label>Username: <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required></label>
        </div>

        <div>
          <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></label>
        </div>

        <div>
          <label>Role:
          <select name="role_level" required>
              <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
              <option value="supervisor" <?= $role === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
              <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
          </select>
          </label>
        </div>

        <?php if ($role === 'student'): ?>
        <div>
          <label>Institute Name: <input type="text" name="institute_name" value="<?= htmlspecialchars($extra['institute_name']) ?>" required></label>
        </div>
        <div>
          <label>Study Program: <input type="text" name="study_program" value="<?= htmlspecialchars($extra['study_program']) ?>" required></label>
        </div> 
        
        <?php elseif ($role === 'supervisor'): ?>
        <div>
          <label>Affiliation: <input type="text" name="affiliation" value="<?= htmlspecialchars($extra['affiliation']) ?>" required></label>
        </div>
        <div>
          <label>Publications: <input type="text" name="no_of_publications" value="<?= htmlspecialchars($extra['no_of_publications']) ?>" required></label>
        </div>
        <div>
          <label>Expertise: 
           <select name="expertise[]" multiple required>
            <?php foreach ($expertiseOptions as $option): ?>
              <option value="<?= htmlspecialchars($option) ?>"
                <?= in_array($option, $selectedExpertise) ? 'selected' : '' ?>>
                <?= htmlspecialchars($option) ?>
              </option>
            <?php endforeach; ?>
         </select>
         </label>
        </div>
        <?php endif; ?>

        <div class="form-btns">
          <button type="submit">Confirm Changes</button>
          <button type="button" onclick="window.location.href='admin-interface.php?page=manage-users'">Cancel</button>
        </div>
        
    </form>
    </div>
</body>
</html>