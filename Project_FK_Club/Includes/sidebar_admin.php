<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
                <img src="../uploads/logo_umpsa.png" alt="Club Logo" style="width: 72px; height: auto; object-fit: contain; align-self: center;">
        <h2>
            FK CLUB & EVENT
        </h2>

        <span>
            Administrator
        </span>

    </div>

    <!-- Menu -->
    <div class="sidebar-menu">

        <a href="dashboard_admin.php"
        class="<?php echo ($current_page === 'dashboard_admin.php') ? 'active' : ''; ?>">

            <i class="fa-solid fa-chart-pie"></i>
            Dashboard

        </a>

        <a href="manage_users.php" class="<?php echo ($current_page === 'manage_users.php') ? 'active' : ''; ?>">

            <i class="fa-solid fa-users"></i>
            Manage Users

        </a>

        <a href="manage_clubs.php" class="<?php echo ($current_page === 'manage_clubs.php') ? 'active' : ''; ?>">

            <i class="fa-solid fa-layer-group"></i>
            Clubs

        </a>

        <a href="manage_memberships.php" class="<?php echo ($current_page === 'manage_memberships.php') ? 'active' : ''; ?>">

            <i class="fa-solid fa-id-card"></i>
            Membership

        </a>

        <a href="manage_committee.php" class="<?php echo ($current_page === 'manage_committee.php') ? 'active' : ''; ?>">

            <i class="fa-solid fa-user-tie"></i>
            Committee

        </a>

        <a href="participation_dashboard.php" class="<?php echo ($current_page === 'participation_dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-bar"></i>
            Participation
        </a>

        <a href="event_attendance_list.php" class="<?php echo in_array($current_page, ['event_attendance_list.php', 'manage_event_attendance.php']) ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-check"></i>
            Attendance
        </a>

        <a href="participant_points.php" class="<?php echo ($current_page === 'participant_points.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-crown"></i>
            Points & Ranking
        </a>

        <a href="event_reports.php" class="<?php echo ($current_page === 'event_reports.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-lines"></i>
            Event Reports
        </a>

    </div>

</div>