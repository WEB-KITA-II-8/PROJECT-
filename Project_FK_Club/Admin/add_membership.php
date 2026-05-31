<?php
// =============================================
// ADMIN ADD MEMBERSHIP
// FILE: admin/add_membership.php
// =============================================

// Start session
session_start();

// =============================================
// DATABASE CONNECTION
// =============================================
include '../db_connect.php';


// =============================================
// FETCH USERS
// Only student and committee users
// =============================================
$userQuery = "
    SELECT user_id, full_name, student_id
    FROM users
    WHERE role IN ('student', 'committee')
    ORDER BY full_name ASC
";

$userResult = mysqli_query($conn, $userQuery);


// =============================================
// FETCH CLUBS
// =============================================
$clubQuery = "
    SELECT club_id, club_name
    FROM clubs
    ORDER BY club_name ASC
";

$clubResult = mysqli_query($conn, $clubQuery);


// =============================================
// FORM SUBMISSION
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form values
    $user_id = intval($_POST['user_id']);
    $club_id = intval($_POST['club_id']);
    $membership_type = mysqli_real_escape_string($conn, $_POST['membership_type']);
    $committee_role = mysqli_real_escape_string($conn, $_POST['committee_role']);
    $joined_date = mysqli_real_escape_string($conn, $_POST['joined_date']);

    // Insert membership
    $insertQuery = "
        INSERT INTO memberships (
            user_id,
            club_id,
            membership_type,
            committee_role,
            joined_date
        )
        VALUES (
            '$user_id',
            '$club_id',
            '$membership_type',
            '$committee_role',
            '$joined_date'
        )
    ";

    // Execute insert
    if (mysqli_query($conn, $insertQuery)) {

        // Redirect after success
        header("Location: manage_memberships.php?success=1");
        exit();

    } else {

        $error = "Failed to add membership.";

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
    <h1>Add Membership</h1>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- =============================================
         FORM CONTAINER
    ============================================= -->
    <div class="form-container">

        <form method="POST">

            <!-- User Selection -->
            <div class="form-group">
                <label>Select User</label>

                <select name="user_id" required>
                    <option value="">Choose Student/Committee Member</option>

                    <?php while ($user = mysqli_fetch_assoc($userResult)): ?>
                        <option value="<?php echo $user['user_id']; ?>">
                            <?php echo htmlspecialchars($user['student_id'] . " - " . $user['full_name']); ?>
                        </option>
                    <?php endwhile; ?>

                </select>
            </div>


            <!-- Club Selection -->
            <div class="form-group">
                <label>Select Club</label>

                <select name="club_id" required>
                    <option value="">Choose Club</option>

                    <?php while ($club = mysqli_fetch_assoc($clubResult)): ?>
                        <option value="<?php echo $club['club_id']; ?>">
                            <?php echo htmlspecialchars($club['club_name']); ?>
                        </option>
                    <?php endwhile; ?>

                </select>
            </div>


            <!-- Membership Type -->
            <div class="form-group">
                <label>Membership Type</label>

                <select name="membership_type" required>
                    <option value="">Choose Type</option>
                    <option value="member">Member</option>
                    <option value="committee">Committee</option>
                </select>
            </div>


            <!-- Committee Role -->
            <div class="form-group">
                <label>Committee Role</label>

                <select name="committee_role">
                    <option value="">None</option>
                    <option value="President">President</option>
                    <option value="Secretary">Secretary</option>
                    <option value="Treasurer">Treasurer</option>
                    <option value="Committee Member">Committee Member</option>
                </select>
            </div>


            <!-- Joined Date -->
            <div class="form-group">
                <label>Joined Date</label>
                <input type="date" name="joined_date" required>
            </div>


            <!-- Submit Button -->
            <div class="form-actions">

                <button type="submit" class="submit-btn">
                    <i class="fa-solid fa-save"></i>
                    Save Membership
                </button>

                <a href="manage_memberships.php" class="cancel-btn">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

<?php include '../Includes/footer.php'; ?>