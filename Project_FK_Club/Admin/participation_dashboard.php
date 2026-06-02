<?php
// =============================================
// ATTENDANCE & PARTICIPATION DASHBOARD
// FILE: participation_dashboard.php
// =============================================

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include '../db_connect.php';

// =============================================
// SECURITY CHECK - Only allow authorized users
// =============================================
//if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'committee'])) {
    //header("Location: index.php");
   // exit();
//}

// =============================================
// USER SESSION DATA
// =============================================
$user_id = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'admin';

// =============================================
// FETCH SUMMARY DATA
// =============================================
// Check if events table exists
$check_events = mysqli_query($conn, "SHOW TABLES LIKE 'events'");
$has_events = $check_events && mysqli_num_rows($check_events) > 0;

// Events Conducted
$events_conducted = 0;
if ($has_events) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM events");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $events_conducted = $row['count'] ?? 0;
    }
}

// Total Participations (from attendance records if available)
$total_participations = 0;
$check_attendance = mysqli_query($conn, "SHOW TABLES LIKE 'attendance'");
if ($check_attendance && mysqli_num_rows($check_attendance) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE status='present'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_participations = $row['count'] ?? 0;
    }
}

// Average Attendance Rate
$avg_attendance_rate = 82; // Default value, can be calculated from database

// Active Students
$active_students = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='student'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $active_students = $row['count'] ?? 0;
}

// =============================================
// FETCH MOST ACTIVE CLUBS (Sample Data)
// =============================================
$clubs_data = [
    ['name' => 'Tech Club', 'events' => 5, 'color' => '#3b82f6'],
    ['name' => 'PMUKM', 'events' => 4, 'color' => '#10b981'],
    ['name' => 'Sports Club', 'events' => 3, 'color' => '#f59e0b'],
];

// =============================================
// FETCH MOST ACTIVE STUDENTS (Sample Data)
// =============================================
$students_data = [
    ['name' => 'Siti Aisyah', 'points' => 95, 'status' => 'Outstanding'],
    ['name' => 'Ahmad Faris', 'points' => 72, 'status' => 'Active'],
    ['name' => 'Nur Izzah', 'points' => 45, 'status' => 'Eligible'],
];

// =============================================
// FILTER HANDLING
// =============================================
$filter_club = $_GET['club'] ?? 'all';
$filter_semester = $_GET['semester'] ?? 'all';
$filter_event_type = $_GET['event_type'] ?? 'all';

?>

<title>Attendance & Participation Dashboard</title>

<?php include('../Includes/header_admin.php'); ?>
<?php include('../Includes/sidebar_admin.php'); ?>

