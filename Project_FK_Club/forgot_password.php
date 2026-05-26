<?php
// =============================================
// FORGOT PASSWORD PAGE WITH SUCCESS POPUP
// FILE: forgot_password.php
// =============================================

// Database connection
include 'db_connect.php';

// Popup trigger variable
$success = false;

// Check if form submitted
if (isset($_POST['submit'])) {

    // Get user email input
    $email = $_POST['email'];

    // Generate secure reset token
    $token = bin2hex(random_bytes(32));

    // Save token into database
    $stmt = $conn->prepare("UPDATE users SET reset_token=? WHERE email=?");
    $stmt->bind_param("ss", $token, $email);
    $stmt->execute();

    // Activate popup
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <!-- Mobile responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Page title -->
    <title>Forgot Password</title>

    <!-- Shared CSS -->
    <link rel="stylesheet" href="css/common.css">

    <!-- Forgot password CSS -->
    <link rel="stylesheet" href="css/forgot_password.css">
</head>
<body>

<!-- =============================================
     SUCCESS POPUP
============================================= -->
<?php if ($success) { ?>
<div class="custom-popup">

    <div class="popup-box success-popup">

        <!-- Success icon -->
        <div class="success-icon">✔</div>

        <!-- Success title -->
        <h3>Successful</h3>

        <!-- Success message -->
        <p>Reset link generated successfully.</p>

        <!-- Close popup -->
        <button onclick="closePopup()">OK</button>

    </div>

</div>
<?php } ?>

<!-- =============================================
     FORGOT PASSWORD CONTAINER
============================================= -->
<div class="forgot-container">

    <!-- Faculty logo -->
    <img src="uploads/LogoFKom.png" alt="Faculty Logo" class="logo">

    <!-- Main title -->
    <h2>Forgot Password</h2>

    <!-- Subtitle -->
    <p>Enter your registered email to reset password</p>

    <!-- Reset password form -->
    <form method="POST">

        <!-- User email -->
        <input type="email"
               name="email"
               placeholder="Enter Email"
               required>

        <!-- Submit button -->
        <button type="submit" name="submit">
            Send Reset Link
        </button>

        <!-- Return to login -->
        <a href="index.php" class="back-btn">
            Back to Login
        </a>

    </form>

</div>

<!-- =============================================
     POPUP CLOSE SCRIPT
============================================= -->
<script>
function closePopup() {
    document.querySelector('.custom-popup').style.display = 'none';
}
</script>

</body>
</html>