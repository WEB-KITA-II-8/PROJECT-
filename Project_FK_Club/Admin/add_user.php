<?php
// =============================================
// ADD USER PAGE
// FILE: admin/add_user.php
// =============================================

// =============================================
// SHOW ERRORS FOR DEBUGGING
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =============================================
// START SESSION
// =============================================
session_start();

// =============================================
// DATABASE CONNECTION
// =============================================
include '../db_connect.php';

// =============================================
// INITIALIZE VARIABLES
// =============================================
$success_message = "";
$error_message = "";

// =============================================
// HANDLE FORM SUBMISSION
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize form inputs
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $full_name  = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email      = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone      = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $password   = mysqli_real_escape_string($conn, trim($_POST['password']));
    $role       = mysqli_real_escape_string($conn, trim($_POST['role']));

    // Default profile image
    $profile_image = "default.png";

    // =============================================
    // VALIDATE REQUIRED FIELDS
    // =============================================
    if (
        empty($student_id) ||
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($password) ||
        empty($role)
    ) {

        $error_message = "All fields are required.";

    } else {

        // =============================================
        // CHECK FOR DUPLICATE USER
        // =============================================
        $check_query = "
            SELECT * FROM users
            WHERE student_id = '$student_id'
            OR email = '$email'
        ";

        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {

            $error_message = "Student ID or Email already exists.";

        } else {

            // =============================================
            // INSERT NEW USER
            // =============================================
            $insert_query = "
                INSERT INTO users
                (student_id, full_name, email, phone, password, role, profile_image, created_at)
                VALUES
                ('$student_id', '$full_name', '$email', '$phone', '$password', '$role', '$profile_image', NOW())
            ";

            if (mysqli_query($conn, $insert_query)) {

                $success_message = "User added successfully.";

            } else {

                $error_message = "Failed to add user.";

            }

        }

    }

}

?>

<?php include '../Includes/header_admin.php'; ?>

<?php include '../Includes/sidebar_admin.php'; ?>

<!-- =============================================
     MAIN CONTENT
============================================= -->
<div class="main-content">

    <!-- Page Title -->
    <div class="page-header">
        <h1>Add User</h1>
    </div>

    <!-- =============================================
         ERROR POPUP
    ============================================= -->
    <?php if (!empty($error_message)): ?>
        <div class="error-popup">
            <?= $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- =============================================
         FORM CONTAINER
    ============================================= -->
    <div class="table-container">

        <form method="POST" class="user-form">

            <!-- Student ID -->
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" required>
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" required>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" required>
            </div>

            <!-- User Role -->
            <div class="form-group">
                <label>User Role</label>
                <select name="role" required>
                    <option value="">Choose Role</option>
                    <option value="student">Student</option>
                    <option value="committee">Committee</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- =============================================
                 FORM BUTTONS
            ============================================= -->
            <div class="form-buttons">

                <!-- Save User -->
                <button type="submit" class="add-btn">
                    <i class="fas fa-save"></i>
                    Save User
                </button>

                <!-- Cancel -->
                <a href="manage_users.php" class="cancel-btn">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

<!-- =============================================
     SUCCESS POPUP
============================================= -->
<?php if (!empty($success_message)): ?>
<div class="popup-overlay">

    <div class="success-popup">

        <!-- Success Icon -->
        <div class="icon">
            <i class="fas fa-check"></i>
        </div>

        <!-- Popup Title -->
        <h2>Success!</h2>

        <!-- Popup Message -->
        <p><?= $success_message; ?></p>

        <!-- Continue Button -->
        <button onclick="window.location.href='manage_users.php'">
            Continue
        </button>

    </div>

</div>
<?php endif; ?>

<?php include '../Includes/footer.php'; ?>