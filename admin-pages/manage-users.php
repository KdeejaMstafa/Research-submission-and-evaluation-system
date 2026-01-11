<?php
// Access control for ADMIN: lists pending registration requests and approved users with edit/delete options

include '../includes/db_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Uncomment this block to enforce admin-only access

if (!isset($_SESSION['username']) || $_SESSION['role_level'] !== 'admin') {
    header("Location: ../templates/login-page.php");
    exit();
}


$editMode = isset($_GET['edit_user_id']) ? intval($_GET['edit_user_id']) : null;
?>

<?php if ($editMode): ?>
<!--  Show edit form instead of tables -->
<?php include '../admin-pages/edit-user.php'; ?>

<?php else: ?>
<!--  Default view: show pending and approved user tables -->

<div class="pending-users-div-el">
  <?php if (isset($_SESSION['admin_message'])): ?>
    <div class="alert-box">
      <?= htmlspecialchars($_SESSION['admin_message']) ?>
    </div>
    <?php unset($_SESSION['admin_message']); ?>
  <?php endif; ?>

  <h2>Pending Registration Requests</h2>
  <?php
  $pendingQuery = "SELECT user_id, username, email, role_level FROM users WHERE status = 'pending'";
  $pendingResult = mysqli_query($connect_db, $pendingQuery);
  ?>

  <?php if ($pendingResult): ?>
    <?php if (mysqli_num_rows($pendingResult) > 0): ?>
      <table class="pending-user-table">
        <thead>
          <tr>
            <th>USER ID</th>
            <th>USERNAME</th>
            <th>EMAIL</th>
            <th>ROLE</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($pendingResult)): ?>
            <tr>
              <td><?= $row['user_id'] ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= ucfirst($row['role_level']) ?></td>
              <td>
                <form method="POST" action="../admin-pages/admin-action.php" style="display:inline;">
                  <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>" />
                  <button name="action" value="approve">Approve</button>
                  <button name="action" value="reject">Reject</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No pending requests.</p>
    <?php endif; ?>
  <?php else: ?>
    <p>Error loading pending users: <?= mysqli_error($connect_db) ?></p>
  <?php endif; ?>
</div>

<div class="approved-users-div-el">
  <h2>All Existing Users</h2>
  <?php
  $approvedQuery = "SELECT user_id, username, email, role_level FROM users WHERE status = 'approved'";
  $approvedResult = mysqli_query($connect_db, $approvedQuery);
  ?>

  <?php if ($approvedResult): ?>
    <?php if (mysqli_num_rows($approvedResult) > 0): ?>
      <table class="approved-user-table">
        <thead>
          <tr>
            <th>USER ID</th>
            <th>USERNAME</th>
            <th>EMAIL</th>
            <th>ROLE</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($user = mysqli_fetch_assoc($approvedResult)): ?>
            <tr>
              <td><?= $user['user_id'] ?></td>
              <td><?= htmlspecialchars($user['username']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= ucfirst($user['role_level']) ?></td>
              <td>
                <!-- Edit Button: stays inside admin-interface -->
                <form method="GET" action="../templates/admin-interface.php" style="display:inline;">
                  <input type="hidden" name="page" value="manage-users">
                  <input type="hidden" name="edit_user_id" value="<?= $user['user_id'] ?>">
                  <button type="submit">Edit</button>
                </form>

                <!-- Delete Button: secure POST -->
                <form method="POST" action="../admin-pages/delete-user.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete <?= htmlspecialchars($user['username']) ?>?')">
                  <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                  <button id="user-del-btn" type="submit" class="delete-button">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No approved users found.</p>
    <?php endif; ?>
  <?php else: ?>
    <p>Error loading approved users: <?= mysqli_error($connect_db) ?></p>
  <?php endif; ?>
</div>

<?php endif; ?>
