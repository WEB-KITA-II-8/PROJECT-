<?php
/*=============================================
ADMIN EDIT MEMBERSHIP
FILE: admin/edit_membership.php
=============================================*/

session_start();
include("../db_connect.php");

/* =============================================
   SECURITY CHECK
============================================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

/* =============================================
   SESSION USER INFO
============================================= */
$user_name = $_SESSION['full_name'] ?? 'Administrator';
$user_role = "Administrator";

/* =============================================
   GET MEMBERSHIP ID
============================================= */
if (!isset($_GET['id'])) {
    header("Location: manage_memberships.php");
    exit();
}

$membership_id = intval($_GET['id']);

/* =============================================
   FETCH CURRENT MEMBERSHIP DATA
============================================= */
$query = "SELECT * FROM memberships WHERE membership_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $membership_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$membership = mysqli_fetch_assoc($result);

if (!$membership) {
    header("Location: manage_memberships.php");
    exit();
}

/* =============================================
   FETCH USERS
============================================= */
$users_query = "SELECT user_id, student_id, full_name 
                FROM users 
                WHERE role IN ('student', 'committee')";
$users_result = mysqli_query($conn, $users_query);

/* =============================================
   FETCH CLUBS
============================================= */
$clubs_query = "SELECT club_id, club_name 
                FROM clubs 
                WHERE status = 'active'";
$clubs_result = mysqli_query($conn, $clubs_query);

/* =============================================
   UPDATE MEMBERSHIP
============================================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = intval($_POST['user_id']);
    $club_id = intval($_POST['club_id']);
    $membership_type = mysqli_real_escape_string($conn, $_POST['membership_type']);
    $committee_role = mysqli_real_escape_string($conn, $_POST['committee_role']);
    $joined_date = $_POST['joined_date'];

    $update_query = "UPDATE memberships 
                     SET user_id=?, club_id=?, membership_type=?, committee_role=?, joined_date=? 
                     WHERE membership_id=?";

    $update_stmt = mysqli_prepare($conn, $update_query);

    mysqli_stmt_bind_param(
        $update_stmt,
        "iisssi",
        $user_id,
        $club_id,
        $membership_type,
        $committee_role,
        $joined_date,
        $membership_id
    );

    if (mysqli_stmt_execute($update_stmt)) {
        header("Location: manage_memberships.php?updated=success");
        exit();
    } else {
        $error = "Failed to update membership.";
    }
}
?>

<?php include '../Includes/header_admin.php'; ?>

<?php include '../Includes/sidebar_admin.php'; ?>   


<!-- =============================================
     TOP HEADER
============================================= -->
<header class="top-header">

    <div class="user-profile">

        <!-- White Profile Box -->
        <div class="user-box">

            <div class="user-info">
                <strong><?php echo htmlspecialchars($user_name); ?></strong>
                <span><?php echo $user_role; ?></span>
            </div>

            <div class="user-icon">
                <i class="fa-solid fa-user"></i>
            </div>

        </div>

        <!-- Dropdown Menu -->
        <div class="profile-dropdown">

            <a href="profile_admin.php">
                <i class="fa-solid fa-user"></i>
                Profile
            </a>

            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>

        </div>

    </div>

</header>


<!-- =============================================
     MAIN CONTENT
============================================= -->
<main class="main-content">

    <!-- Page Title -->
    <h1>Edit Membership</h1>

    <!-- Error Message -->
    <?php if (isset($error)) : ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>


    <!-- =============================================
         FORM CONTAINER
    ============================================= -->
    <div class="form-container">

        <form method="POST">

            <!-- Select User -->
            <div class="form-group">
                <label>Select User</label>
                <select name="user_id" required>

                    <?php while ($user = mysqli_fetch_assoc($users_result)) : ?>

                        <option value="<?php echo $user['user_id']; ?>"
                            <?php if ($user['user_id'] == $membership['user_id']) echo 'selected'; ?>>

                            <?php echo $user['student_id'] . " - " . $user['full_name']; ?>

                        </option>

                    <?php endwhile; ?>

                </select>
            </div>


            <!-- Select Club -->
            <div class="form-group">
                <label>Select Club</label>
                <select name="club_id" required>

                    <?php while ($club = mysqli_fetch_assoc($clubs_result)) : ?>

                        <option value="<?php echo $club['club_id']; ?>"
                            <?php if ($club['club_id'] == $membership['club_id']) echo 'selected'; ?>>

                            <?php echo $club['club_name']; ?>

                        </option>

                    <?php endwhile; ?>

                </select>
            </div>


            <!-- Membership Type -->
            <div class="form-group">
                <label>Membership Type</label>
                <select name="membership_type" required>

                    <option value="Member"
                        <?php if ($membership['membership_type'] == 'Member') echo 'selected'; ?>>
                        Member
                    </option>

                    <option value="Committee"
                        <?php if ($membership['membership_type'] == 'Committee') echo 'selected'; ?>>
                        Committee
                    </option>

                    <option value="Active Member"
                        <?php if ($membership['membership_type'] == 'Active Member') echo 'selected'; ?>>
                        Active Member
                    </option>

                </select>
            </div>


            <!-- Committee Role -->
            <div class="form-group">
                <label>Committee Role</label>
                <select name="committee_role">

                    <option value="None">None</option>
                    <option value="President">President</option>
                    <option value="Vice President">Vice President</option>
                    <option value="Secretary">Secretary</option>
                    <option value="Treasurer">Treasurer</option>
                    <option value="Member">Member</option>

                </select>
            </div>


            <!-- Joined Date -->
            <div class="form-group">
                <label>Joined Date</label>
                <input type="date"
                       name="joined_date"
                       value="<?php echo $membership['joined_date']; ?>"
                       required>
            </div>


            <!-- Form Buttons -->
            <div class="form-actions">

                <!-- Save -->
                <button type="submit" class="save-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Update Membership
                </button>

                <!-- Cancel -->
                <a href="manage_memberships.php" class="cancel-btn">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</main>

<?php include '../Includes/footer.php'; ?>