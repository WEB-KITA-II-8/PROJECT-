<?php
session_start();

/* =============================================
   SESSION USER INFO
============================================= */
$user_name = isset($_SESSION['full_name'])
    ? $_SESSION['full_name']
    : 'Committee Member';

/* =============================================
   PARTICIPATION STORAGE (NO DATABASE)
============================================= */
$participationFile = __DIR__ . '/participation.json';

/* Sample Participation Data */
$default_participation = [

    [
        'participation_id' => 1,
        'student_name' => 'Ahmad Firdaus',
        'event_name' => 'Leadership Seminar',
        'attendance_status' => 'Attended',
        'participation_date' => '2026-05-20'
    ],

    [
        'participation_id' => 2,
        'student_name' => 'Siti Nurhaliza',
        'event_name' => 'Volunteer Program',
        'attendance_status' => 'Absent',
        'participation_date' => '2026-05-18'
    ],

    [
        'participation_id' => 3,
        'student_name' => 'Muhammad Danish',
        'event_name' => 'Sports Carnival',
        'attendance_status' => 'Pending',
        'participation_date' => '2026-05-15'
    ]

];

/* Create File if Not Exist */
if (!file_exists($participationFile)) {

    file_put_contents(
        $participationFile,
        json_encode($default_participation, JSON_PRETTY_PRINT)
    );

}

/* Load Participation */
$participation_list = json_decode(
    file_get_contents($participationFile),
    true
) ?: [];

/* =============================================
   SIMPLE STATISTICS
============================================= */

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
    
<!-- =============================================
     TOPBAR
============================================= -->

<div class="topbar">

    <div class="profile-menu">

        <button type="button"
                class="profile-btn"
                id="profileButton">

            <div class="profile-info">

                <span class="profile-name">
                    <?php echo htmlspecialchars($user_name); ?>
                </span>

                <span class="profile-role">
                    Participation Manager
                </span>

            </div>

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

        </button>

        <div class="dropdown-content"
             id="profileDropdown">

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

    <!-- Banner -->
    <div class="participation-banner">

        <div class="participation-banner-content">

            <h1>Participation Management 📊</h1>

            <p>
                Monitor and manage student participation records
            </p>

        </div>

        <div class="participation-banner-icon">
            <i class="fa-solid fa-chart-simple"></i>
        </div>

    </div>

    <!-- Statistics -->
    <div class="participation-stats">

        <div class="participation-stat-card">

            <div class="participation-stat-icon stat-total">
                <i class="fa-solid fa-users"></i>
            </div>

            <div class="participation-stat-details">
                <h4>Total Participation</h4>
                <p><?php echo $total_participation; ?></p>
            </div>

        </div>

        <div class="participation-stat-card">

            <div class="participation-stat-icon stat-attended">
                <i class="fa-solid fa-check"></i>
            </div>

            <div class="participation-stat-details">
                <h4>Attended</h4>
                <p><?php echo $total_attended; ?></p>
            </div>

        </div>

        <div class="participation-stat-card">

            <div class="participation-stat-icon stat-absent">
                <i class="fa-solid fa-xmark"></i>
            </div>

            <div class="participation-stat-details">
                <h4>Absent</h4>
                <p><?php echo $total_absent; ?></p>
            </div>

        </div>

        <div class="participation-stat-card">

            <div class="participation-stat-icon stat-pending">
                <i class="fa-solid fa-clock"></i>
            </div>

            <div class="participation-stat-details">
                <h4>Pending</h4>
                <p><?php echo $total_pending; ?></p>
            </div>

        </div>

    </div>

    <!-- Table Header -->
    <div class="participation-header">

        <h2>
            <i class="fa-solid fa-list"></i>
            Participation List
        </h2>

    </div>

    <!-- Table -->
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
                            <?php
                            echo htmlspecialchars(
                                $participation['student_name']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $participation['event_name']
                            );
                            ?>
                        </td>

                        <td>

                            <?php
                            $status =
                                $participation['attendance_status'];

                            if ($status === 'Attended') {

                                echo '
                                <span class="status-badge attended">
                                    Attended
                                </span>';

                            } elseif ($status === 'Absent') {

                                echo '
                                <span class="status-badge absent">
                                    Absent
                                </span>';

                            } else {

                                echo '
                                <span class="status-badge pending">
                                    Pending
                                </span>';
                            }
                            ?>

                        </td>

                        <td>

                            <?php
                            echo date(
                                "d M Y",
                                strtotime(
                                    $participation['participation_date']
                                )
                            );
                            ?>

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

                <p>
                    No participation data available yet
                </p>

            </div>

        </div>

    <?php } ?>

</div>

<!-- =============================================
     PROFILE DROPDOWN SCRIPT
============================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const profileBtn =
        document.getElementById("profileButton");

    const profileDropdown =
        document.getElementById("profileDropdown");

    if (profileBtn && profileDropdown) {

        profileBtn.addEventListener(
            "click",
            function (event) {

                event.stopPropagation();

                profileDropdown.classList.toggle("show");

            }
        );

        document.addEventListener(
            "click",
            function (event) {

                if (
                    !profileBtn.contains(event.target) &&
                    !profileDropdown.contains(event.target)
                ) {
                    profileDropdown.classList.remove("show");
                }

            }
        );

    }

});

</script>

</body>
</html>