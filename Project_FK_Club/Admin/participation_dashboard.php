<?php
// =============================================
// ATTENDANCE & PARTICIPATION DASHBOARD
// FILE: Admin/participation_dashboard.php
// MODULE 4 - Manage Event Attendance & Reports
// =============================================

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db_connect.php';

// =============================================
// SECURITY CHECK
// =============================================
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'committee'])) {
    header("Location: ../index.php");
    exit();
}

$user_id   = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'admin';

// =============================================
// FILTER HANDLING
// =============================================
$filter_club  = isset($_GET['club'])  ? mysqli_real_escape_string($conn, $_GET['club'])  : 'all';
$filter_month = isset($_GET['month']) ? mysqli_real_escape_string($conn, $_GET['month']) : 'all';

// Build WHERE clauses
$where_club  = ($filter_club  !== 'all') ? "AND ea.club_name = '$filter_club'"  : '';
$where_month = ($filter_month !== 'all') ? "AND DATE_FORMAT(ea.attendance_date,'%Y-%m') = '$filter_month'" : '';

// =============================================
// SUMMARY STATISTICS  (from event_attendance)
// =============================================

// Total events conducted (distinct event names)
$r = mysqli_query($conn, "SELECT COUNT(DISTINCT event_name) AS cnt FROM event_attendance WHERE 1=1 $where_month");
$events_conducted = mysqli_fetch_assoc($r)['cnt'] ?? 0;

// Total participation records
$r = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM event_attendance WHERE 1=1 $where_club $where_month");
$total_participations = mysqli_fetch_assoc($r)['cnt'] ?? 0;

// Average attendance rate  (present+volunteer / total * 100)
$r = mysqli_query($conn, "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN attendance_status IN ('present','volunteer') THEN 1 ELSE 0 END) AS attended
    FROM event_attendance WHERE 1=1 $where_club $where_month");
$row = mysqli_fetch_assoc($r);
$avg_attendance_rate = ($row['total'] > 0) ? round(($row['attended'] / $row['total']) * 100, 1) : 0;

// Active students (students who have at least one attendance record)
$r = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) AS cnt FROM event_attendance WHERE 1=1 $where_club $where_month");
$active_students = mysqli_fetch_assoc($r)['cnt'] ?? 0;

// =============================================
// MOST ACTIVE CLUBS  (by attendance records)
// =============================================
$clubs_result = mysqli_query($conn,
    "SELECT club_name, COUNT(*) AS total_records
     FROM event_attendance
     WHERE club_name IS NOT NULL AND club_name != 'N/A'
     $where_month
     GROUP BY club_name
     ORDER BY total_records DESC
     LIMIT 7"
);
$clubs_data = [];
while ($row = mysqli_fetch_assoc($clubs_result)) {
    $clubs_data[] = $row;
}
$club_labels  = json_encode(array_column($clubs_data, 'club_name'));
$club_totals  = json_encode(array_column($clubs_data, 'total_records'));

// All club names for filter dropdown
$all_clubs_result = mysqli_query($conn,
    "SELECT DISTINCT club_name FROM event_attendance WHERE club_name != 'N/A' ORDER BY club_name"
);

// =============================================
// MOST ACTIVE STUDENTS  (by total points)
// =============================================
$top_students_result = mysqli_query($conn,
    "SELECT
        u.full_name,
        u.student_id,
        SUM(ea.points_awarded) AS total_points,
        COUNT(ea.attendance_id) AS events_count
     FROM event_attendance ea
     JOIN users u ON ea.user_id = u.user_id
     WHERE 1=1 $where_club $where_month
     GROUP BY ea.user_id
     ORDER BY total_points DESC
     LIMIT 6"
);
$top_students = [];
while ($row = mysqli_fetch_assoc($top_students_result)) {
    $row['recognition'] = getRecognitionLevel($row['total_points']);
    $top_students[] = $row;
}

