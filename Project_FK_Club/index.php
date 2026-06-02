<?php

// Start session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <!-- Mobile responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Page title -->
    <title>FK Student Club & Event Management System</title>

    <!-- Main CSS -->

    <link rel="stylesheet" href="CSS/login.css">

    <!-- Font Awesome for eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- JavaScript -->
    <script src="js/script.js"></script>
</head>
<body>

<!-- =============================================
     LOGIN ERROR POPUP
============================================= -->
<?php if (isset($_GET['error'])) { ?>
<div class="custom-popup">

    <div class="popup-box">

        <!-- Warning title -->
        <h3>⚠ Warning</h3>

        <!-- Warning message -->
        <p>Invalid email, password, or role. Please try again.</p>

        <!-- Close button -->
        <button onclick="closePopup()">OK</button>

    </div>

</div>
<?php } ?>

<!-- =============================================
     LOGIN CONTAINER
============================================= -->
<div class="login-container">

    <!-- Faculty logo -->
    <img src="Uploads/LogoFKom_nb.png" alt="Faculty Logo" class="logo">

    <!-- System title -->
    <h2>FK Student Club & Event</h2>

    <!-- Subtitle -->
    <p>Please login to access your account</p>

    <!-- Login form -->
    <form action="login_process.php" method="POST">

        <!-- User email -->
        <input type="email"
               name="email"
               placeholder="Username"
               required>

        <!-- =============================================
             PASSWORD FIELD WITH TOGGLE
        ============================================= -->
        <div class="password-container">

            <!-- Password -->
            <input type="password"
                   name="password"
                   id="password"
                   placeholder="Password"
                   required>

            <!-- Eye icon -->
            <span class="toggle-password"
                  id="toggleIcon"
                  onclick="togglePassword()">

                <i class="fa fa-eye-slash"></i>

            </span>

        </div>

        <!-- Login button -->
        <button type="submit" class="login-btn">
            Login
        </button>

        <!-- Forgot password -->
        <a href="forgot_password.php" class="forgot-btn">
            Forgot Password
        </a>

        <br>

        <!-- Register button -->
        <a href="registration.php" class="register-link" style="text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
            Didn't have an account? Register here
        </a>

    </form>

</div>

<!-- =============================================
     POPUP SCRIPT
============================================= -->
<script>
function closePopup() {
    document.querySelector('.custom-popup').style.display = 'none';
}
</script>

</body>
</html>