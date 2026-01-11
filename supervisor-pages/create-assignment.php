<?php
require_once '../includes/db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: /rses/templates/login-page.php");
  exit();
}

$supervisorId = $_SESSION['user_id'];
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $category = trim($_POST['category']);
  $dueDate = $_POST['due_date'];

  // File upload
  $uploadDir = '../uploads/assignments/';
  $filePath = '';
  $uploadOk = true;

  if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === 0) {
    $fileName = basename($_FILES['assignment_file']['name']);
    $targetPath = $uploadDir . time() . '_' . $fileName;
    $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

    if (!in_array($fileType, ['pdf', 'doc', 'docx'])) {
      $uploadOk = false;
      $error = 'Only PDF or DOC/DOCX files are allowed.';
    }

    if ($uploadOk && move_uploaded_file($_FILES['assignment_file']['tmp_name'], $targetPath)) {
      $filePath = $targetPath;
    } else if ($uploadOk) {
      $error = 'File upload failed.';
    }
  } else {
    $uploadOk = false;
    $error = 'No file uploaded.';
  }

  // Insert into database
  if ($uploadOk) {
    $stmt = $connect_db->prepare("INSERT INTO assignments (title, category, due_date, created_by, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssis', $title, $category, $dueDate, $supervisorId, $filePath);
    $success = $stmt->execute();

    if ($success) {
      $successMessage = "Assignment created successfully!";
    } else {
    $error = "Database error: " . $stmt->error;
    }

    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Assignment</title>
  <link rel="stylesheet" href="/rses/assets/css/supervisor-styles.css">
</head>
<body>
  <div class="assignment-form-container">
    <?php if (!empty($successMessage)): ?>
      <p style="color: aliceblue;"><?php echo $successMessage; ?></p>
    <?php elseif (!empty($error)): ?>
      <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    
    <h2>Create New Research Assignment</h2>

    <form method="POST" action="" enctype="multipart/form-data" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
     <div class="form-grid">
        <div>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>
      </div>

      <div>
        <label for="category">Category / Domain:</label>
        <input type="text" name="category" id="category" required>
      </div>

      <div>
        <label for="due_date">Due Date:</label>
        <input type="date" name="due_date" id="due_date" required>
      </div>

      <div>
        <label for="assignment_file">Upload Assignment File (PDF/DOCX):</label>
        <input type="file" name="assignment_file" id="assignment_file" accept=".pdf,.doc,.docx" required>
      </div>
      
        <div class="create-as-btns">
          <button type="submit">Create Assignment</button>
          <button type="button" onclick="window.location.href='/rses/templates/supervisor-dashboard.php'">Cancel</button>
        </div>
      
     </div>
    </form>
  </div>
</body>
</html>