<?php
// =============================================
// EVENT REPORTS & STATISTICS
// FILE: Admin/event_reports.php
// MODULE 4 - Reports (Single Table + Join Table)
// =============================================

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db_connect.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'committee'])) {
    header("Location: ../index.php");
    exit();
}

$user_id   = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'admin';

// Active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'attendance';

// =============================================
// REPORT 1 (SINGLE TABLE): Attendance Summary
// per event_name from event_attendance only
// =============================================
$single_table_result = mysqli_query($conn, "
    SELECT
        event_name,
        attendance_date,
        COUNT(*) AS total_records,
        SUM(CASE WHEN attendance_status = 'present'   THEN 1 ELSE 0 END) AS present_count,
        SUM(CASE WHEN attendance_status = 'late'      THEN 1 ELSE 0 END) AS late_count,
        SUM(CASE WHEN attendance_status = 'absent'    THEN 1 ELSE 0 END) AS absent_count,
        SUM(CASE WHEN attendance_status = 'volunteer' THEN 1 ELSE 0 END) AS volunteer_count,
        SUM(CASE WHEN attendance_status IN ('present','volunteer','late') THEN 1 ELSE 0 END) AS attended_count,
        ROUND(
            SUM(CASE WHEN attendance_status IN ('present','volunteer') THEN 1 ELSE 0 END)
            / COUNT(*) * 100, 1
        ) AS attendance_rate,
        SUM(points_awarded) AS total_points_awarded
    FROM event_attendance
    GROUP BY event_name, attendance_date
    ORDER BY attendance_date DESC
");

// =============================================
// REPORT 2 (JOIN TABLE): Student Participation
// JOIN event_attendance + users + memberships + clubs
// =============================================
$join_table_result = mysqli_query($conn, "
    SELECT
        u.student_id,
        u.full_name      AS student_name,
        u.email,
        c.club_name,
        ea.event_name,
        ea.attendance_date,
        ea.attendance_status,
        ea.check_in_time,
        ea.points_awarded,
        COALESCE(SUM(ea2.points_awarded), 0) AS cumulative_points
    FROM event_attendance ea
    JOIN users u ON ea.user_id = u.user_id
    LEFT JOIN memberships m ON u.user_id = m.user_id AND m.membership_status = 'Active'
    LEFT JOIN clubs c ON m.club_id = c.club_id
    LEFT JOIN event_attendance ea2 ON ea2.user_id = ea.user_id
                                   AND ea2.attendance_date <= ea.attendance_date
    WHERE u.role = 'student'
    GROUP BY ea.attendance_id
    ORDER BY ea.attendance_date DESC, u.full_name ASC
");

// =============================================
// SUMMARY STATS FOR TOP STRIP
// =============================================
$r = mysqli_query($conn, "SELECT COUNT(DISTINCT event_name) AS c FROM event_attendance");
$total_events = mysqli_fetch_assoc($r)['c'] ?? 0;

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM event_attendance");
$total_records = mysqli_fetch_assoc($r)['c'] ?? 0;

$r = mysqli_query($conn, "
    SELECT ROUND(
        SUM(CASE WHEN attendance_status IN ('present','volunteer') THEN 1 ELSE 0 END)
        / COUNT(*) * 100, 1
    ) AS rate FROM event_attendance");
$overall_rate = mysqli_fetch_assoc($r)['rate'] ?? 0;

$r = mysqli_query($conn, "
    SELECT COUNT(DISTINCT ea.user_id) AS c FROM event_attendance ea
    JOIN users u ON ea.user_id = u.user_id WHERE u.role = 'student'");
$unique_students = mysqli_fetch_assoc($r)['c'] ?? 0;
?>

<title>Event Reports & Statistics</title>

<?php include '../Includes/header_admin.php'; ?>
<?php include '../Includes/sidebar_admin.php'; ?>

<div class="main-content">

    <!-- PAGE HEADER -->
    <div class="rpt-page-header">
        <div>
            <h1><i class="fa-solid fa-file-lines" style="color:#10b981;"></i> Event Reports & Statistics</h1>
            <p>Comprehensive attendance and participation reports</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="participation_dashboard.php" class="rpt-btn-back">
                <i class="fa-solid fa-arrow-left"></i> Dashboard
            </a>
            <button onclick="printReport()" class="rpt-btn-print">
                <i class="fa-solid fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- SUMMARY STRIP -->
    <div class="rpt-summary-strip">
        <div class="rpt-stat">
            <i class="fa-solid fa-calendar-check" style="color:#3b82f6;"></i>
            <div><strong><?php echo $total_events; ?></strong><span>Events</span></div>
        </div>
        <div class="rpt-stat">
            <i class="fa-solid fa-list-check" style="color:#10b981;"></i>
            <div><strong><?php echo $total_records; ?></strong><span>Total Records</span></div>
        </div>
        <div class="rpt-stat">
            <i class="fa-solid fa-percent" style="color:#f59e0b;"></i>
            <div><strong><?php echo $overall_rate; ?>%</strong><span>Avg Attendance</span></div>
        </div>
        <div class="rpt-stat">
            <i class="fa-solid fa-user-graduate" style="color:#a855f7;"></i>
            <div><strong><?php echo $unique_students; ?></strong><span>Students Involved</span></div>
        </div>
    </div>

    <!-- TABS -->
    <div class="rpt-tabs">
        <a href="?tab=attendance"    class="rpt-tab <?php echo ($active_tab==='attendance')   ? 'rpt-tab-active' : ''; ?>">
            <i class="fa-solid fa-table"></i> Report 1: Attendance per Event
        </a>
        <a href="?tab=participation" class="rpt-tab <?php echo ($active_tab==='participation')? 'rpt-tab-active' : ''; ?>">
            <i class="fa-solid fa-table-columns"></i> Report 2: Student Participation (Join)
        </a>
    </div>

    <!-- =============================================
         REPORT 1: Single Table — event_attendance
    ============================================= -->
    <?php if ($active_tab === 'attendance'): ?>
    <div class="rpt-card" id="printableReport">
        <div class="rpt-card-header">
            <div>
                <h2><i class="fa-solid fa-table"></i> Attendance Summary per Event</h2>
                <p class="rpt-sql-tag">SQL: Single Table — <code>event_attendance</code></p>
            </div>
            <button onclick="exportCSV('tblSingle','attendance_report')" class="rpt-btn-export">
                <i class="fa-solid fa-download"></i> Export CSV
            </button>
        </div>

        <?php if (!mysqli_num_rows($single_table_result)): ?>
            <div class="rpt-empty"><i class="fa-solid fa-folder-open"></i><p>No attendance records found</p></div>
        <?php else: ?>
        <div class="rpt-table-wrap">
            <table class="rpt-table" id="tblSingle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Present</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Volunteer</th>
                        <th>Attendance Rate</th>
                        <th>Points Awarded</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 1; while ($row = mysqli_fetch_assoc($single_table_result)): ?>
                    <tr>
                        <td><?php echo $n++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['event_name']); ?></strong></td>
                        <td><?php echo $row['attendance_date'] ?? '-'; ?></td>
                        <td><?php echo $row['total_records']; ?></td>
                        <td><span class="rpt-badge rpt-present"><?php echo $row['present_count']; ?></span></td>
                        <td><span class="rpt-badge rpt-late"><?php echo $row['late_count']; ?></span></td>
                        <td><span class="rpt-badge rpt-absent"><?php echo $row['absent_count']; ?></span></td>
                        <td><span class="rpt-badge rpt-volunteer"><?php echo $row['volunteer_count']; ?></span></td>
                        <td>
                            <div class="rpt-rate-bar">
                                <div style="width:<?php echo $row['attendance_rate']; ?>%;background:<?php echo $row['attendance_rate']>=70?'#10b981':'#f59e0b'; ?>;height:100%;border-radius:4px;"></div>
                            </div>
                            <small><?php echo $row['attendance_rate']; ?>%</small>
                        </td>
                        <td><strong><?php echo $row['total_points_awarded']; ?> pts</strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- =============================================
         REPORT 2: Join Table — event_attendance JOIN users JOIN clubs
    ============================================= -->
    <?php if ($active_tab === 'participation'): ?>
    <div class="rpt-card" id="printableReport">
        <div class="rpt-card-header">
            <div>
                <h2><i class="fa-solid fa-table-columns"></i> Student Participation History</h2>
                <p class="rpt-sql-tag">SQL: Join Table — <code>event_attendance</code> JOIN <code>users</code> JOIN <code>memberships</code> JOIN <code>clubs</code></p>
            </div>
            <button onclick="exportCSV('tblJoin','participation_report')" class="rpt-btn-export">
                <i class="fa-solid fa-download"></i> Export CSV
            </button>
        </div>

        <?php if (!mysqli_num_rows($join_table_result)): ?>
            <div class="rpt-empty"><i class="fa-solid fa-folder-open"></i><p>No participation records found</p></div>
        <?php else: ?>
        <div class="rpt-table-wrap">
            <table class="rpt-table" id="tblJoin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Club</th>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Check-in</th>
                        <th>Status</th>
                        <th>Points</th>
                        <th>Cumulative Pts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $status_class = [
                        'present'   => 'rpt-present',
                        'late'      => 'rpt-late',
                        'absent'    => 'rpt-absent',
                        'volunteer' => 'rpt-volunteer',
                    ];
                    $n = 1;
                    while ($row = mysqli_fetch_assoc($join_table_result)):
                        $sc = $status_class[$row['attendance_status']] ?? '';
                    ?>
                    <tr>
                        <td><?php echo $n++; ?></td>
                        <td><?php echo htmlspecialchars($row['student_id'] ?? '-'); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['student_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['club_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                        <td><?php echo $row['attendance_date'] ?? '-'; ?></td>
                        <td><?php echo $row['check_in_time'] ?? '-'; ?></td>
                        <td><span class="rpt-badge <?php echo $sc; ?>"><?php echo ucfirst($row['attendance_status']); ?></span></td>
                        <td>
                            <strong style="color:<?php echo $row['points_awarded'] < 0 ? '#ef4444' : '#10b981'; ?>;">
                                <?php echo ($row['points_awarded'] >= 0 ? '+' : '') . $row['points_awarded']; ?>
                            </strong>
                        </td>
                        <td><strong><?php echo $row['cumulative_points']; ?> pts</strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div><!-- end main-content -->

<script>
function printReport() {
    window.print();
}

function exportCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    let csv = [];
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const rowData = Array.from(cols).map(col => '"' + col.innerText.replace(/"/g, '""') + '"');
        csv.push(rowData.join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename + '_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}
</script>

<style>
.rpt-page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.rpt-page-header h1{font-size:24px;font-weight:800;color:#1e293b;margin:0;display:flex;align-items:center;gap:10px;}
.rpt-page-header p{font-size:14px;color:#64748b;margin:4px 0 0;}
.rpt-btn-back,.rpt-btn-print,.rpt-btn-export{display:inline-flex;align-items:center;gap:8px;
    padding:10px 18px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;
    text-decoration:none;transition:all .2s;border:none;}
.rpt-btn-back{background:#fff;color:#374151;border:1.5px solid #e2e8f0;}
.rpt-btn-back:hover{border-color:#3b82f6;color:#3b82f6;}
.rpt-btn-print{background:#fff;color:#374151;border:1.5px solid #e2e8f0;}
.rpt-btn-print:hover{background:#f8fafc;}
.rpt-btn-export{background:#10b981;color:#fff;}
.rpt-btn-export:hover{background:#059669;}
.rpt-summary-strip{display:flex;gap:0;background:#fff;border-radius:16px;
    box-shadow:0 2px 10px rgba(0,0,0,.05);margin-bottom:24px;overflow:hidden;}
.rpt-stat{flex:1;display:flex;align-items:center;gap:14px;padding:20px 24px;
    border-right:1px solid #f1f5f9;}
.rpt-stat:last-child{border-right:none;}
.rpt-stat i{font-size:24px;}
.rpt-stat strong{font-size:26px;font-weight:800;color:#1e293b;display:block;}
.rpt-stat span{font-size:13px;color:#64748b;}
.rpt-tabs{display:flex;gap:4px;background:#fff;border-radius:16px;padding:6px;
    box-shadow:0 2px 10px rgba(0,0,0,.05);margin-bottom:20px;}
.rpt-tab{flex:1;display:flex;align-items:center;justify-content:center;gap:8px;
    padding:12px 18px;border-radius:12px;text-decoration:none;font-size:14px;
    font-weight:600;color:#64748b;transition:all .2s;}
.rpt-tab:hover{color:#1e293b;background:#f8fafc;}
.rpt-tab-active{background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff !important;}
.rpt-card{background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;}
.rpt-card-header{display:flex;justify-content:space-between;align-items:flex-start;
    padding:20px 24px;border-bottom:1px solid #f1f5f9;flex-wrap:wrap;gap:12px;}
.rpt-card-header h2{font-size:16px;font-weight:700;color:#1e293b;margin:0;
    display:flex;align-items:center;gap:10px;}
.rpt-sql-tag{font-size:12px;color:#94a3b8;margin:4px 0 0;}
.rpt-sql-tag code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-family:monospace;color:#3b82f6;}
.rpt-table-wrap{overflow-x:auto;padding:0 24px 24px;}
.rpt-table{width:100%;border-collapse:collapse;font-size:13px;margin-top:16px;}
.rpt-table thead th{background:#f8fafc;padding:12px 14px;text-align:left;
    font-weight:700;color:#374151;font-size:12px;border-bottom:2px solid #e2e8f0;white-space:nowrap;}
.rpt-table tbody td{padding:11px 14px;border-bottom:1px solid #f1f5f9;color:#374151;vertical-align:middle;}
.rpt-table tbody tr:hover{background:#f8fafc;}
.rpt-badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;}
.rpt-present{background:#d1fae5;color:#065f46;}
.rpt-late{background:#fef3c7;color:#92400e;}
.rpt-absent{background:#fee2e2;color:#991b1b;}
.rpt-volunteer{background:#dbeafe;color:#1e40af;}
.rpt-rate-bar{background:#e2e8f0;border-radius:4px;height:8px;width:80px;overflow:hidden;display:inline-block;vertical-align:middle;margin-right:6px;}
.rpt-empty{text-align:center;padding:60px;color:#94a3b8;}
.rpt-empty i{font-size:48px;display:block;margin-bottom:12px;}
@media print {
    .sidebar,.topbar,.rpt-tabs,.rpt-btn-back,.rpt-btn-print,.rpt-btn-export{display:none!important;}
    .main-content{margin-left:0!important;}
}
</style>

<?php include '../Includes/footer.php'; ?>
