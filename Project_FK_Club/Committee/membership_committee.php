<?php
/* =============================================
COMMITTEE MEMBERSHIP PAGE
FILE: committee/membership_committee.php
============================================= */

session_start();
include('../db_connect.php');

/* =============================================
SESSION SECURITY
============================================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'committee') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =============================================
GET COMMITTEE USER INFO
============================================= */
$user_query = mysqli_query($conn, "
    SELECT full_name, profile_image
    FROM users
    WHERE user_id = '$user_id'
");
$user = mysqli_fetch_assoc($user_query);

$committee_name = $user['full_name'] ?? 'Committee Member';
$profile_image = !empty($user['profile_image']) ? "../uploads/" . $user['profile_image'] : "../uploads/default.png";

/* =============================================
GET COMMITTEE CLUB ID
============================================= */
$club_query = mysqli_query($conn, "
    SELECT club_id
    FROM memberships
    WHERE user_id = '$user_id'
    LIMIT 1
");

$club_data = mysqli_fetch_assoc($club_query);
$club_id = $club_data['club_id'] ?? 0;

/* =============================================
GET CLUB NAME
============================================= */
$club_name = "N/A";

if ($club_id > 0) {
    $club_result = mysqli_query($conn, "
        SELECT club_name
        FROM clubs
        WHERE club_id = '$club_id'
        LIMIT 1
    ");

    if ($club_row = mysqli_fetch_assoc($club_result)) {
        $club_name = $club_row['club_name'];
    }
}

/* =============================================
SEARCH FILTER
============================================= */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$membership_query = "
    SELECT 
        users.student_id,
        users.full_name,
        users.email,
        memberships.membership_type,
        memberships.membership_status,
        memberships.joined_date
    FROM memberships
    INNER JOIN users ON memberships.user_id = users.user_id
    WHERE memberships.club_id = '$club_id'
";

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $membership_query .= "
        AND (
            users.full_name LIKE '%$search_safe%'
            OR users.student_id LIKE '%$search_safe%'
            OR users.email LIKE '%$search_safe%'
        )
    ";
}

$membership_query .= " ORDER BY memberships.joined_date DESC";

$membership_result = mysqli_query($conn, $membership_query);
?>

 <title>Manage Membership | Committee</title>

<?php include '../Includes/header_comm.php'; ?>
<?php include '../Includes/sidebar_comm.php'; ?>


<div class="topbar">

    <div class="profile-menu">

        <button class="profile-btn" onclick="toggleDropdown()">

            <div class="profile-info">
                <span class="profile-name"><?php echo htmlspecialchars($committee_name); ?></span>
                <span class="profile-role">Committee Member</span>
            </div>

            <div class="profile-icon">
                <i class="fas fa-user"></i>
            </div>

        </button>

        <div class="dropdown-content" id="profileDropdown">
            <a href="profile_committee.php">
                <i class="fas fa-user"></i> Manage Profile
            </a>

            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

    </div>

</div>

<div class="main-content">

    <h1>Manage Membership</h1>

    <div class="mini-cards">

        <div class="mini-card">
            <h4>Total Club Members</h4>
            <p><?php echo mysqli_num_rows($membership_result); ?></p>
        </div>

        <div class="mini-card">
            <h4>Club Name</h4>
            <p style="font-size: 24px; word-break: break-word;"><?php echo htmlspecialchars($club_name); ?></p>
        </div>

    </div>

    <div class="dashboard-card">

        <div class="card-header">
            <h2>Club Membership Members</h2>
        </div>

        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search by name, student ID or email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">
                <i class="fas fa-search"></i> Search
            </button>
        </form>

        <?php if (mysqli_num_rows($membership_result) > 0): ?>

            <table class="membership-table">

                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Membership Type</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($row = mysqli_fetch_assoc($membership_result)): ?>

                        <tr>
                            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($row['membership_type'])); ?></td>

                            <td>
                                <span class="status-badge <?php echo strtolower($row['membership_status']) === 'active' ? 'status-active' : 'status-pending'; ?>">
                                    <?php echo htmlspecialchars($row['membership_status']); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo date("d M Y", strtotime($row['joined_date'])); ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty-state">
                No membership records found.
            </div>

        <?php endif; ?>

    </div>

</div>

<script>
function toggleDropdown() {
    document.getElementById("profileDropdown").classList.toggle("show");
}

window.onclick = function(event) {
    if (!event.target.closest('.profile-menu')) {
        document.getElementById("profileDropdown").classList.remove("show");
    }
}
</script>

<?php include '../Includes/footer.php'; ?>