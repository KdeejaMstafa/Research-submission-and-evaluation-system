<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role_level'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Help & Support</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* Container */
    .help-container {
      background-color: #faf7e9ff;
      padding: 2em;
      border-radius: 5px;
      font-family: "Open Sans", 'Courier New', Courier, monospace;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    /* Headings */
    .help-container h1,
    .help-container h2 {
      font-family: "Libre Baskerville", serif, 'Courier New', Courier, monospace;
      color: #00324A;
    }

    /* FAQ box */
    .faq-list {
      list-style: none;
      padding: 0;
      margin: 1em 0;
      background-color: #00324A;
      border-radius: 5px;
      overflow: hidden;
    }

    .faq-item {
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    .faq-question {
      cursor: pointer;
      padding: 1em;
      color: aliceblue;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .faq-question:hover {
      background-color: #13526f;
    }

    .faq-answer {
      display: none;
      padding: 0 1em 1em 1em;
      color: aliceblue;
      font-weight: normal;
    }
  </style>
</head>
<body>
  <div class="help-container">
    <h2>Help & Support</h2>

    <?php if ($role === 'student'): ?>
      <h3>For Students</h3>
      <p>Welcome to the RSES system. Here’s how you can use it:</p>
      <ul>
        <li><strong>Submit Papers:</strong> Go to <em>Published Papers → Upload</em>.</li>
        <li><strong>Check Evaluations:</strong> View feedback in your <em>Dashboard → Notifications</em>.</li>
        <li><strong>Profile:</strong> Keep your student details updated for supervisors.</li>
      </ul>

      <h3>Frequently Asked Questions</h3>
      <ul class="faq-list">
        <li class="faq-item">
          <div class="faq-question">Why can’t I upload my paper?</div>
          <div class="faq-answer">Ensure your file is in the correct format (PDF/DOCX) and under the size limit.</div>
        </li>
        <li class="faq-item">
          <div class="faq-question">Where do I see supervisor feedback?</div>
          <div class="faq-answer">Feedback appears in your Dashboard under Notifications once the supervisor submits it.</div>
        </li>
        <li class="faq-item">
          <div class="faq-question">I forgot my password.</div>
          <div class="faq-answer">Use the Reset Password option on the login page.</div>
        </li>
      </ul>

      <p>If you face issues, contact the system administrator at 
         <a href="mailto:admin@rses.edu">admin@rses.edu</a>.
      </p>

    <?php elseif ($role === 'supervisor'): ?>
      <h3>For Supervisors</h3>
      <p>Welcome to the RSES system. Here’s how you can use it:</p>
      <ul>
        <li><strong>Evaluate Papers:</strong> Check pending submissions in your <em>Dashboard → Pending Evaluations</em>.</li>
        <li><strong>Provide Feedback:</strong> Use the evaluation form to give constructive comments.</li>
        <li><strong>Monitor Students:</strong> Track assigned students’ submissions and deadlines.</li>
      </ul>

      <h3>Frequently Asked Questions</h3>
      <ul class="faq-list">
        <li class="faq-item">
          <div class="faq-question">How do I know when a student submits a paper?</div>
          <div class="faq-answer">You’ll see a notification in your Dashboard under Pending Evaluations.</div>
        </li>
        <li class="faq-item">
          <div class="faq-question">Can I edit feedback after submitting?</div>
          <div class="faq-answer">No, feedback is final once submitted. Contact the admin if corrections are needed.</div>
        </li>
        <li class="faq-item">
          <div class="faq-question">Why don’t I see a student in my list?</div>
          <div class="faq-answer">Only students officially assigned to you appear in your dashboard.</div>
        </li>
      </ul>

      <p>If you face issues, contact the system administrator at 
         <a href="mailto:admin@rses.edu">admin@rses.edu</a>.
      </p>

    <?php else: ?>
      <h2>General Help</h2>
      <p>Please log in to access role‑specific help. For assistance, contact 
         <a href="mailto:admin@rses.edu">admin@rses.edu</a>.
      </p>
    <?php endif; ?>
  </div>

  <script>
    // FAQ accordion toggle
    document.querySelectorAll('.faq-question').forEach(question => {
      question.addEventListener('click', () => {
        const answer = question.nextElementSibling;
        if (answer.style.display === "block") {
          answer.style.display = "none";
        } else {
          answer.style.display = "block";
        }
      });
    });
  </script>
</body>
</html>