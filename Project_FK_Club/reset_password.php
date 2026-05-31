<?php
// =============================================
// RESET PASSWORD SYSTEM
// FILE: reset_password.php
// =============================================
include 'db_connect.php';

// Get reset token from URL
$token = $_GET['token'];

if (isset($_POST['reset'])) {

    // Encrypt new password
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Update password securely
    $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL WHERE reset_token=?");
    $stmt->bind_param("ss", $newPassword, $token);
    $stmt->execute();

    echo "Password successfully reset.";
}
?>

<form method="POST">
    <input type="password" name="password" placeholder="New Password" required>
    <button type="submit" name="reset">Reset Password</button>
</form>