<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/includes/db_connection.php';

$paperId = $_GET['paper_id'] ?? null;

if (!$paperId) {
  echo "<p>Invalid paper ID.</p>";
  exit;
}

// Fetch submission details
$query = "SELECT rp.*, a.title AS assignment_title, u.username AS student_name
  FROM research_papers rp
  JOIN assignments a ON rp.assignment_id = a.assignment_id
  JOIN users u ON rp.submitted_by = u.user_id
  WHERE rp.paper_id = ?
";
$stmt = $connect_db->prepare($query);
$stmt->bind_param("i", $paperId);
$stmt->execute();
$result = $stmt->get_result();
$paper = $result->fetch_assoc();
$stmt->close();

if (!$paper) {
  echo "<p>Submission not found.</p>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Evaluate Submission</title>
  <link rel="stylesheet" href="/rses/assets/css/supervisor-styles.css">
  <style>
    
  </style>
</head>
<body>
  <div class="sup-eval-page">
    <h2>Evaluate Submission</h2>
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="success-message">Paper evaluated successfully!</div>
    <?php endif; ?>

    <div class="evaluation-container">
      <!-- LEFT SIDE: Submission Info + Form -->
      <div class="eval-left">
        <p><strong>Assignment Title:</strong> <?php echo htmlspecialchars($paper['assignment_title']); ?></p>
        <p><strong>Student:</strong> <?php echo htmlspecialchars($paper['student_name']); ?></p>
        <p><strong>Submitted on:</strong> <?php echo htmlspecialchars(date('d-m-Y', strtotime($paper['submission_date']))); ?></p>
        <p>
          <strong>Download Submission:</strong>
          <a href="<?php echo htmlspecialchars($paper['file_path']); ?>" download>Download File</a>
        </p>

        <form class="eval-form" action="/rses/supervisor-pages/process-evaluation.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="evaluated_by" value="<?php echo $_SESSION['user_id']; ?>">
          <input type="hidden" name="paper_id" value="<?php echo $paperId; ?>">

            <div class="rating-row">
            <label for="rating" class="rating-label">Rating:</label>
              <div class="star-rating">
                <input type="radio" name="rating" id="star5" value="5"><label for="star5">★</label>
                <input type="radio" name="rating" id="star4" value="4"><label for="star4">★</label>
                <input type="radio" name="rating" id="star3" value="3"><label for="star3">★</label>
                <input type="radio" name="rating" id="star2" value="2"><label for="star2">★</label>
                <input type="radio" name="rating" id="star1" value="1"><label for="star1">★</label>
              </div>
            </div>


          <label>Status:</label>
          <select name="status" required>
            <option value="">-- Select Status --</option>
            <option value="accepted">Accepted</option>
            <option value="rejected">Rejected</option>
            <option value="needs improvement">Needs Improvement</option>
            <option value="accepted and published">Accepted and Published</option>
          </select>

          <label>Comments:</label>
          <textarea name="feedback" rows="5" placeholder="Write your feedback here..."></textarea>

          <label>Upload Commented File (optional):</label>
          <input type="file" name="commented_file" accept=".pdf,.doc,.docx">

          <button type="submit" class="submit-btn">Submit Evaluation</button>
          <button type="button" class="back-btn" onclick="window.location.href='supervisor-dashboard.php'">Back</button>
        </form>
      </div>

      <!-- RIGHT SIDE: Evaluation Criteria -->
      <div class="eval-right">
        <h3>Evaluation Criteria</h3>
        <table class="criteria-table">
            <thead>
            <tr>
                <th>Criterion</th>
                <th>Description</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Relevance</td>
                <td>Does the paper address the assigned topic and objectives?</td>
            </tr>
            <tr>
                <td>Originality</td>
                <td>Is the work creative, insightful, or novel?</td>
            </tr>
            <tr>
                <td>Research Quality</td>
                <td>Are sources credible, current, and well-integrated?</td>
            </tr>
            <tr>
                <td>Structure & Clarity</td>
                <td>Is the paper well-organized and easy to follow?</td>
            </tr>
            <tr>
                <td>Language & Grammar</td>
                <td>Is the writing grammatically correct and stylistically appropriate?</td>
            </tr>
            <tr>
                <td>Formatting</td>
                <td>Does it follow the required format (APA, MLA, etc.)?</td>
            </tr>
            </tbody>
        </table>
        </div>
    </div>
  </div>
</body>
</html>