// =============================================
// MONTHLY ATTENDANCE TREND (last 6 months)
// =============================================
$trend_result = mysqli_query($conn,
    "SELECT
        DATE_FORMAT(attendance_date,'%b %Y') AS month_label,
        DATE_FORMAT(attendance_date,'%Y-%m') AS month_key,
        COUNT(*) AS total,
        SUM(CASE WHEN attendance_status IN ('present','volunteer') THEN 1 ELSE 0 END) AS attended
     FROM event_attendance
     WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     $where_club
     GROUP BY month_key, month_label
     ORDER BY month_key ASC"
);
$trend_labels  = [];
$trend_totals  = [];
$trend_present = [];
while ($row = mysqli_fetch_assoc($trend_result)) {
    $trend_labels[]  = $row['month_label'];
    $trend_totals[]  = (int)$row['total'];
    $trend_present[] = (int)$row['attended'];
}
$trend_labels_json  = json_encode($trend_labels);
$trend_totals_json  = json_encode($trend_totals);
$trend_present_json = json_encode($trend_present);

// =============================================
// ATTENDANCE STATUS BREAKDOWN (Pie)
// =============================================
$pie_result = mysqli_query($conn,
    "SELECT attendance_status, COUNT(*) AS cnt
     FROM event_attendance
     WHERE 1=1 $where_club $where_month
     GROUP BY attendance_status"
);
$pie_labels = []; $pie_data = []; $pie_colors = [];
$status_colors = [
    'present'   => '#10b981',
    'late'      => '#f59e0b',
    'absent'    => '#ef4444',
    'volunteer' => '#3b82f6',
];
while ($row = mysqli_fetch_assoc($pie_result)) {
    $pie_labels[] = ucfirst($row['attendance_status']);
    $pie_data[]   = (int)$row['cnt'];
    $pie_colors[] = $status_colors[$row['attendance_status']] ?? '#64748b';
}
$pie_labels_json = json_encode($pie_labels);
$pie_data_json   = json_encode($pie_data);
$pie_colors_json = json_encode($pie_colors);

// =============================================
// RECOGNITION LEVEL FUNCTION
// =============================================
function getRecognitionLevel($points) {
    if ($points >= 80)     return ['label' => 'Outstanding',    'color' => '#f59e0b'];
    elseif ($points >= 50) return ['label' => 'Active Student', 'color' => '#10b981'];
    elseif ($points >= 20) return ['label' => 'Eligible',       'color' => '#3b82f6'];
    else                   return ['label' => 'Warning',        'color' => '#ef4444'];
}

// =============================================
// POINTS DISTRIBUTION (for bar chart)
// =============================================
$dist_result = mysqli_query($conn,
    "SELECT
        SUM(CASE WHEN total_pts < 20               THEN 1 ELSE 0 END) AS warning,
        SUM(CASE WHEN total_pts BETWEEN 20 AND 49  THEN 1 ELSE 0 END) AS eligible,
        SUM(CASE WHEN total_pts BETWEEN 50 AND 79  THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN total_pts >= 80              THEN 1 ELSE 0 END) AS outstanding
     FROM (
        SELECT user_id, SUM(points_awarded) AS total_pts
        FROM event_attendance $where_club GROUP BY user_id
     ) sub"
);
$dist = mysqli_fetch_assoc($dist_result);
$dist_data_json = json_encode([
    (int)($dist['warning']     ?? 0),
    (int)($dist['eligible']    ?? 0),
    (int)($dist['active']      ?? 0),
    (int)($dist['outstanding'] ?? 0),
]);

// Month options for filter
$months_result = mysqli_query($conn,
    "SELECT DISTINCT DATE_FORMAT(attendance_date,'%Y-%m') AS m,
                     DATE_FORMAT(attendance_date,'%b %Y') AS label
     FROM event_attendance ORDER BY m DESC LIMIT 12"
);
?>

<title>Attendance & Participation Dashboard</title>

<?php include('../Includes/header_admin.php'); ?>
<?php include('../Includes/sidebar_admin.php'); ?>

