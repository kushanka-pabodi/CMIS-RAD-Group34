<!-- admin.php -->
<?php
// Start the session at the very beginning
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>

  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet" />

  <!-- Font Awesome (for icons) -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

  <style>
    /* Body background and centering */
    body {
      background: url("image/valueedu.jpeg") no-repeat center center;
      background-size: cover;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }

    /* Card styling */
    .login-card {
      max-width: 400px;
      width: 100%;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
      background: #fff;
      padding: 30px;
    }

    .login-card h3 {
      margin-bottom: 20px;
      text-align: center;
    }

    .form-control {
      border-radius: 20px;
    }

    .btn-login {
      border-radius: 20px;
      width: 100%;
      font-weight: 500;
    }
  </style>
</head>

<body>
  <div class="login-card">
    <h3>Admin Login</h3>

    <?php
    
    // Get "redirect" param from URL if present
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

    // Check if there's an "error" param in the URL
    if (isset($_GET['error']) && !empty($_GET['error'])) {
      echo '<div class="alert alert-danger" role="alert">'
        . htmlspecialchars($_GET['error']) .
        '</div>';
    }

    // Check for login error via GET parameter
    if (isset($_GET['loginError']) && !empty($_GET['loginError'])) {
      echo '<div class="alert alert-danger" role="alert">'
        . htmlspecialchars($_SESSION['login_error'] ?? 'An error occurred.') .
        '</div>';
      unset($_SESSION['login_error']); // Clear the error after displaying
    }

    // Check for success message in the session
    if (isset($_SESSION['success_message'])) {
      $message = htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8');
      echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
      echo $message;
      echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
      echo '</div>';
      unset($_SESSION['success_message']);
    }
    ?>

    <!-- The form posts to login.php -->
    <form action="login.php" method="POST">
      <!-- Hidden field to carry the redirect param through -->
      <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

      <!-- Username with icon -->
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="fa fa-user"></i>
          </span>
          <input
            type="text"
            class="form-control"
            id="username"
            name="username"
            placeholder="Enter username"
            required />
        </div>
      </div>

      <!-- Password with toggle visibility icon -->
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="fa fa-lock"></i>
          </span>
          <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            placeholder="Enter password"
            required />
          <!-- Button to toggle password visibility -->
          <button
            type="button"
            class="btn btn-outline-secondary"
            onclick="togglePassword()">
            <i class="fa fa-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <!-- Login button -->
      <button type="submit" class="btn btn-primary btn-login">
        Log In
      </button>
    </form>
  </div>

  <!-- Bootstrap JS (for optional interactivity) -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
  </script>

  <!-- Password toggle script -->
  <script>
    function togglePassword() {
      const passwordField = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');

      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
      } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
      }
    }
    // Check URL for "loginError" parameter and display an alert
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('loginError')) {
      alert('Invalid username or password.');
    }
  </script>
</body>

</html>