<?php
session_start();
include("../db_connect.php");

/* SECURITY CHECK */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'committee') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'Committee Member';

/* GET COMMITTEE ROLE */
$committee_role = 'Committee Member';

$query = mysqli_query($conn, "
    SELECT committee_role
    FROM memberships
    WHERE user_id = '$user_id'
    LIMIT 1
");

if ($query && mysqli_num_rows($query) > 0) {
    $row = mysqli_fetch_assoc($query);

    if (!empty($row['committee_role'])) {
        $committee_role = $row['committee_role'];
    }
}
?>

<title>Event & Participation Report</title>

<?php include('../Includes/header_comm.php'); ?>
<?php include('../Includes/sidebar_comm.php'); ?>

<div class="topbar">
    <div class="profile-menu">
        <button type="button" class="profile-btn" id="profileButton">
            <div class="profile-info">
                <span class="profile-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <span class="profile-role"><?php echo htmlspecialchars($committee_role); ?></span>
            </div>
            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </button>

        <div class="dropdown-content" id="profileDropdown">
            <a href="profile_committee.php"><i class="fa-solid fa-user"></i>Manage Profile</a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
        </div>
    </div>
</div>


<div class="main-content">

        <div class="report-container">

            <!-- REPORT HEADER -->

            <div class="report-header">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h2>
                            Event & Participation Report
                        </h2>

                        <p>
                            Faculty Club Management System
                        </p>

                    </div>

                    <button class="btn btn-primary"
                    onclick="window.print()">

                        <i class="fa-solid fa-print"></i>
                        Print Report

                    </button>

                </div>

            </div>

            <!-- SUMMARY -->

            <div class="summary-cards">

                <div class="summary-card">

                    <div class="summary-icon icon-blue">

                        <i class="fa-solid fa-calendar-days"></i>

                    </div>

                    <div class="summary-info">

                        <h4>
                            Total Events
                        </h4>

                        <h2>
                            24
                        </h2>

                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-icon icon-green">

                        <i class="fa-solid fa-users"></i>

                    </div>

                    <div class="summary-info">

                        <h4>
                            Total Participants
                        </h4>

                        <h2>
                            1,240
                        </h2>

                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-icon icon-orange">

                        <i class="fa-solid fa-chart-line"></i>

                    </div>

                    <div class="summary-info">

                        <h4>
                            Active Events
                        </h4>

                        <h2>
                            12
                        </h2>

                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-icon icon-red">

                        <i class="fa-solid fa-check"></i>

                    </div>

                    <div class="summary-info">

                        <h4>
                            Completed Events
                        </h4>

                        <h2>
                            12
                        </h2>

                    </div>

                </div>

            </div>

            <!-- EVENT REPORT -->

            <div class="report-card">

                <div class="report-title">

                    <h3>
                        Event Report
                    </h3>

                    <span class="badge-status status-success">
                        Updated Today
                    </span>

                </div>

                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Status</th>
                                <th>Participants</th>
                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td>1</td>

                                <td>
                                    Annual Conference 2026
                                </td>

                                <td>
                                    15 Sep 2026
                                </td>

                                <td>
                                    Pekan Hall
                                </td>

                                <td>

                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                </td>

                                <td>
                                    150
                                </td>

                                <td>

                                    <button class="action-btn btn-view">

                                        <i class="fa-solid fa-eye"></i>

                                    </button>

                                    <button class="action-btn btn-download">

                                        <i class="fa-solid fa-download"></i>

                                    </button>

                                </td>

                            </tr>

                            <tr>

                                <td>2</td>

                                <td>
                                    Leadership Workshop
                                </td>

                                <td>
                                    25 Aug 2026
                                </td>

                                <td>
                                    Main Hall
                                </td>

                                <td>

                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>

                                </td>

                                <td>
                                    85
                                </td>

                                <td>

                                    <button class="action-btn btn-view">

                                        <i class="fa-solid fa-eye"></i>

                                    </button>

                                    <button class="action-btn btn-download">

                                        <i class="fa-solid fa-download"></i>

                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>
        S</div>

    <!-- PARTICIPATION REPORT -->

    <div class="report-card">

        <div class="report-title">

            <h3>
                Participation Report
            </h3>

            <span class="badge-status status-warning">
                Monthly Overview
            </span>

        </div>

        <div class="table-responsive">

            <table class="table table-striped">

                <thead>

                    <tr>

                        <th>No</th>
                        <th>Student Name</th>
                        <th>Matric ID</th>
                        <th>Event Joined</th>
                        <th>Attendance</th>
                        <th>Certificate</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>1</td>

                        <td>
                            Muhammad Danish
                        </td>

                        <td>
                            CB22045
                        </td>

                        <td>
                            Annual Conference 2026
                        </td>

                        <td>

                            <span class="badge bg-success">
                                Present
                            </span>

                        </td>

                        <td>

                            <button class="btn btn-sm btn-outline-primary">

                                Download

                            </button>

                        </td>

                    </tr>

                    <tr>

                        <td>2</td>

                        <td>
                            Aisyah Humaira
                        </td>

                        <td>
                            CB22089
                        </td>

                        <td>
                            Leadership Workshop
                        </td>

                        <td>

                            <span class="badge bg-danger">
                                Absent
                            </span>

                        </td>

                        <td>

                            <button class="btn btn-sm btn-outline-secondary">

                                N/A

                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {

    const profileBtn = document.getElementById("profileButton");
    const profileDropdown = document.getElementById("profileDropdown");

    if (!profileBtn || !profileDropdown) {
        console.log("Profile dropdown elements not found");
        return;
    }

    profileBtn.addEventListener("click", function(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle("show");
    });

    document.addEventListener("click", function() {
        profileDropdown.classList.remove("show");
    });

    profileDropdown.addEventListener("click", function(e) {
        e.stopPropagation();
    });

});
</script>

<?php include('../Includes/footer.php'); ?>