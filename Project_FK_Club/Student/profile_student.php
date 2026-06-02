<?php
/*=============================================
REGISTERED STUDENT PROFILE MANAGEMENT
FILE: student/profile_student.php
=============================================*/

session_start();
include("../db_connect.php");

/*=============================================
SECURITY CHECK FOR STUDENT ONLY
=============================================*/
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'Student';

/*=============================================
FETCH STUDENT DATA + MEMBERSHIP + CLUB INFO
=============================================*/
function getStudentData($conn, $user_id) {
    $query = "
        SELECT users.*,
               memberships.membership_type,
               memberships.joined_date,
               clubs.club_name
        FROM users
        LEFT JOIN memberships ON users.user_id = memberships.user_id
        LEFT JOIN clubs ON memberships.club_id = clubs.club_id
        WHERE users.user_id = '$user_id'
        LIMIT 1
    ";

    return mysqli_fetch_assoc(mysqli_query($conn, $query));
}

$user = getStudentData($conn, $user_id);

/*=============================================
DEFAULT PROFILE IMAGE
=============================================*/
if (empty($user['profile_image'])) {
    $user['profile_image'] = 'default.png';
}

/*=============================================
DEFAULT DISPLAY VALUES
=============================================*/
$club_name         = $user['club_name'] ?? 'No Club Joined';
$membership_type   = $user['membership_type'] ?? 'Student';
$joined_date       = $user['joined_date'] ?? 'N/A';

/*=============================================
HANDLE PROFILE UPDATE
=============================================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name     = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email         = mysqli_real_escape_string($conn, $_POST['email']);
    $phone         = mysqli_real_escape_string($conn, $_POST['phone']);
    $profile_image = $user['profile_image'];

    /*=========================================
    DELETE PROFILE IMAGE
    =========================================*/
    if (isset($_POST['delete_image'])) {

        if (!empty($user['profile_image']) && $user['profile_image'] !== 'default.png') {
            $old_path = "../uploads/" . $user['profile_image'];

            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }

        $profile_image = 'default.png';
    }

    /*=========================================
    UPLOAD NEW PROFILE IMAGE
    =========================================*/
    elseif (!empty($_FILES['profile_image']['name'])) {

        $target_dir = "../uploads/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name   = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {

            /* Delete old image */
            if (!empty($user['profile_image']) && $user['profile_image'] !== 'default.png') {
                $old_path = "../uploads/" . $user['profile_image'];

                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }

            $profile_image = $file_name;
        }
    }

    /*=========================================
    UPDATE PASSWORD IF PROVIDED
    =========================================*/
    if (!empty($_POST['new_password'])) {

        $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);

        $updateQuery = "
            UPDATE users SET
                full_name     = '$full_name',
                email         = '$email',
                phone         = '$phone',
                password      = '$new_password',
                profile_image = '$profile_image'
            WHERE user_id = '$user_id'
        ";

    } else {

        $updateQuery = "
            UPDATE users SET
                full_name     = '$full_name',
                email         = '$email',
                phone         = '$phone',
                profile_image = '$profile_image'
            WHERE user_id = '$user_id'
        ";
    }

    /*=========================================
    EXECUTE UPDATE
    =========================================*/
    if (mysqli_query($conn, $updateQuery)) {

        $_SESSION['full_name'] = $full_name;

        header("Location: profile_student.php?updated=1");
        exit();

    } else {
        $error = "Profile update failed.";
    }
}

/* Refresh updated data */
$user = getStudentData($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>

    <link rel="stylesheet" href="../css/student.css">
    <link rel="stylesheet" href="../css/profile_student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <h2>FK CLUB & EVENT</h2>
        <span>Registered Student</span>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard_student.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
        <a href="../manage_clubs.php"><i class="fa-solid fa-layer-group"></i>Clubs</a>
        <a href="membership_student.php"><i class="fa-solid fa-id-card"></i>Membership</a>
        <a href="../manage_events.php"><i class="fa-solid fa-calendar-days"></i>Events</a>
        <a href="participation_student.php"><i class="fa-solid fa-chart-line"></i>Participation</a>
        <a href="../reports.php"><i class="fa-solid fa-file-lines"></i>Reports</a>
    </div>
</div>

<div class="topbar">
    <div class="profile-menu">
        <button type="button" class="profile-btn" id="profileButton">
            <div class="profile-info">
                <span class="profile-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <span class="profile-role"><?php echo htmlspecialchars($membership_type); ?></span>
            </div>
            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </button>

        <div class="dropdown-content" id="profileDropdown">
            <a href="profile_student.php"><i class="fa-solid fa-user"></i>Manage Profile</a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
        </div>
    </div>
</div>

<div class="main-content">
    <h1>Manage Profile</h1>

    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="profile-container">
        <form method="POST" enctype="multipart/form-data">

            <div class="profile-image-section full-width">
                <img src="../uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-image-preview">
                <div class="upload-group">
                    <input type="file" name="profile_image">
                </div>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>New Password (Optional)</label>
                <input type="password" name="new_password" placeholder="Leave blank to keep current password">
            </div>

            <div class="membership-box full-width">
                <h3>Club Membership Information</h3>
                <div class="membership-grid">
                    <div><strong>Club Name:</strong><br><?php echo htmlspecialchars($club_name); ?></div>
                    <div><strong>Membership Type:</strong><br><?php echo htmlspecialchars($membership_type); ?></div>
                    <div><strong>Joined Date:</strong><br><?php echo htmlspecialchars($joined_date); ?></div>
                </div>
            </div>

            <div id="deletePopup" class="popup-overlay" style="display:none;">
                <div class="delete-popup">
                    <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h2>Delete Image?</h2>
                    <p>Are you sure you want to permanently remove your profile image?</p>
                    <div class="popup-buttons">
                        <button type="button" class="cancel-popup-btn" onclick="closeDeletePopup()">Cancel</button>
                        <button type="submit" name="delete_image" class="confirm-delete-btn">Delete</button>
                    </div>
                </div>
            </div>

            <div class="form-actions full-width">
                <button type="submit" class="save-btn">Save Changes</button>
                <button type="button" class="delete-image-btn" onclick="showDeletePopup()">Delete Image</button>
                <a href="dashboard_student.php" class="cancel-btn">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php if (isset($_GET['updated'])): ?>
<div class="popup-overlay" id="successPopup">
    <div class="success-popup">
        <div class="icon"><i class="fa-solid fa-circle-check"></i></div>
        <h2>Profile Updated!</h2>
        <p>Your profile has been successfully updated.</p>
        <button type="button" onclick="closePopup()">OK</button>
    </div>
</div>
<?php endif; ?>

<script>
function closePopup() {
    const popup = document.getElementById("successPopup");
    if (popup) popup.style.display = "none";
    if (window.history.replaceState) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

function showDeletePopup() {
    document.getElementById("deletePopup").style.display = "flex";
}

function closeDeletePopup() {
    document.getElementById("deletePopup").style.display = "none";
}

window.addEventListener("click", function(event) {
    const deletePopup = document.getElementById("deletePopup");
    if (event.target === deletePopup) closeDeletePopup();
});

document.addEventListener("DOMContentLoaded", function () {
    const profileBtn = document.getElementById("profileButton");
    const profileDropdown = document.getElementById("profileDropdown");

    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener("click", function (event) {
            event.stopPropagation();
            profileDropdown.classList.toggle("show");
        });

        document.addEventListener("click", function (event) {
            if (!profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove("show");
            }
        });
    }
});
</script>

</body>
</html>