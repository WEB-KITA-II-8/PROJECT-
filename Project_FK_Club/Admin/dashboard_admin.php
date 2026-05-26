<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Database connection
include '../db_connect.php';

// =============================================
// SAFE COUNT FUNCTION
// =============================================
function getCount($conn, $query) {

    $result = mysqli_query($conn, $query);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return isset($row['total'])
        ? (int)$row['total']
        : 0;
}

// =============================================
// FETCH DASHBOARD DATA
// =============================================
$total_students = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role='student'"
);

$total_clubs = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM clubs"
);

$total_committee = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role='committee'"
);

$recent_registrations = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE MONTH(created_at)=MONTH(CURRENT_DATE())
     AND YEAR(created_at)=YEAR(CURRENT_DATE())"
);

// Events count
$total_events = 0;

$check_events = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'events'"
);

if ($check_events && mysqli_num_rows($check_events) > 0) {

    $total_events = getCount(
        $conn,
        "SELECT COUNT(*) AS total FROM events"
    );
}

// =============================================
// USER INFO
// =============================================
$user_name = isset($_SESSION['full_name'])
    ? htmlspecialchars($_SESSION['full_name'])
    : 'Admin';

$user_role = 'Administrator';

?>

<title>

<?php echo isset($pageTitle)
? $pageTitle . ' | FK Student Club System'
: 'FK Student Club System'; ?>

</title>

<?php include '../Includes/header_admin.php'; ?>

<?php include '../Includes/sidebar_admin.php'; ?>

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
     MAIN CONTENT
============================================= -->

<div class="main-content">

<?php

$page = isset($_GET['page'])
    ? $_GET['page']
    : 'dashboard';

// =============================================
// LOAD CLUB PAGE
// =============================================
if($page == 'clubs'){

    include 'manage_clubs.php';

}

// =============================================
// DASHBOARD HOME
// =============================================
else{

?>

    <h1>
        Administrator Dashboard
    </h1>

    <!-- Summary Cards -->
    <div class="cards">

        <div class="card">

            <h3>
                Total Registered Students
            </h3>

            <p>
                <?php echo $total_students; ?>
            </p>

        </div>

        <div class="card">

            <h3>
                Active Clubs
            </h3>

            <p>
                <?php echo $total_clubs; ?>
            </p>

        </div>

        <div class="card">

            <h3>
                Upcoming Events
            </h3>

            <p>
                <?php echo $total_events; ?>
            </p>

        </div>

        <div class="card">

            <h3>
                Recent Registrations
            </h3>

            <p>
                <?php echo $recent_registrations; ?>
            </p>

        </div>

    </div>

    <!-- Charts -->
    <div class="charts-container">

        <div class="chart-box">

            <h2>
                Registrations (This Month)
            </h2>

            <canvas id="registrationChart"></canvas>

        </div>

        <div class="chart-box">

            <h2>
                User Roles Distribution
            </h2>

            <canvas id="roleChart"></canvas>

        </div>

        <div class="chart-box">

            <h2>
                Activity Events
            </h2>

            <canvas id="eventChart"></canvas>

        </div>

    </div>

<?php } ?>

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

<!-- =============================================
     CHART JS
============================================= -->

<script>

// Registration Chart
new Chart(
document.getElementById(
'registrationChart'
), {

    type: 'bar',

    data: {

        labels: ['This Month'],

        datasets: [{

            label: 'Registrations',

            data: [
                <?php echo $recent_registrations; ?>
            ],

            backgroundColor: '#2d5fd3'

        }]
    },

    options: {

        responsive: true,
        maintainAspectRatio: false

    }
});

// User Role Chart
new Chart(
document.getElementById(
'roleChart'
), {

    type: 'pie',

    data: {

        labels: [
            'Students',
            'Committee',
            'Admin'
        ],

        datasets: [{

            data: [

                <?php echo $total_students; ?>,

                <?php echo $total_committee; ?>,

                1
            ],

            backgroundColor: [

                '#2d5fd3',
                '#22c55e',
                '#f59e0b'

            ]

        }]
    },

    options: {

        responsive: true,
        maintainAspectRatio: false

    }
});

// Events Chart
new Chart(
document.getElementById(
'eventChart'
), {

    type: 'line',

    data: {

        labels: ['Upcoming'],

        datasets: [{

            label: 'Events',

            data: [
                <?php echo $total_events; ?>
            ],

            borderColor: '#ef4444',

            backgroundColor:
            'rgba(239,68,68,0.15)',

            fill: true,
            tension: 0.3

        }]
    },

    options: {

        responsive: true,
        maintainAspectRatio: false

    }
});

</script>

<?php include '../Includes/footer.php'; ?>