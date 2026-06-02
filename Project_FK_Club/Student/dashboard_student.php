<?php
// =============================================
// STUDENT DASHBOARD FINAL REPAIRED
// FILE: student/dashboard_student.php
// =============================================

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =============================================
// DATABASE CONNECTION
// =============================================
include '../db_connect.php';

// =============================================
// USER SESSION DATA
// =============================================
$user_id = $_SESSION['user_id'];
$student_name = $_SESSION['full_name'] ?? 'Student';

// =============================================
// SUMMARY DATA
// =============================================

// Total clubs joined by student
$total_clubs_result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM memberships
    WHERE user_id = '$user_id'
");
$total_clubs = mysqli_fetch_assoc($total_clubs_result)['total'] ?? 0;

// Total registered events
$total_registered_events_result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM events
");
$total_registered_events = mysqli_fetch_assoc($total_registered_events_result)['total'] ?? 0;

// Upcoming events count
$upcoming_events_result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM events
    WHERE event_date >= CURDATE()
");
$upcoming_events = mysqli_fetch_assoc($upcoming_events_result)['total'] ?? 0;

// Participation points system
$participation_points = ($total_clubs * 10) + ($total_registered_events * 5);

// =============================================
// MEMBERSHIP OVERVIEW
// =============================================
$membership_query = mysqli_query($conn, "
    SELECT clubs.club_name,
           memberships.membership_type,
           memberships.joined_date
    FROM memberships
    INNER JOIN clubs ON memberships.club_id = clubs.club_id
    WHERE memberships.user_id = '$user_id'
    ORDER BY memberships.joined_date DESC
    LIMIT 5
");

// =============================================
// UPCOMING EVENTS
// =============================================
$event_query = mysqli_query($conn, "
    SELECT event_name,
           event_date,
           event_location
    FROM events
    WHERE event_date >= CURDATE()
    ORDER BY event_date ASC
    LIMIT 5
");

// =============================================
// PARTICIPATION HISTORY
// =============================================
$participation_query = mysqli_query($conn, "
    SELECT event_name,
           event_date,
           event_location
    FROM events
    ORDER BY event_date DESC
    LIMIT 5
");
?>

<title>Student Dashboard</title>

<?php include('../Includes/header_stud.php'); ?>
<?php include('../Includes/sidebar_stud.php'); ?>

<!-- =============================================
MAIN CONTENT
============================================= -->
<div class="main-content">

    <h1>Registered Student Dashboard</h1>

    <!-- =============================================
    SUMMARY CARDS
    ============================================= -->
    <div class="cards">

        <div class="card">
            <h3>Total Clubs Joined</h3>
            <p><?php echo $total_clubs; ?></p>
        </div>

        <div class="card">
            <h3>Registered Events</h3>
            <p><?php echo $total_registered_events; ?></p>
        </div>

        <div class="card">
            <h3>Upcoming Events</h3>
            <p><?php echo $upcoming_events; ?></p>
        </div>

        <div class="card">
            <h3>Participation Points</h3>
            <p><?php echo $participation_points; ?></p>
        </div>

    </div>

    <!-- =============================================
    CHART SECTION
    ============================================= -->
    <div class="charts-section">

        <!-- Club Overview -->
        <div class="chart-card">
            <h2>Club Membership Overview</h2>
            <canvas id="clubChart"></canvas>
        </div>

        <!-- Participation Progress -->
        <div class="chart-card">
            <h2>Participation Progress</h2>
            <canvas id="pointsChart"></canvas>
        </div>

    </div>

    <!-- =============================================
    MAIN STUDENT MODULE SECTION
    ============================================= -->
    <div class="student-sections">

        <!-- MEMBERSHIP OVERVIEW -->
        <div class="dashboard-card">

            <div class="card-header">
                <h2>My Membership Overview</h2>
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Club Name</th>
                        <th>Type</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                if (mysqli_num_rows($membership_query) > 0) {
                    while ($membership = mysqli_fetch_assoc($membership_query)) {
                        echo "<tr>
                                <td>{$membership['club_name']}</td>
                                <td>{$membership['membership_type']}</td>
                                <td>{$membership['joined_date']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No memberships found</td></tr>";
                }
                ?>
                </tbody>
            </table>

        </div>

        <!-- UPCOMING EVENTS -->
        <div class="dashboard-card">

            <div class="card-header">
                <h2>Upcoming Events</h2>
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Venue</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                if (mysqli_num_rows($event_query) > 0) {
                    while ($event = mysqli_fetch_assoc($event_query)) {
                        echo "<tr>
                                <td>{$event['event_name']}</td>
                                <td>{$event['event_date']}</td>
                                <td>{$event['event_location']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No upcoming events</td></tr>";
                }
                ?>
                </tbody>
            </table>

        </div>

        <!-- PARTICIPATION HISTORY -->
        <div class="dashboard-card">

            <div class="card-header">
                <h2>Participation History</h2>
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Venue</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                if (mysqli_num_rows($participation_query) > 0) {
                    while ($participation = mysqli_fetch_assoc($participation_query)) {
                        echo "<tr>
                                <td>{$participation['event_name']}</td>
                                <td>{$participation['event_date']}</td>
                                <td>{$participation['event_location']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No participation history</td></tr>";
                }
                ?>
                </tbody>
            </table>

        </div>

    </div>

</div>

<!-- =============================================
JAVASCRIPT SECTION
============================================= -->
<script>
// =============================================
// PROFILE DROPDOWN TOGGLE (Direct Approach)
// =============================================
function toggleDropdown() {
    const dropdown = document.getElementById("profileDropdown");
    dropdown.classList.toggle("show");
}


// Close profile dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.closest('.profile-menu')) {
        const profileDropdown = document.getElementById("profileDropdown");
        if (profileDropdown) {
            profileDropdown.classList.remove("show");
        }
    }
};

// =============================================
// SAFE PHP VALUES FOR CHARTS
// =============================================
const totalClubs = <?php echo intval(max(1, $total_clubs)); ?>;
const participationPoints = <?php echo intval(max(1, $participation_points)); ?>;
const remainingPoints = <?php echo intval(max(1, 150 - $participation_points)); ?>;

// =============================================
// CLUB MEMBERSHIP BAR CHART
// =============================================
new Chart(document.getElementById('clubChart'), {
    type: 'bar',
    data: {
        labels: ['Clubs Joined'],
        datasets: [{
            label: 'Clubs',
            data: [totalClubs],
            backgroundColor: '#2d5fd3',
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// =============================================
// PARTICIPATION PIE CHART
// =============================================
new Chart(document.getElementById('pointsChart'), {
    type: 'pie',
    data: {
        labels: ['Points Earned', 'Remaining'],
        datasets: [{
            data: [participationPoints, remainingPoints],
            backgroundColor: ['#1d4ed8', '#f59e0b'],
            borderColor: ['#ffffff', '#ffffff'],
            borderWidth: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

</body>
</html>