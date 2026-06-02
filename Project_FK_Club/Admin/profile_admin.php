<?php
// =============================================
// ADMIN PROFILE
// FILE: admin/profile_admin.php
// =============================================

// Start session
session_start();

// =============================================
// SECURITY CHECK
// Only administrators can access
// =============================================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// =============================================
// DATABASE CONNECTION
// =============================================
include '../db_connect.php';

// Current logged in admin user ID
$user_id = $_SESSION['user_id'];

// =============================================
// FETCH USER DATA
// =============================================
$query = "SELECT * FROM users WHERE user_id='$user_id' LIMIT 1";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// =============================================
// DEFAULT PROFILE IMAGE
// =============================================
if (empty($user['profile_image'])) {
    $user['profile_image'] = 'default.png';
}

// =============================================
// HANDLE PROFILE UPDATE FORM
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize input
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $phone     = mysqli_real_escape_string($conn, $_POST['phone']);

    // Keep current image
    $profile_image = $user['profile_image'];

    // =============================================
    // DELETE PROFILE IMAGE
    // =============================================
    if (isset($_POST['delete_image'])) {

        // Delete old uploaded image
        if (!empty($user['profile_image']) &&
            $user['profile_image'] !== 'default.png') {

            $old_path = "../uploads/" . $user['profile_image'];

            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }

        // Reset to default
        $profile_image = 'default.png';
    }

    // =============================================
    // UPLOAD NEW PROFILE IMAGE
    // =============================================
    elseif (!empty($_FILES['profile_image']['name'])) {

        $target_dir  = "../uploads/";
        $file_name   = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $file_name;

        // Upload new image
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {

            // Delete previous uploaded image
            if (!empty($user['profile_image']) &&
                $user['profile_image'] !== 'default.png') {

                $old_path = "../uploads/" . $user['profile_image'];

                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }

            $profile_image = $file_name;
        }
    }

    // =============================================
    // PASSWORD UPDATE
    // =============================================
    if (!empty($_POST['new_password'])) {

        $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);

        $updateQuery = "
            UPDATE users SET
                full_name='$full_name',
                email='$email',
                phone='$phone',
                password='$new_password',
                profile_image='$profile_image'
            WHERE user_id='$user_id'
        ";

    } else {

        // Keep current password
        $updateQuery = "
            UPDATE users SET
                full_name='$full_name',
                email='$email',
                phone='$phone',
                profile_image='$profile_image'
            WHERE user_id='$user_id'
        ";
    }

    // =============================================
    // EXECUTE PROFILE UPDATE
    // =============================================
    if (mysqli_query($conn, $updateQuery)) {

        // Update session name
        $_SESSION['full_name'] = $full_name;

        // Redirect with success
        header("Location: profile_admin.php?updated=1");
        exit();

    } else {
        $error = "Profile update failed.";
    }
}

// =============================================
// REFRESH USER DATA
// =============================================
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// =============================================
// SESSION DISPLAY
// =============================================
$user_name = $_SESSION['full_name'] ?? 'Administrator';
$user_role = 'Administrator';
?>

<?php include '../Includes/header_admin.php'; ?>

<?php include '../Includes/sidebar_admin.php'; ?>

<!-- =============================================
     MAIN CONTENT
============================================= -->
<div class="main-content">

    <h1>Manage Profile</h1>

    <!-- Error -->
    <?php if (!empty($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Profile Form -->
    <div class="profile-container">

        <form method="POST" enctype="multipart/form-data">

            <!-- =============================================
                 PROFILE IMAGE
            ============================================= -->
            <div class="profile-image-section">

                <img src="../uploads/<?php echo htmlspecialchars($user['profile_image']); ?>"
                     alt="Profile Image"
                     class="profile-image-preview">

                <div class="upload-group">
                    <input type="file" name="profile_image">
                </div>

            </div>

            <!-- Full Name -->
            <div class="form-group">
                <label>Full Name</label>
                <input type="text"
                       name="full_name"
                       value="<?php echo htmlspecialchars($user['full_name']); ?>"
                       required>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label>Email</label>
                <input type="email"
                       name="email"
                       value="<?php echo htmlspecialchars($user['email']); ?>"
                       required>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text"
                       name="phone"
                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                       required>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label>New Password (Optional)</label>
                <input type="password"
                       name="new_password"
                       placeholder="Leave blank to keep current password">
            </div>

            <!-- =============================================
                 DELETE IMAGE POPUP
            ============================================= -->
            <div id="deletePopup" class="popup-overlay" style="display:none;">

                <div class="delete-popup">

                    <div class="icon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <h2>Delete Image?</h2>

                    <p>
                        Are you sure you want to permanently remove your
                        profile image? This action cannot be undone.
                    </p>

                    <div class="popup-buttons">

                        <button type="button"
                                class="cancel-popup-btn"
                                onclick="closeDeletePopup()">
                            Cancel
                        </button>

                        <button type="submit"
                                name="delete_image"
                                class="confirm-delete-btn">
                            Delete
                        </button>

                    </div>

                </div>

            </div>

            <!-- =============================================
                 FORM ACTIONS
            ============================================= -->
            <div class="form-actions">

                <!-- Save -->
                <button type="submit"
                        name="save_changes"
                        class="save-btn">
                    Save Changes
                </button>

                <!-- Delete -->
                <button type="button"
                        class="delete-image-btn"
                        onclick="showDeletePopup()">
                    Delete Image
                </button>

                <!-- Cancel -->
                <a href="dashboard_admin.php" class="cancel-btn">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

<!-- =============================================
     SUCCESS POPUP
============================================= -->
<?php if (isset($_GET['updated'])): ?>

<div class="popup-overlay" id="successPopup">

    <div class="success-popup">

        <div class="icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <h2>Profile Updated!</h2>

        <p>
            Your profile information has been successfully updated.
        </p>

        <button type="button" onclick="closePopup()">
            OK
        </button>

    </div>

</div>

<?php endif; ?>

<!-- =============================================
     JAVASCRIPT
============================================= -->
<script>

// Close success popup
function closePopup() {

    const popup = document.getElementById("successPopup");

    if (popup) {
        popup.style.display = "none";
    }

    if (window.history.replaceState) {
        window.history.replaceState(
            {},
            document.title,
            window.location.pathname
        );
    }
}

// Show delete popup
function showDeletePopup() {
    document.getElementById("deletePopup").style.display = "flex";
}

// Close delete popup
function closeDeletePopup() {
    document.getElementById("deletePopup").style.display = "none";
}

// Close popup if clicked outside
window.addEventListener("click", function(event) {

    const deletePopup = document.getElementById("deletePopup");

    if (event.target === deletePopup) {
        closeDeletePopup();
    }

});

</script>

<?php include '../Includes/footer.php'; ?>