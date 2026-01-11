<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$assignmentId = $_GET['assignment_id'] ?? null;
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Assignments</title>
  <link rel="stylesheet" href="/rses/assets/css/student-styles.css">
</head>
<body>
    <div class="submit-assignment-div">
        <h3>Submit Your Research Assignment</h3>

        <?php if (isset($_GET['msg'])): ?>
        <div class="std-submit-msg-area">
            <?php
            $messages = [
                'submission_success' => ['Your paper was submitted successfully.', 'success-msg'],
                'upload_error' => ['There was an error uploading your file.', 'error-msg'],
                'move_failed' => ['Failed to save your file. Try again.', 'error-msg'],
                'missing_data' => ['Missing submission data.', 'error-msg'],
                'missing_session' => ['Session expired. Please log in again.', 'error-msg'],
                'invalid_file_type' => ['Only PDF or Word files are allowed.', 'error-msg']
            ];
            if (array_key_exists($_GET['msg'], $messages)) {
                [$text, $class] = $messages[$_GET['msg']];
                echo "<p class='$class'>$text</p>";
            }
            ?>
        </div>
        <?php endif; ?>

        <?php if ($assignmentId): ?>
            <form method="POST" action="/rses/student-pages/submit-assignment-handler.php" enctype="multipart/form-data" class="upload-form">
            <input type="hidden" name="assignment_id" value="<?php echo htmlspecialchars($assignmentId); ?>">

            <div class="file-bar">
                <span id="file-name">No file chosen</span>
                <label for="paper_file" class="file-btn">Choose File</label>
                <input type="file" name="paper_file" id="paper_file" required>
            </div>

            <div class="form-buttons">
                <button type="submit">Submit Assignment</button>
                <a href="/rses/templates/student-dashboard.php"><button type="button">Back</button></a>
            </div>
            </form>

        <?php else: ?>
        <p class="error-msg">No assignment selected.</p>
        <?php endif; ?>
    </div>

<script>
  const fileInput = document.getElementById('paper_file');
  const fileNameDisplay = document.getElementById('file-name');

  fileInput.addEventListener('change', function () {
    fileNameDisplay.textContent = this.files[0]?.name || 'No file chosen';
  });
</script>

</body>
</html>