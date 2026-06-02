<?php
session_start();

$user_name = $_SESSION['full_name'] ?? 'Committee Member';

include('../db_connect.php');

$user_name = $_SESSION['full_name'] ?? 'Committee Member';

include('../db_connect.php');

$result = mysqli_query($conn, "
    SELECT
        r.registration_id AS participation_id,
        r.student_name,
        e.event_name,
        'Pending' AS attendance_status,
        DATE(r.registered_at) AS participation_date
    FROM event_registrations r
    JOIN events_comm e
        ON r.event_id = e.event_id
    ORDER BY r.registered_at DESC
");

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}

$participation_list = [];

while ($row = mysqli_fetch_assoc($result)) {
    $participation_list[] = $row;
}

$total_participation = count($participation_list);

$total_attended = count(array_filter(
    $participation_list,
    fn($p) => $p['attendance_status'] === 'Attended'
));

$total_absent = count(array_filter(
    $participation_list,
    fn($p) => $p['attendance_status'] === 'Absent'
));

$total_pending = count(array_filter(
    $participation_list,
    fn($p) => $p['attendance_status'] === 'Pending'
));
?>

<title>Participation Management</title>

<?php include('../Includes/header_comm.php'); ?>
<?php include('../Includes/sidebar_comm.php'); ?>

<div class="topbar">

    <div class="profile-menu">

        <button type="button" class="profile-btn" id="profileButton">

            <div class="profile-info">
                <span class="profile-name">
                    <?= htmlspecialchars($user_name) ?>
                </span>

                <span class="profile-role">
                    Participation Manager
                </span>
            </div>

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

        </button>

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

<div class="main-content">

    <div class="participation-banner">

        <div class="participation-banner-content">
            <h1>Participation Management 📊</h1>
            <p>Monitor and manage student participation records</p>
        </div>

        <div class="participation-banner-icon">
            <i class="fa-solid fa-chart-simple"></i>
        </div>

    </div>

    <div class="participation-stats">

        <div class="participation-stat-card">
            <div class="participation-stat-icon stat-total">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="participation-stat-details">
                <h4>Total Participation</h4>
                <p><?= $total_participation ?></p>
            </div>
        </div>

        <div class="participation-stat-card">
            <div class="participation-stat-icon stat-attended">
                <i class="fa-solid fa-check"></i>
            </div>
            <div class="participation-stat-details">
                <h4>Attended</h4>
                <p><?= $total_attended ?></p>
            </div>
        </div>

        <div class="participation-stat-card">
            <div class="participation-stat-icon stat-absent">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <div class="participation-stat-details">
                <h4>Absent</h4>
                <p><?= $total_absent ?></p>
            </div>
        </div>

        <div class="participation-stat-card">
            <div class="participation-stat-icon stat-pending">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="participation-stat-details">
                <h4>Pending</h4>
                <p><?= $total_pending ?></p>
            </div>
        </div>

    </div>

    <div class="participation-header">
        <h2>
            <i class="fa-solid fa-list"></i>
            Participation List
        </h2>
    </div>

    <?php if (count($participation_list) > 0) { ?>

        <div class="participation-table-card">

            <table>

                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Event</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($participation_list as $participation) { ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($participation['student_name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($participation['event_name']) ?>
                        </td>

                        <td>
                            <?php
                            $status = $participation['attendance_status'];

                            if ($status === 'Attended') {
                                echo '<span class="status-badge attended">Attended</span>';
                            } elseif ($status === 'Absent') {
                                echo '<span class="status-badge absent">Absent</span>';
                            } else {
                                echo '<span class="status-badge pending">Pending</span>';
                            }
                            ?>
                        </td>

                        <td>
                            <?= date("d M Y", strtotime($participation['participation_date'])) ?>
                        </td>

                        <td class="participation-actions">

                            <button class="action-btn btn-view">
                                <i class="fa-solid fa-eye"></i>
                                View
                            </button>

                            <button class="action-btn btn-edit">
                                <i class="fa-solid fa-pen"></i>
                                Edit
                            </button>

                            <button class="action-btn btn-delete">
                                <i class="fa-solid fa-trash"></i>
                                Delete
                            </button>

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    <?php } else { ?>

        <div class="participation-table-card">

            <div class="empty-state">

                <i class="fa-solid fa-users-slash"></i>

                <h3>No Participation Records</h3>

                <p>No participation data available yet</p>

            </div>

        </div>

    <?php } ?>

</div>

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