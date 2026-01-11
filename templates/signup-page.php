<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../assets/css/signup-page-style.css"/>
  <script src="../assets/js/validate-sign-up-form-input.js" defer></script>
</head>
<body>
<nav>
  <a href="/rses/index.php">
  <img id="logo" src="../assets/images/research-system-logo.png" alt="Research System Logo"/></a>
</nav>
  
<div class="signup-box">
  <h2>SIGN UP</h2>
  <p>Get started with streamlined research submission and evaluation for students and suprvisors.</p>
  <div id="signup-error-msg">
    <?php session_start();
    if (!empty($_SESSION['signup_errors'])) {
      echo '<ul>';
      foreach ($_SESSION['signup_errors'] as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
      }
      echo '</ul>';
      unset($_SESSION['signup_errors']);
    }
  ?>
  </div>

 <form id="signup-form" method="POST" action="/rses/handlers/signup-submission.php">
    <!-- Role Selection -->
    <div class="role-toggle">
      <label>You are a</label>
      <div class="role-options">
        <input type="radio" name="role" id="student" value="student" required>
        <label for="student" class="role-btn">STUDENT</label>

        <input type="radio" name="role" id="supervisor" value="supervisor">
        <label for="supervisor" class="role-btn">SUPERVISOR</label>
      </div>
    </div>
  <div class="contner-fr-inputs">
    <!-- Common Fields -->
    <div id="common-fields" style="display:none;">
      <div class="form-input-div"><label for="username">Full Name</label><input type="text" name="username" id="username" required></div>
      <div class="form-input-div"><label for="email">Email</label><input type="email" name="email" id="email" required></div>
      <div class="form-input-div"><label for="password">Set Password</label><input type="password" name="password" id="password" required minlength="6" maxlength="30"></div>
      <div class="form-input-div"><label for="confirm-password">Confirm Password</label><input type="password" name="confirm-password" id="confirm-password" required minlength="6" maxlength="30"></div>
    </div>

    <!-- Supervisor Fields -->
    <div id="supervisor-fields" class="role-specific" style="display:none;">
      <div class="form-input-div"><label for="sup-affiliation">Current Affiliation</label><input type="text" name="sup-affiliation" id="sup-affiliation"></div>
      <div class="form-input-div"><label for="sup-publications">Number of Publications</label><input type="number" name="sup-publications" id="sup-publications" min="0"></div>

      <div class="form-input-div area-of-expertise">
        <label>Area of Expertise</label>
        <div class="checkbox-options">
          <div class="checkbox-item"><label for="ai"><input type="checkbox" name="expertise[]" id="ai" value="AI">AI</label></div>
          <div class="checkbox-item"><label for="cybersec"><input type="checkbox" name="expertise[]" id="cybersec" value="Cybersecurity">Cybersecurity</label></div>
          <div class="checkbox-item"><label for="datascience"><input type="checkbox" name="expertise[]" id="datascience" value="Data Science">Data Science</label></div>
          <div class="checkbox-item"><label for="iot"><input type="checkbox" name="expertise[]" id="iot" value="IoT">IoT</label></div>
          <div class="checkbox-item"><label for="robotics"><input type="checkbox" name="expertise[]" id="robotics" value="Robotics">Robotics</label></div>
          <div class="checkbox-item"><label for="biotech"><input type="checkbox" name="expertise[]" id="biotech" value="Biotech">Biotech</label></div>
          <div class="checkbox-item"><label for="quantum"><input type="checkbox" name="expertise[]" id="quantum" value="Quantum Computing">Quantum Computing</label></div>
          <div class="checkbox-item"><label for="softwareng"><input type="checkbox" name="expertise[]" id="softwareng" value="Software Engineering">Software Engineering</label></div>
          <div class="checkbox-item"><label for="fintech"><input type="checkbox" name="expertise[]" id="fintech" value="Financial Technology">Financial Technology</label></div>
        </div>
      </div>
    </div>
          <!-- Student Fields -->
    <div id="student-fields" class="role-specific" style="display:none;">
      <div class="form-input-div"><label for="stu-program">Program</label><input type="text" name="stu-program" id="stu-program"></div>
      <div class="form-input-div"><label for="stu-year">Year of Study</label><input type="number" name="stu-year" id="stu-year"></div>
      <div class="form-input-div"><label for="institute">Institute Name</label><input type="text" name="institute" id="institute"></div>
    </div>

  </div>

  <div class="signup-buttons-div" style="display:none;" id="signup-buttons">
    <button class="reg-btn" type="submit">SUBMIT</button>
    <button class="reg-btn" type="reset">CLEAR ALL</button>
  </div>
 </form>

</div>
</body>
</html>