<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'committee') {
    header("Location: ../index.php");
    exit();
}

include '../db_connect.php';

$user_name = $_SESSION['full_name'] ?? 'Committee Member';

/* =============================================
   FETCH COMMITTEE MEMBERS
============================================= */
$committee_query = mysqli_query(
    $conn,
    "SELECT 
        u.full_name,
        u.email,
        m.committee_role,
        m.membership_status
     FROM memberships m
     LEFT JOIN users u ON m.user_id = u.user_id
     WHERE m.membership_type = 'committee'"
);
?>

<title>Committee Members</title>

<?php include('../Includes/header_comm.php'); ?>
<?php include('../Includes/sidebar_comm.php'); ?>

<!-- =============================================
     TOPBAR
============================================= -->
<div class="topbar">

    <div class="profile-menu">

        <button type="button" class="profile-btn" id="profileButton">

            <div class="profile-info">

                <span class="profile-name">
                    <?php echo htmlspecialchars($user_name); ?>
                </span>

                <span class="profile-role">
                    Committee Member
                </span>

            </div>

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

        </button>

        <!-- Dropdown -->
        <div class="dropdown-content" id="profileDropdown">

            <a href="profile_committee.php">
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

    <div class="committee-table-card">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2>Committee Members</h2>

            <button class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                Add Committee
            </button>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Committee Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                if ($committee_query && mysqli_num_rows($committee_query) > 0) {

                    $no = 1;

                    while ($row = mysqli_fetch_assoc($committee_query)) {
                ?>

                    <tr>

                        <td><?php echo $no++; ?></td>

                        <td>
                            <?php echo htmlspecialchars($row['full_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['email']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['committee_role']); ?>
                        </td>

                        <td>

                            <?php
                            $status = $row['membership_status'];

                            if ($status == 'Active') {
                                echo '<span class="status-badge status-active">Active</span>';
                            } else {
                                echo '<span class="status-badge status-pending">Pending</span>';
                            }
                            ?>

                        </td>

                        <td>

                            <button class="btn btn-warning btn-sm">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button class="btn btn-danger btn-sm">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </td>

                    </tr>

                <?php
                    }
                } else {
                ?>

                    <tr>
                        <td colspan="6" class="text-center">
                            No committee members found.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

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