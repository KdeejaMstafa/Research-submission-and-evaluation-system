<?php
if (!isset($_SESSION)) session_start();
if (!isset($connect_db)) {
  require_once dirname(__DIR__) . '/includes/db_connection.php';
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  echo "<p style='color:red;'>Session expired. Please log in again.</p>";
  return;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current = $_POST['current_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if (!$current || !$new || !$confirm) {
    $error = "All fields are required.";
  } elseif ($new !== $confirm) {
    $error = "New passwords do not match.";
  } else {
    $stmt = $connect_db->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($current, $user['password'])) {
      $error = "Current password is incorrect.";
    } else {
      $hashed = password_hash($new, PASSWORD_DEFAULT);
      $update = $connect_db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
      $update->bind_param("si", $hashed, $userId);
      if ($update->execute()) {
        $success = "Password updated successfully.";
      } else {
        $error = "Failed to update password.";
      }
      $update->close();
    }
  }
}
?>

<style>
.change-password-form {
  max-width: 500px;
  margin: 30px auto;
  padding: 20px; /* ← this gives equal space on left and right */
  background-color: #faf7e9;
  border-radius: 3px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  font-family: "open sans", 'Courier New', Courier, monospace;
}

.change-password-form h3 {
  margin-bottom: 20px;
  color: #003f5c;
}
.change-password-form label {
  display: block;
  margin-bottom: 6px;
  font-weight: bold;
}
.change-password-form input[type="password"] {
  width: 100%;
  padding: 10px;
  margin-bottom: 14px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.change-password-form button {
  background-color: #003f5c;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 3px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.change-password-form button:hover {
  background-color: #094c6bff;
}
.success-msg {
  color: green;
  margin-bottom: 10px;
}
.error-msg {
  color: red;
  margin-bottom: 10px;
}
</style>

<div class="change-password-form">
  <h3>Change Password</h3>

  <?php if ($success): ?>
    <p class="success-msg"><?= htmlspecialchars($success) ?></p>
  <?php elseif ($error): ?>
    <p class="error-msg"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST">
    <label for="current_password">Current Password</label>
    <input type="password" name="current_password" id="current_password" required>

    <label for="new_password">New Password</label>
    <input type="password" name="new_password" id="new_password" required>

    <label for="confirm_password">Confirm New Password</label>
    <input type="password" name="confirm_password" id="confirm_password" required>

    <button type="submit">Update Password</button>

    <?php
    $role_level = $_SESSION['role_level'] ?? null;

    if ($role_level === 'admin') {
      $dashboard = 'admin-interface.php';
    } elseif ($role_level === 'supervisor') {
      $dashboard = 'supervisor-dashboard.php';
    } elseif ($role_level === 'student') {
      $dashboard = 'student-dashboard.php';
    } else {
      echo "<p style='color:red;'>User role not defined. Please log in again.</p>";
      return;
    }
    ?>

    <button type="button" onclick="window.location.href='<?= $dashboard ?>?page=profile'">Cancel</button>
  </form>
</div>