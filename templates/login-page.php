<!-- Stuff on this page: 
 1. Login page html layout.
 2. PHP code for $_SESSION to print login errors.
 3. JavaScript code for client side validation (at the bottom). -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="../assets/css/login-style.css"/> 
</head>

<body>
  <nav>
    <a href="/rses/index.php">
    <img id="logo" src="/rses/assets/images/research-system-logo.png" alt="Research System Logo"/></a>
  </nav>
  <div class="login-box">
        <h2>Log In</h2>

          <div id="login-error" class="error-box">
            <?php
              session_start();
              if (isset($_SESSION['login_error'])) {
                echo htmlspecialchars($_SESSION['login_error']);
                unset($_SESSION['login_error']); // Clear after showing
              }
            ?>
        </div> <!-- this is where I'll put login error message -->
        <form id="login-form" method="POST" action="../handlers/login-handler.php">
         <section>
            <div class="login-form-input">
              <label for="username">Username</label>
              <input type="text" name="username" id="username" minlength="3" maxlength="30">
            </div>

            <div class="login-form-input" style="position: relative;">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" minlength="6" maxlength="30">
            </div>
          </section>
          
            <div class="login-buttons-div">
              <button class="single-login-btn" type="submit" name="submit">Log In</button>
              <button class="single-login-btn" type="Reset">Clear All</button>
            </div>
        </form>
    </div>
<script>
document.getElementById('login-form').addEventListener('submit', function(e) {
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();

  if (username.length < 3 || password.length < 6) {
    e.preventDefault();
    document.getElementById('login-error').textContent = "Please enter valid credentials.";
  }
});
</script>
</body>
</html>