<div class="main-content">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-chart-pie" style="color:#3b82f6;"></i> Attendance & Participation Dashboard</h1>
            <p>Real-time overview of student engagement and event attendance</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="manage_event_attendance.php" class="btn-primary">
                <i class="fa-solid fa-clipboard-check"></i> Manage Attendance
            </a>
            <a href="event_reports.php" class="btn-secondary">
                <i class="fa-solid fa-file-lines"></i> View Reports
            </a>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-section">
        <form method="GET" class="filter-controls" id="filterForm">
            <div class="filter-group">
                <label><i class="fa-solid fa-layer-group"></i> Club</label>
                <select name="club" class="filter-select" onchange="this.form.submit()">
                    <option value="all">All Clubs</option>
                    <?php while ($c = mysqli_fetch_assoc($all_clubs_result)): ?>
                        <option value="<?php echo htmlspecialchars($c['club_name']); ?>"
                            <?php echo ($filter_club === $c['club_name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['club_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fa-solid fa-calendar"></i> Month</label>
                <select name="month" class="filter-select" onchange="this.form.submit()">
                    <option value="all">All Time</option>
                    <?php
                    mysqli_data_seek($months_result, 0);
                    while ($m = mysqli_fetch_assoc($months_result)):
                    ?>
                        <option value="<?php echo $m['m']; ?>"
                            <?php echo ($filter_month === $m['m']) ? 'selected' : ''; ?>>
                            <?php echo $m['label']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php if ($filter_club !== 'all' || $filter_month !== 'all'): ?>
                <a href="participation_dashboard.php" class="btn-clear">
                    <i class="fa-solid fa-xmark"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- QUICK NAVIGATION CARDS -->
    <div class="navigation-cards">
        <a href="manage_event_attendance.php" class="nav-card">
            <div class="nav-card-icon" style="background:#dbeafe;">
                <i class="fa-solid fa-clipboard-check" style="color:#3b82f6;"></i>
            </div>
            <h3>Manage Attendance</h3>
            <p>Record, edit & delete attendance records</p>
            <span class="nav-arrow"><i class="fa-solid fa-arrow-right"></i></span>
        </a>
        <a href="participant_points.php" class="nav-card">
            <div class="nav-card-icon" style="background:#fef3c7;">
                <i class="fa-solid fa-crown" style="color:#f59e0b;"></i>
            </div>
            <h3>Points & Rankings</h3>
            <p>Student recognition levels & leaderboard</p>
            <span class="nav-arrow"><i class="fa-solid fa-arrow-right"></i></span>
        </a>
        <a href="event_reports.php" class="nav-card">
            <div class="nav-card-icon" style="background:#d1fae5;">
                <i class="fa-solid fa-chart-bar" style="color:#10b981;"></i>
            </div>
            <h3>Reports & Statistics</h3>
            <p>Comprehensive event participation reports</p>
            <span class="nav-arrow"><i class="fa-solid fa-arrow-right"></i></span>
        </a>
    </div>

    <!-- SUMMARY STAT CARDS -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="card-icon" style="background:#dbeafe;">
                <i class="fa-solid fa-calendar-check" style="color:#3b82f6;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $events_conducted; ?></h3>
                <p>Events Conducted</p>
            </div>
        </div>
        <div class="summary-card">
            <div class="card-icon" style="background:#d1fae5;">
                <i class="fa-solid fa-hand-fist" style="color:#10b981;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $total_participations; ?></h3>
                <p>Total Participations</p>
            </div>
        </div>
        <div class="summary-card">
            <div class="card-icon" style="background:#fed7aa;">
                <i class="fa-solid fa-percent" style="color:#f59e0b;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $avg_attendance_rate; ?>%</h3>
                <p>Attendance Rate</p>
            </div>
        </div>
        <div class="summary-card">
            <div class="card-icon" style="background:#e9d5ff;">
                <i class="fa-solid fa-users" style="color:#a855f7;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $active_students; ?></h3>
                <p>Active Students</p>
            </div>
        </div>
    </div>

    <!-- CHARTS ROW 1 -->
    <div class="charts-section">

        <!-- Monthly Trend Line Chart -->
        <div class="chart-card chart-large">
            <h2><i class="fa-solid fa-chart-line"></i> Monthly Attendance Trend</h2>
            <canvas id="trendChart" height="110"></canvas>
        </div>

        <!-- Status Breakdown Pie -->
        <div class="chart-card chart-small">
            <h2><i class="fa-solid fa-circle-half-stroke"></i> Status Breakdown</h2>
            <canvas id="pieChart" height="200"></canvas>
            <div class="pie-legend" id="pieLegend"></div>
        </div>

    </div>

    <!-- CHARTS ROW 2 -->
    <div class="charts-section">

        <!-- Most Active Clubs Bar -->
        <div class="chart-card chart-medium">
            <h2><i class="fa-solid fa-layer-group"></i> Most Active Clubs</h2>
            <?php if (empty($clubs_data)): ?>
                <div class="empty-state"><i class="fa-solid fa-chart-bar"></i><p>No club data yet</p></div>
            <?php else: ?>
                <canvas id="clubsChart" height="180"></canvas>
            <?php endif; ?>
        </div>

        <!-- Points Distribution -->
        <div class="chart-card chart-medium">
            <h2><i class="fa-solid fa-trophy"></i> Recognition Level Distribution</h2>
            <canvas id="distChart" height="180"></canvas>
        </div>

    </div>

    <!-- TOP STUDENTS TABLE -->
    <div class="chart-card" style="margin-top:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h2><i class="fa-solid fa-star"></i> Top Students by Points</h2>
            <a href="participant_points.php" style="font-size:14px;color:#3b82f6;text-decoration:none;">
                View All <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <?php if (empty($top_students)): ?>
            <div class="empty-state"><i class="fa-solid fa-users"></i><p>No student data yet</p></div>
        <?php else: ?>
        <div class="table-container">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Events</th>
                        <th>Total Points</th>
                        <th>Recognition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_students as $i => $s):
                        $rec = $s['recognition'];
                    ?>
                    <tr>
                        <td><strong><?php echo $i + 1; ?></strong></td>
                        <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($s['student_id'] ?? '-'); ?></td>
                        <td><?php echo $s['events_count']; ?></td>
                        <td><strong style="color:<?php echo $rec['color']; ?>;"><?php echo $s['total_points']; ?> pts</strong></td>
                        <td>
                            <span class="recognition-badge"
                                  style="background:<?php echo $rec['color']; ?>20;color:<?php echo $rec['color']; ?>;">
                                <?php echo $rec['label']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- end main-content -->

<!-- CHART JS SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ---- Monthly Trend ----
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?php echo $trend_labels_json; ?>,
        datasets: [
            {
                label: 'Total Records',
                data: <?php echo $trend_totals_json; ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3b82f6',
                pointRadius: 5
            },
            {
                label: 'Present / Volunteer',
                data: <?php echo $trend_present_json; ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointRadius: 5
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// ---- Status Pie ----
const pieCtx = document.getElementById('pieChart').getContext('2d');
const pieLabels = <?php echo $pie_labels_json; ?>;
const pieData   = <?php echo $pie_data_json; ?>;
const pieColors = <?php echo $pie_colors_json; ?>;

new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: pieLabels,
        datasets: [{ data: pieData, backgroundColor: pieColors, borderWidth: 2 }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

// Build pie legend
const legendEl = document.getElementById('pieLegend');
pieLabels.forEach((l, i) => {
    legendEl.innerHTML += `<div class="legend-item">
        <span style="width:12px;height:12px;border-radius:50%;background:${pieColors[i]};display:inline-block;margin-right:6px;"></span>
        ${l}: <strong>${pieData[i]}</strong>
    </div>`;
});

// ---- Clubs Bar ----
<?php if (!empty($clubs_data)): ?>
const clubsCtx = document.getElementById('clubsChart').getContext('2d');
new Chart(clubsCtx, {
    type: 'bar',
    data: {
        labels: <?php echo $club_labels; ?>,
        datasets: [{
            label: 'Attendance Records',
            data: <?php echo $club_totals; ?>,
            backgroundColor: ['#3b82f6','#10b981','#f59e0b','#a855f7','#ef4444','#06b6d4','#84cc16'],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
<?php endif; ?>

// ---- Points Distribution ----
const distCtx = document.getElementById('distChart').getContext('2d');
new Chart(distCtx, {
    type: 'bar',
    data: {
        labels: ['Warning\n(<20)', 'Eligible\n(20-49)', 'Active\n(50-79)', 'Outstanding\n(80+)'],
        datasets: [{
            label: 'Students',
            data: <?php echo $dist_data_json; ?>,
            backgroundColor: ['#ef4444','#3b82f6','#10b981','#f59e0b'],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>

<!-- Extra inline styles for dashboard-specific elements -->
<style>
.btn-primary {
    display:inline-flex;align-items:center;gap:8px;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8);
    color:#fff;padding:10px 18px;border-radius:12px;
    text-decoration:none;font-size:14px;font-weight:600;
    transition:all .2s;
}
.btn-primary:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(59,130,246,.4); }
.btn-secondary {
    display:inline-flex;align-items:center;gap:8px;
    background:#fff;color:#374151;border:1.5px solid #e2e8f0;
    padding:10px 18px;border-radius:12px;text-decoration:none;
    font-size:14px;font-weight:600;transition:all .2s;
}
.btn-secondary:hover { border-color:#3b82f6;color:#3b82f6; }
.btn-clear {
    display:inline-flex;align-items:center;gap:6px;
    padding:10px 14px;border-radius:12px;background:#fee2e2;
    color:#dc2626;text-decoration:none;font-size:14px;font-weight:600;
}
.filter-section { background:#fff;border-radius:16px;padding:18px 24px;margin-bottom:24px;
    box-shadow:0 2px 10px rgba(0,0,0,.05); }
.filter-controls { display:flex;align-items:flex-end;gap:16px;flex-wrap:wrap; }
.filter-group { display:flex;flex-direction:column;gap:4px; }
.filter-group label { font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase; }
.filter-select { padding:8px 14px;border:1.5px solid #e2e8f0;border-radius:10px;
    font-size:14px;color:#1e293b;background:#f8fafc;cursor:pointer; }
.navigation-cards { display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:24px; }
.nav-card { background:#fff;border-radius:16px;padding:22px;display:flex;flex-direction:column;gap:8px;
    text-decoration:none;color:#1e293b;box-shadow:0 2px 10px rgba(0,0,0,.05);
    transition:all .25s;border:1.5px solid transparent; }
.nav-card:hover { border-color:#3b82f6;transform:translateY(-2px);box-shadow:0 6px 20px rgba(59,130,246,.15); }
.nav-card-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;
    justify-content:center;font-size:20px; }
.nav-card h3 { font-size:15px;font-weight:700;margin:0; }
.nav-card p { font-size:13px;color:#64748b;margin:0; }
.nav-arrow { margin-top:auto;color:#3b82f6;font-size:13px; }
.summary-cards { display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:24px; }
.summary-card { background:#fff;border-radius:16px;padding:22px;display:flex;align-items:center;
    gap:16px;box-shadow:0 2px 10px rgba(0,0,0,.05); }
.card-icon { width:50px;height:50px;border-radius:14px;display:flex;align-items:center;
    justify-content:center;font-size:22px; }
.card-content h3 { font-size:28px;font-weight:800;color:#1e293b;margin:0; }
.card-content p { font-size:13px;color:#64748b;margin:0;margin-top:2px; }
.charts-section { display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px; }
.chart-card { background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.05); }
.chart-card h2 { font-size:16px;font-weight:700;color:#1e293b;margin-bottom:18px;
    display:flex;align-items:center;gap:10px; }
.chart-large { grid-column:span 2; }
.chart-small { grid-column:span 1; }
.chart-medium { grid-column:span 1; }
.pie-legend { display:flex;flex-wrap:wrap;gap:8px;margin-top:14px; }
.legend-item { font-size:13px;color:#374151;display:flex;align-items:center; }
.empty-state { text-align:center;padding:40px;color:#94a3b8; }
.empty-state i { font-size:40px;margin-bottom:12px;display:block; }
.table-container { overflow-x:auto; }
.students-table { width:100%;border-collapse:collapse;font-size:14px; }
.students-table thead th { background:#f8fafc;padding:12px 16px;text-align:left;
    font-weight:700;color:#374151;font-size:13px; }
.students-table tbody td { padding:12px 16px;border-bottom:1px solid #f1f5f9;color:#374151; }
.students-table tbody tr:hover { background:#f8fafc; }
.recognition-badge { padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600; }
.page-header { display:flex;justify-content:space-between;align-items:center;
    margin-bottom:24px;flex-wrap:wrap;gap:12px; }
.page-header h1 { font-size:24px;font-weight:800;color:#1e293b;margin:0; }
.page-header p { font-size:14px;color:#64748b;margin:4px 0 0; }
@media(max-width:900px){
    .summary-cards,.navigation-cards,.charts-section{grid-template-columns:1fr 1fr;}
    .chart-large,.chart-small,.chart-medium{grid-column:span 2;}
}
@media(max-width:600px){
    .summary-cards,.navigation-cards{grid-template-columns:1fr;}
}
</style>

<?php include('../Includes/footer.php'); ?>
