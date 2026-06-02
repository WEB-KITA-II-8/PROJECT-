<?php
// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// =============================================
// USER INFO
// =============================================
$user_name = isset($_SESSION['full_name'])
    ? htmlspecialchars($_SESSION['full_name'])
    : 'Admin';

$user_role = 'Administrator';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Administrator Dashboard
    </title>

    <!-- CSS -->
    <link rel="stylesheet"
    href="../CSS/admin.css">

    <link rel="stylesheet"
    href="../CSS/manage_users.css">

    <link rel="stylesheet"
    href="../CSS/add_user.css">

    <link rel="stylesheet"
    href="../CSS/manage_memberships.css">

    <link rel="stylesheet" 
    href="../CSS/add_membership.css">

    <link rel="stylesheet" 
    href="../CSS/edit_membership.css">

    <link rel="stylesheet"
    href="../CSS/profile_admin.css">

    <link rel="stylesheet"
    href="../CSS/event_reports.css">

    <link rel="stylesheet"
    href="../CSS/manage_clubs.css">

    <link rel="stylesheet"
    href="../CSS/manage_comm.css">

    <link rel="stylesheet"
    href="../CSS/participant_points.css">

    <link rel="stylesheet"
    href="../CSS/attendance_dashboard.css">

    <link rel="stylesheet"
    href="../CSS/add_comm.css">

    <link rel="stylesheet" href="../CSS/manage_event_attendance.css">

    <link rel="icon" type="image/png" href="../Uploads/logo_umpsa.png">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

</head>

<div class="topbar">

    <!-- Profile Menu -->
    <div class="profile-menu">

        <button type="button"
        class="profile-btn"
        id="profileButton">

            <div class="profile-info">

                <span class="profile-name">
                    <?php echo $user_name; ?>
                </span>

                <span class="profile-role">
                    <?php echo $user_role; ?>
                </span>

            </div>

            <div class="profile-icon">

                <i class="fa-solid fa-user"></i>

            </div>

        </button>

        <!-- Dropdown -->
        <div class="dropdown-content"
        id="profileDropdown">

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
     PROFILE DROPDOWN SCRIPT
============================================= -->

<script>

document.addEventListener(
"DOMContentLoaded",
function () {

    const profileBtn =
    document.getElementById(
    "profileButton"
    );

    const profileDropdown =
    document.getElementById(
    "profileDropdown"
    );

    if (
    profileBtn
    &&
    profileDropdown
    ) {

        profileBtn.addEventListener(
        "click",
        function (event) {

            event.stopPropagation();

            profileDropdown
            .classList
            .toggle("show");

        });

        document.addEventListener(
        "click",
        function (event) {

            if (
            !profileBtn.contains(event.target)
            &&
            !profileDropdown.contains(event.target)
            ) {

                profileDropdown
                .classList
                .remove("show");
            }
        });

    }

});

</script>