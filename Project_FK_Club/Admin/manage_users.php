<?php
// =============================================
// ADMIN MANAGE USERS
// FILE: admin/manage_users.php
// =============================================

// =============================================
// START SESSION
// =============================================
session_start();

// =============================================
// SECURITY CHECK
// ONLY ADMIN CAN ACCESS
// =============================================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ./index.php");
    exit();
}

// =============================================
// DATABASE CONNECTION
// =============================================
include '../db_connect.php';

// =============================================
// SEARCH FUNCTION
// SEARCH USER BY NAME / ID / EMAIL / ROLE
// =============================================
$search = "";
$whereClause = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {

    $search = mysqli_real_escape_string($conn, trim($_GET['search']));

    $whereClause = "WHERE users.full_name LIKE '%$search%'
                    OR users.student_id LIKE '%$search%'
                    OR users.email LIKE '%$search%'
                    OR users.role LIKE '%$search%'";
}

// =============================================
// FETCH USER DATA
// INCLUDE CLUB MEMBERSHIP INFO
// =============================================
$query = "
    SELECT users.*,
           clubs.club_name,
           memberships.membership_type
    FROM users
    LEFT JOIN memberships ON users.user_id = memberships.user_id
    LEFT JOIN clubs ON memberships.club_id = clubs.club_id
    $whereClause
    ORDER BY users.created_at DESC
";

$result = mysqli_query($conn, $query);

// =============================================
// USER SESSION INFO
// =============================================
$user_name = $_SESSION['full_name'] ?? 'Admin';
$user_role = 'Administrator';
?>

<title>

<?php echo isset($pageTitle)
? $pageTitle . ' | Manage Users'
: 'Manage Users'; ?>

</title>

<?php include '../Includes/header_admin.php'; ?>

<?php include '../Includes/sidebar_admin.php'; ?>

<div class="topbar">

    <div class="profile-menu">

        <!-- PROFILE BUTTON -->
        <button type="button" class="profile-btn" id="profileButton">

            <div class="profile-info">
                <span class="profile-name">
                    <?php echo htmlspecialchars($user_name); ?>
                </span>

                <span class="profile-role">
                    <?php echo $user_role; ?>
                </span>
            </div>

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

        </button>

        <!-- PROFILE DROPDOWN -->
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

    <!-- PAGE HEADER -->
    <div class="page-header">

        <h1>Manage Users</h1>

        <!-- ADD USER BUTTON -->
        <a href="add_user.php" class="add-btn">
            <i class="fa-solid fa-user-plus"></i>
            Add User
        </a>

    </div>

    <!-- =============================================
         SEARCH FORM
    ============================================== -->
    <form method="GET" class="search-form">

        <input type="text"
               name="search"
               placeholder="Search name, student ID, email or role..."
               value="<?php echo htmlspecialchars($search); ?>">

        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
            Search
        </button>

    </form>

    <!-- =============================================
         USERS TABLE
    ============================================== -->
    <div class="table-container">

        <table>

            <!-- TABLE HEADER -->
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Club</th>
                    <th>Status</th>
                    <th>Registered Date</th>
                    <th>Action</th>
                </tr>
            </thead>

                        <!-- TABLE BODY -->
            <tbody>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php
                // =============================================
                // DISPLAY USER NUMBERING (1,2,3...)
                // =============================================
                $counter = 1;

                while ($row = mysqli_fetch_assoc($result)):
                ?>

                    <tr>

                        <!-- DISPLAY NUMBER -->
                        <td>
                            <?php echo $counter++; ?>
                        </td>

                        <!-- STUDENT ID -->
                        <td>
                            <?php echo htmlspecialchars($row['student_id']); ?>
                        </td>

                        <!-- FULL NAME -->
                        <td>
                            <?php echo htmlspecialchars($row['full_name']); ?>
                        </td>

                        <!-- EMAIL -->
                        <td>
                            <?php echo htmlspecialchars($row['email']); ?>
                        </td>

                        <!-- PHONE -->
                        <td>
                            <?php
                            echo !empty($row['phone_number'])
                                ? htmlspecialchars($row['phone_number'])
                                : '-';
                            ?>
                        </td>

                        <!-- ROLE -->
                        <td>
                            <span class="membership-badge">
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        </td>

                        <!-- CLUB -->
                        <td>
                            <?php
                            echo !empty($row['club_name'])
                                ? htmlspecialchars($row['club_name'])
                                : '-';
                            ?>
                        </td>

                        <!-- STATUS -->
                        <td>
                            <?php
                            $status = !empty($row['status'])
                                ? ucfirst($row['status'])
                                : 'Active';
                            ?>

                            <span class="status-badge <?php echo strtolower($status); ?>">
                                <?php echo $status; ?>
                            </span>
                        </td>

                        <!-- REGISTERED DATE -->
                        <td>
                            <?php
                            echo !empty($row['created_at'])
                                ? date('d M Y', strtotime($row['created_at']))
                                : '-';
                            ?>
                        </td>

                        <!-- ACTION BUTTONS -->
                        <td class="action-buttons">

                            <!-- EDIT USER -->
                            <a href="edit_user.php?id=<?php echo $row['user_id']; ?>"
                               class="edit-btn"
                               title="Edit User">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <!-- DELETE USER -->
                            <a href="#"
                               class="delete-btn"
                               title="Delete User"
                               onclick="openDeleteModal(<?php echo $row['user_id']; ?>)">
                                <i class="fa-solid fa-trash"></i>
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <!-- NO DATA -->
                <tr>
                    <td colspan="10" class="no-data">
                        No users found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- =============================================
     DELETE CONFIRMATION MODAL
============================================= -->
<div id="deleteModal" class="custom-modal">

    <div class="custom-modal-content">

        <!-- WARNING ICON -->
        <div class="modal-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <!-- MODAL TITLE -->
        <h3>Delete User?</h3>

        <!-- MODAL MESSAGE -->
        <p>This action cannot be undone.</p>

        <!-- MODAL BUTTONS -->
        <div class="modal-buttons">

            <!-- CANCEL -->
            <button class="cancel-modal-btn"
                    onclick="closeDeleteModal()">
                Cancel
            </button>

            <!-- CONFIRM -->
            <a id="confirmDeleteBtn"
               href="#"
               class="confirm-modal-btn">
                Delete
            </a>

        </div>

    </div>

</div>

<!-- =============================================
     JAVASCRIPT
============================================= -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    // =============================================
    // PROFILE DROPDOWN
    // =============================================
    const profileBtn = document.getElementById("profileButton");
    const profileDropdown = document.getElementById("profileDropdown");

    profileBtn.addEventListener("click", function (event) {
        event.stopPropagation();
        profileDropdown.classList.toggle("show");
    });

    document.addEventListener("click", function (event) {
        if (!profileBtn.contains(event.target) &&
            !profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove("show");
        }
    });

});

// =============================================
// OPEN DELETE MODAL
// =============================================
function openDeleteModal(id) {
    document.getElementById("deleteModal").style.display = "flex";
    document.getElementById("confirmDeleteBtn").href =
        "delete_user.php?id=" + id;
}

// =============================================
// CLOSE DELETE MODAL
// =============================================
function closeDeleteModal() {
    document.getElementById("deleteModal").style.display = "none";
}

// =============================================
// CLOSE MODAL ON OUTSIDE CLICK
// =============================================
window.onclick = function(event) {
    const modal = document.getElementById("deleteModal");

    if (event.target === modal) {
        modal.style.display = "none";
    }
};
</script>

<?php include '../Includes/footer.php'; ?>