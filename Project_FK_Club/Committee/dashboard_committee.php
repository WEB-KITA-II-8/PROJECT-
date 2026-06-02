<?php
// =============================================
// COMMITTEE MEMBER DASHBOARD
// FILE: committee/dashboard_committee.php
// =============================================

session_start();

/* =============================================
   SECURITY CHECK
============================================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'committee') {
    header("Location: ../index.php");
    exit();
}

/* =============================================
   DATABASE CONNECTION
============================================= */
include '../db_connect.php';

/* =============================================
   SESSION USER INFO
============================================= */
$user_id   = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['full_name'] ?? 'Committee Member';

/* =============================================
   DEFAULT VALUES
============================================= */
$club_id          = 0;
$club_name        = "No Club Assigned";
$committee_role   = "Committee Member";
$membership_type  = "Committee";
$joined_date      = "N/A";

/* =============================================
   FETCH MEMBERSHIP + CLUB DATA
============================================= */
$membership_query = mysqli_query(
    $conn,
    "SELECT 
        m.club_id,
        m.committee_role,
        m.membership_type,
        m.joined_date,
        c.club_name
     FROM memberships m
     LEFT JOIN clubs c ON m.club_id = c.club_id
     WHERE m.user_id = '$user_id'
     LIMIT 1"
);

if ($membership_query && mysqli_num_rows($membership_query) > 0) {

    $membership_data = mysqli_fetch_assoc($membership_query);

    $club_id         = $membership_data['club_id'] ?? 0;
    $club_name       = $membership_data['club_name'] ?? 'No Club Assigned';
    $committee_role  = !empty($membership_data['committee_role'])
                        ? $membership_data['committee_role']
                        : 'Committee Member';
    $membership_type = $membership_data['membership_type'] ?? 'Committee';
    $joined_date     = $membership_data['joined_date'] ?? 'N/A';
}

/* =============================================
   DASHBOARD SUMMARY COUNTS
============================================= */

/* Total Members */
$total_members = 0;
$total_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM memberships
     WHERE club_id = '$club_id'"
);

if ($total_query) {
    $total_data = mysqli_fetch_assoc($total_query);
    $total_members = $total_data['total'] ?? 0;
}

/* Pending Approval */
$pending_approval = 0;
$pending_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM memberships
     WHERE club_id = '$club_id'
     AND membership_status = 'Pending'"
);

if ($pending_query) {
    $pending_data = mysqli_fetch_assoc($pending_query);
    $pending_approval = $pending_data['total'] ?? 0;
}

/* Upcoming Events */
$upcoming_events = 0;
$upcoming_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM events
     WHERE event_date >= CURDATE()"
);

if ($upcoming_query) {
    $upcoming_data = mysqli_fetch_assoc($upcoming_query);
    $upcoming_events = $upcoming_data['total'] ?? 0;
}

/* Events This Month */
$events_this_month = 0;
$monthly_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM events
     WHERE MONTH(event_date) = MONTH(CURDATE())
     AND YEAR(event_date) = YEAR(CURDATE())"
);

if ($monthly_query) {
    $monthly_data = mysqli_fetch_assoc($monthly_query);
    $events_this_month = $monthly_data['total'] ?? 0;
}
?>
<title>Committee Dashboard</title>

<?php include '../Includes/header_comm.php'; ?>
<?php include '../Includes/sidebar_comm.php'; ?>

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
                    <?php echo htmlspecialchars($committee_role); ?>
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
            <div style="margin-top: 20px;">
                <form method="POST" action="../change_role.php">

                    <button type="submit" name="role" value="student"
                        onclick="return confirm('Are you sure you want to become a Student? You will lose committee access.')"
                        style="padding:10px 15px; background:red; color:white; border:none; border-radius:8px;">
                        Switch to Student Role
                    </button>

                </form>
            </div>

        </div>

    </div>

</div>

<!-- =============================================
     MAIN CONTENT
============================================= -->
<div class="main-content">

    <h1>Committee Member Dashboard</h1>

    <!-- =============================================
         SUMMARY MINI CARDS
    ============================================= -->
    <div class="mini-cards">

        <div class="mini-card">
            <h4>Total Members</h4>
            <p><?php echo $total_members; ?></p>
        </div>

        <div class="mini-card">
            <h4>Pending Approval</h4>
            <p><?php echo $pending_approval; ?></p>
        </div>

        <div class="mini-card">
            <h4>Upcoming Events</h4>
            <p><?php echo $upcoming_events; ?></p>
        </div>

        <div class="mini-card">
            <h4>Events This Month</h4>
            <p><?php echo $events_this_month; ?></p>
        </div>

    </div>

    <!-- =============================================
         MAIN DASHBOARD SECTIONS
    ============================================= -->
    <div class="committee-sections">

        <!-- =============================================
             CLUB MEMBERSHIP INFORMATION
        ============================================= -->
        <div class="dashboard-card">

            <div class="card-header">
                <h3>Club Membership Information</h3>
            </div>

            <div class="info-box">

                <div class="info-item">
                    <h4>Club Name</h4>
                    <p><?php echo htmlspecialchars($club_name); ?></p>
                </div>

                <div class="info-item">
                    <h4>Committee Role</h4>
                    <p><?php echo htmlspecialchars($committee_role); ?></p>
                </div>

                <div class="info-item">
                    <h4>Membership Type</h4>
                    <p><?php echo htmlspecialchars(ucfirst($membership_type)); ?></p>
                </div>

                <div class="info-item">
                    <h4>Joined Date</h4>
                    <p>
                        <?php
                            echo (!empty($joined_date) && $joined_date !== 'N/A')
                                ? date("d M Y", strtotime($joined_date))
                                : 'N/A';
                        ?>
                    </p>
                </div>

            </div>

        </div>

        <!-- =============================================
             CLUB SUMMARY
        ============================================= -->
        <div class="dashboard-card">

            <div class="card-header">
                <h3>Club Summary</h3>
            </div>

            <div class="announcement-box">

                <div class="announcement-item">
                    <h4>Total Club Members</h4>
                    <p><?php echo $total_members; ?> registered members</p>
                </div>

                <div class="announcement-item">
                    <h4>Pending Membership Requests</h4>
                    <p><?php echo $pending_approval; ?> pending approval(s)</p>
                </div>

                <div class="announcement-item">
                    <h4>Upcoming Club Events</h4>
                    <p><?php echo $upcoming_events; ?> upcoming events available</p>
                </div>

            </div>

        </div>

        <!-- =============================================
             RECENT ANNOUNCEMENTS
        ============================================= -->
        <div class="dashboard-card">

            <div class="card-header">
                <h3>Recent Announcements</h3>
            </div>

            <div class="announcement-box">

                <div class="announcement-item">
                    <h4>Reminder</h4>
                    <p>Please manage club activities and memberships regularly.</p>
                </div>

                <div class="announcement-item">
                    <h4>Participation</h4>
                    <p>Monitor student engagement and maintain active participation.</p>
                </div>

                <div class="announcement-item">
                    <h4>System Notice</h4>
                    <p>Keep membership data updated for accurate club records.</p>
                </div>

            </div>

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

<?php include '../Includes/footer.php'; ?>