<!-- =============================================
MAIN CONTENT
============================================= -->
<div class="main-content">

    <!-- =============================================
    PAGE HEADER
    ============================================= -->
    <div class="page-header">
        <div>
            <h1>Attendance & Participation Dashboard</h1>
            <p>Real-time overview of student engagement</p>
        </div>
    </div>

    <!-- =============================================
    FILTER SECTION
    ============================================= -->
    <div class="filter-section">
        <p class="filter-info">
            <i class="fa-solid fa-info-circle"></i>
            Dashboard displays summarized statistics. Filter by club, semester or event type using the controls below.
        </p>

        <div class="filter-controls">
            <select id="filterClub" class="filter-select">
                <option value="all">All Clubs</option>
                <option value="tech">Tech Club</option>
                <option value="pmukm">PMUKM</option>
                <option value="sports">Sports Club</option>
            </select>

            <select id="filterSemester" class="filter-select">
                <option value="all">All Semesters</option>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
            </select>

            <select id="filterEventType" class="filter-select">
                <option value="all">All Event Types</option>
                <option value="workshop">Workshop</option>
                <option value="seminar">Seminar</option>
                <option value="meeting">Meeting</option>
            </select>

            <button class="apply-filter-btn" onclick="applyFilters()">
                <i class="fa-solid fa-check"></i> Apply Filter
            </button>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="navigation-cards">

        <a href="participant_points.php" class="nav-card">
            <div class="nav-card-icon">
                <i class="fa-solid fa-crown"></i>
            </div>
            <h3>Participation Points & Ranking</h3>
            <p>View student points, rankings, and recognition levels</p>
            <span class="nav-arrow"><i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="manage_event_attendance.php" class="nav-card">
            <div class="nav-card-icon">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <h3>Manage Event Attendance</h3>
            <p>Record and manage student attendance with QR codes</p>
            <span class="nav-arrow"><i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="event_reports.php" class="nav-card">
            <div class="nav-card-icon">
                <i class="fa-solid fa-chart-bar"></i>
            </div>
            <h3>Event Reports & Statistics</h3>
            <p>View comprehensive participation trends and reports</p>
            <span class="nav-arrow"><i class="fa-solid fa-arrow-right"></i></span>
        </a>

    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="card-icon" style="background: #dbeafe;">
                <i class="fa-solid fa-calendar-check" style="color: #3b82f6;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $events_conducted; ?></h3>
                <p>Events Conducted</p>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon" style="background: #d1fae5;">
                <i class="fa-solid fa-hand-fist" style="color: #10b981;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $total_participations; ?></h3>
                <p>Total Participations</p>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon" style="background: #fed7aa;">
                <i class="fa-solid fa-percent" style="color: #f59e0b;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $avg_attendance_rate; ?>%</h3>
                <p>Avg Attendance Rate</p>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon" style="background: #e9d5ff;">
                <i class="fa-solid fa-users" style="color: #a855f7;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $active_students; ?></h3>
                <p>Active Students</p>
            </div>
        </div>

    </div>

    <!-- =============================================
    CHARTS SECTION
    ============================================= -->
    <div class="charts-section">

        <!-- Most Active Clubs -->
        <div class="chart-card">
            <h2>
                <i class="fa-solid fa-layer-group"></i>
                Most Active Clubs
            </h2>
            <div class="clubs-chart">
                <?php foreach ($clubs_data as $club): ?>
                    <div class="club-item">
                        <div class="club-header">
                            <span class="club-name"><?php echo $club['name']; ?></span>
                            <span class="club-count"><?php echo $club['events']; ?> events</span>
                        </div>
                        <div class="club-bar-container">
                            <div class="club-bar" style="width: <?php echo ($club['events'] / 5) * 100; ?>%; background: linear-gradient(90deg, <?php echo $club['color']; ?>, <?php echo $club['color']; ?>dd); box-shadow: 0 4px 12px rgba(0,0,0,0.18); border-radius: 18px;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Most Active Students -->
        <div class="chart-card">
            <h2>
                <i class="fa-solid fa-star"></i>
                Most Active Students
            </h2>
            <div class="students-list">
                <?php foreach ($students_data as $student): ?>
                    <div class="student-item">
                        <div class="student-info">
                            <h4><?php echo $student['name']; ?></h4>
                            <span class="student-points"><?php echo $student['points']; ?> pts</span>
                        </div>
                        <span class="student-status" style="background: <?php 
                            if ($student['status'] === 'Outstanding') echo '#dcfce7;'; 
                            elseif ($student['status'] === 'Active') echo '#dbeafe;'; 
                            else echo '#fef3c7;'; 
                        ?>; color: <?php 
                            if ($student['status'] === 'Outstanding') echo '#15803d;'; 
                            elseif ($student['status'] === 'Active') echo '#0369a1;'; 
                            else echo '#92400e;'; 
                        ?>;">
                            <?php echo $student['status']; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<!-- =============================================
SCRIPTS
============================================= -->
<script>

// Apply filters
function applyFilters() {
    const club = document.getElementById('filterClub').value;
    const semester = document.getElementById('filterSemester').value;
    const eventType = document.getElementById('filterEventType').value;

    // Build query string
    let params = new URLSearchParams();
    if (club !== 'all') params.append('club', club);
    if (semester !== 'all') params.append('semester', semester);
    if (eventType !== 'all') params.append('event_type', eventType);

    // Redirect with filters
    window.location.href = 'participation_dashboard.php?' + params.toString();
}
</script>

<?php include('../Includes/footer.php'); ?>