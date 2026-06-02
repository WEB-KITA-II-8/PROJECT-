<?php
// =============================================
// ADD USER PAGE
// FILE: admin/add_user.php
// =============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include '../db_connect.php';

$success_message = "";
$error_message = "";

/* =============================================
   AUTO GENERATE USER ID
============================================= */
function generateUserID($conn, $role)
{
    if ($role === 'student') {
        $prefix = 'S';
    } elseif ($role === 'committee') {
        $prefix = 'C';
    } elseif ($role === 'admin') {
        $prefix = 'A';
    } else {
        return false;
    }

    $query = mysqli_query(
        $conn,
        "SELECT student_id
         FROM users
         WHERE student_id LIKE '$prefix%'
         ORDER BY student_id DESC
         LIMIT 1"
    );

    if ($query && mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        $last_number = (int) substr($row['student_id'], 1);
        $new_number = $last_number + 1;
    } else {
        $new_number = 1;
    }

    return $prefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);
}

/* =============================================
   HANDLE FORM SUBMISSION
============================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone     = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $password  = mysqli_real_escape_string($conn, trim($_POST['password']));
    $role      = mysqli_real_escape_string($conn, trim($_POST['role']));

    $profile_image = "default.png";

    if (
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($password) ||
        empty($role)
    ) {
        $error_message = "All fields are required.";
    } else {

        $student_id = generateUserID($conn, $role);

        if ($student_id === false) {
            $error_message = "Invalid role selected.";
        } else {

            $check_query = "
                SELECT * FROM users
                WHERE email = '$email'
            ";

            $check_result = mysqli_query($conn, $check_query);

            if (mysqli_num_rows($check_result) > 0) {
                $error_message = "Email already exists.";
            } else {

                $insert_query = "
                    INSERT INTO users
                    (student_id, full_name, email, phone, password, role, profile_image, created_at)
                    VALUES
                    ('$student_id', '$full_name', '$email', '$phone', '$password', '$role', '$profile_image', NOW())
                ";

                if (mysqli_query($conn, $insert_query)) {
                    $success_message = "User added successfully. Generated ID: " . $student_id;
                } else {
                    $error_message = "Failed to add user.";
                }
            }
        }
    }
}
?>

<?php include '../Includes/header_admin.php'; ?>
<?php include '../Includes/sidebar_admin.php'; ?>

<div class="main-content">

    <div class="page-header">
        <h1>Add User</h1>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="error-popup">
            <?= $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="table-container">

        <form method="POST" class="user-form">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" required>
            </div>

            <div class="form-group">
                <label>User Role</label>
                <select name="role" required>
                    <option value="">Choose Role</option>
                    <option value="student">Student</option>
                    <option value="committee">Committee</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="form-buttons">

                <button type="submit" class="add-btn">
                    <i class="fas fa-save"></i>
                    Save User
                </button>

                <a href="manage_users.php" class="cancel-btn">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

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

<?php include '../Includes/footer.php'; ?>