<?php
// =============================================
// EDIT USER PAGE
// FILE: admin/edit_user.php
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
// SECURITY CHECK
// ONLY ADMIN CAN ACCESS
// =============================================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// =============================================
// DATABASE CONNECTION
// =============================================
include '../db_connect.php';

// =============================================
// INITIALIZE VARIABLES
// =============================================
$success_message = "";
$error_message = "";

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// =============================================
// INVALID USER ID CHECK
// =============================================
if ($user_id <= 0) {
    header("Location: manage_users.php");
    exit();
}

// =============================================
// FETCH USER DATA
// =============================================
$query = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: manage_users.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// =============================================
// HANDLE FORM SUBMISSION
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $full_name  = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email      = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone      = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $password   = mysqli_real_escape_string($conn, trim($_POST['password']));
    $role       = mysqli_real_escape_string($conn, trim($_POST['role']));

    // =============================================
    // VALIDATION
    // =============================================
    if (
        empty($student_id) ||
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($role)
    ) {

        $error_message = "All required fields must be filled.";

    } else {

        // =============================================
        // CHECK DUPLICATE USER
        // =============================================
        $duplicate_query = "
            SELECT * FROM users
            WHERE (student_id = '$student_id' OR email = '$email')
            AND user_id != '$user_id'
        ";

        $duplicate_result = mysqli_query($conn, $duplicate_query);

        if (mysqli_num_rows($duplicate_result) > 0) {

            $error_message = "Student ID or Email already exists.";

        } else {

            // =============================================
            // UPDATE WITH PASSWORD
            // =============================================
            if (!empty($password)) {

                $update_query = "
                    UPDATE users SET
                        student_id = '$student_id',
                        full_name = '$full_name',
                        email = '$email',
                        phone = '$phone',
                        password = '$password',
                        role = '$role'
                    WHERE user_id = '$user_id'
                ";

            } else {

                // =============================================
                // UPDATE WITHOUT PASSWORD
                // =============================================
                $update_query = "
                    UPDATE users SET
                        student_id = '$student_id',
                        full_name = '$full_name',
                        email = '$email',
                        phone = '$phone',
                        role = '$role'
                    WHERE user_id = '$user_id'
                ";

            }

            // Execute update
            if (mysqli_query($conn, $update_query)) {

                $success_message = "User updated successfully.";

                // Refresh user data
                $refresh_query = "SELECT * FROM users WHERE user_id = '$user_id'";
                $refresh_result = mysqli_query($conn, $refresh_query);
                $user = mysqli_fetch_assoc($refresh_result);

            } else {

                $error_message = "Failed to update user.";

            }

        }

    }

}

// =============================================
// SESSION USER DATA
// =============================================
$user_name = $_SESSION['full_name'] ?? 'Admin';
$user_role = 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Character Encoding -->
    <meta charset="UTF-8">

    <!-- Responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Page Title -->
    <title>Edit User</title>

    <!-- Main Admin CSS -->
    <link rel="stylesheet" href="../css/admin.css">

    <!-- Add/Edit User CSS -->
    <link rel="stylesheet" href="../css/add_user.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<!-- =============================================
     SIDEBAR
============================================= -->
<div class="sidebar">

    <div class="sidebar-logo">
        <h2>FK CLUB & EVENT</h2>
        <span>Administrator</span>
    </div>

    <div class="sidebar-menu">

        <a href="dashboard_admin.php">
            <i class="fa-solid fa-chart-pie"></i>
            Dashboard
        </a>

        <a href="manage_users.php" class="active">
            <i class="fa-solid fa-users"></i>
            Manage Users
        </a>

        <a href="../manage_clubs.php">
            <i class="fa-solid fa-layer-group"></i>
            Clubs
        </a>

        <a href="manage_memberships.php">
            <i class="fa-solid fa-id-card"></i>
            Membership
        </a>

        <a href="../manage_committee.php">
            <i class="fa-solid fa-user-tie"></i>
            Committee
        </a>

        <a href="../manage_events.php">
            <i class="fa-solid fa-calendar-days"></i>
            Events
        </a>

        <a href="../participation.php">
            <i class="fa-solid fa-chart-line"></i>
            Participation
        </a>

        <a href="../reports.php">
            <i class="fa-solid fa-file-lines"></i>
            Reports
        </a>

    </div>

</div>

<!-- =============================================
     TOPBAR
============================================= -->
<div class="topbar">

    <div class="profile-menu">

        <button type="button" class="profile-btn" id="profileButton">

            <div class="profile-info">
                <span class="profile-name">
                    <?= htmlspecialchars($user_name); ?>
                </span>
                <span class="profile-role">
                    <?= $user_role; ?>
                </span>
            </div>

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

        </button>

        <div class="dropdown-content" id="profileDropdown">

            <a href="profile_admin.php">
                <i class="fa-solid fa-user"></i>
                Manage Profile
            </a>

            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>

        </div>

    </div>

</div>

<!-- =============================================
     MAIN CONTENT
============================================= -->
<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <h1>Edit User</h1>
    </div>

    <!-- Error Popup -->
    <?php if (!empty($error_message)): ?>
        <div class="error-popup">
            <?= $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="table-container">

        <form method="POST" class="user-form">

            <!-- Student ID -->
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id"
                       value="<?= htmlspecialchars($user['student_id']); ?>" required>
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name"
                       value="<?= htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone"
                       value="<?= htmlspecialchars($user['phone']); ?>" required>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label>New Password (Optional)</label>
                <input type="text" name="password"
                       placeholder="Leave blank to keep current password">
            </div>

            <!-- User Role -->
            <div class="form-group">
                <label>User Role</label>
                <select name="role" required>

                    <option value="">Choose Role</option>

                    <option value="student"
                        <?= ($user['role'] == 'student') ? 'selected' : ''; ?>>
                        Student
                    </option>

                    <option value="committee"
                        <?= ($user['role'] == 'committee') ? 'selected' : ''; ?>>
                        Committee
                    </option>

                    <option value="admin"
                        <?= ($user['role'] == 'admin') ? 'selected' : ''; ?>>
                        Admin
                    </option>

                </select>
            </div>

            <!-- Buttons -->
            <div class="form-buttons">

                <button type="submit" class="add-btn">
                    <i class="fas fa-save"></i>
                    Update User
                </button>

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

        <div class="icon">
            <i class="fas fa-check"></i>
        </div>

        <h2>Success!</h2>

        <p><?= $success_message; ?></p>

        <button onclick="window.location.href='manage_users.php'">
            Continue
        </button>

    </div>

</div>
<?php endif; ?>

<!-- =============================================
     PROFILE DROPDOWN SCRIPT
============================================= -->
<script>

document.addEventListener("DOMContentLoaded", function () {

    const profileBtn = document.getElementById("profileButton");
    const profileDropdown = document.getElementById("profileDropdown");

    if (profileBtn && profileDropdown) {

        profileBtn.addEventListener("click", function (event) {
            event.stopPropagation();
            profileDropdown.classList.toggle("show");
        });

        document.addEventListener("click", function (event) {

            if (
                !profileBtn.contains(event.target) &&
                !profileDropdown.contains(event.target)
            ) {
                profileDropdown.classList.remove("show");
            }

        });

    }

});
</script>

</body>
</html>