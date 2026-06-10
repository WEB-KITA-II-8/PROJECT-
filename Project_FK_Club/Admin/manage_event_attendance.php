<?php
// =============================================
// MANAGE EVENT ATTENDANCE (PER-EVENT)
// FILE: Admin/manage_event_attendance.php
// MODULE 4 — Record & manage attendance for
//             ONE specific event at a time.
//
// USAGE:
//   manage_event_attendance.php?event_id=5
//
// Flow:
//   event_attendance_list.php
//     → click "Manage Attendance"
//     → this page (event_id in URL)
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

$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'admin';

// =============================================
// REQUIRE event_id — redirect back if missing
// =============================================
if (empty($_GET['event_id'])) {
    header("Location: event_attendance_list.php");
    exit();
}

$event_id = intval($_GET['event_id']);

// =============================================
// FETCH THIS EVENT'S DETAILS
// Try events_comm first (richer), fall back to events
// =============================================
$event = null;

// Try events_comm first
$r = mysqli_query($conn, "SELECT * FROM events_comm WHERE event_id = $event_id LIMIT 1");
if ($r && mysqli_num_rows($r) > 0) {
    $raw = mysqli_fetch_assoc($r);
    $event = [
        'event_id'       => $raw['event_id'],
        'event_name'     => $raw['event_name'],
        'event_date'     => date('Y-m-d', strtotime($raw['event_start_datetime'])),
        'event_time'     => date('g:i A', strtotime($raw['event_start_datetime'])),
        'event_location' => $raw['event_location'],
        'event_status'   => $raw['event_status'],
    ];
} else {
    // Fall back to simpler events table
    $r2 = mysqli_query($conn, "SELECT * FROM events WHERE event_id = $event_id LIMIT 1");
    if ($r2 && mysqli_num_rows($r2) > 0) {
        $raw = mysqli_fetch_assoc($r2);
        $event = [
            'event_id'       => $raw['event_id'],
            'event_name'     => $raw['event_name'],
            'event_date'     => $raw['event_date'],
            'event_time'     => '',
            'event_location' => $raw['event_location'] ?? '',
            'event_status'   => 'Active',
        ];
    }
}

if (!$event) {
    header("Location: event_attendance_list.php?error=not_found");
    exit();
}

$event_name     = $event['event_name'];
$event_date     = $event['event_date'];
$event_time     = $event['event_time'];
$event_location = $event['event_location'];

// =============================================
// ENSURE event_attendance TABLE EXISTS
// =============================================
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS event_attendance (
        attendance_id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT,
        user_id INT,
        event_name VARCHAR(255),
        student_id VARCHAR(50),
        student_name VARCHAR(255),
        club_name VARCHAR(255),
        check_in_time TIME,
        attendance_date DATE,
        attendance_status ENUM('present','late','absent','volunteer') DEFAULT 'present',
        points_awarded INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )
");

// =============================================
// POINTS HELPER
// =============================================
function calcPoints($status) {
    switch ($status) {
        case 'present':   return 10;
        case 'late':      return 5;
        case 'absent':    return -10;
        case 'volunteer': return 15;   // present +10  + volunteer +5
        default:          return 0;
    }
}

function statusColor($status) {
    switch ($status) {
        case 'present':   return '#10b981';
        case 'late':      return '#f59e0b';
        case 'absent':    return '#ef4444';
        case 'volunteer': return '#7c3aed';
        default:          return '#6b7280';
    }
}

// =============================================
// HANDLE: MARK ATTENDANCE (POST)
// =============================================
$flash_message = '';
$flash_type    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ---- ADD / MARK ----
    if ($action === 'mark_attendance') {
        $input_student_id   = mysqli_real_escape_string($conn, trim($_POST['student_id'] ?? ''));
        $attendance_status  = mysqli_real_escape_string($conn, $_POST['attendance_status'] ?? 'present');
        $check_in_time      = date('H:i:s');

        if (!empty($input_student_id)) {
            // Find user by student_id OR user_id
            $sq = "SELECT user_id, full_name, student_id
                   FROM users
                   WHERE (student_id = '$input_student_id' OR user_id = '$input_student_id')
                     AND role = 'student'
                   LIMIT 1";
            $sr = mysqli_query($conn, $sq);

            if ($sr && mysqli_num_rows($sr) > 0) {
                $student    = mysqli_fetch_assoc($sr);
                $uid        = $student['user_id'];
                $sname      = mysqli_real_escape_string($conn, $student['full_name']);
                $sid        = mysqli_real_escape_string($conn, $student['student_id'] ?: $student['user_id']);

                // Check duplicate for this event
                $dup = mysqli_query($conn, "SELECT attendance_id FROM event_attendance
                                            WHERE event_id = $event_id AND user_id = $uid LIMIT 1");
                if ($dup && mysqli_num_rows($dup) > 0) {
                    $flash_message = "⚠ Attendance already recorded for $sname in this event.";
                    $flash_type    = 'warning';
                } else {
                    // Get club
                    $cr    = mysqli_query($conn, "SELECT c.club_name FROM memberships m
                                                  JOIN clubs c ON m.club_id = c.club_id
                                                  WHERE m.user_id = $uid LIMIT 1");
                    $cname = ($cr && mysqli_num_rows($cr) > 0)
                           ? mysqli_real_escape_string($conn, mysqli_fetch_assoc($cr)['club_name'])
                           : 'N/A';

                    $pts = calcPoints($attendance_status);
                    $en  = mysqli_real_escape_string($conn, $event_name);

                    $ins = "INSERT INTO event_attendance
                            (event_id, user_id, event_name, student_id, student_name,
                             club_name, check_in_time, attendance_date, attendance_status, points_awarded)
                            VALUES ($event_id, $uid, '$en', '$sid', '$sname',
                                    '$cname', '$check_in_time', '$event_date', '$attendance_status', $pts)";

                    if (mysqli_query($conn, $ins)) {
                        header("Location: manage_event_attendance.php?event_id=$event_id&success=marked");
                        exit();
                    } else {
                        $flash_message = "✗ DB error: " . mysqli_error($conn);
                        $flash_type    = 'error';
                    }
                }
            } else {
                $flash_message = "✗ Student ID \"$input_student_id\" not found.";
                $flash_type    = 'error';
            }
        } else {
            $flash_message = "✗ Please enter a Student ID.";
            $flash_type    = 'error';
        }
    }

    // ---- EDIT STATUS ----
    if ($action === 'edit_attendance') {
        $att_id    = intval($_POST['attendance_id'] ?? 0);
        $new_status = mysqli_real_escape_string($conn, $_POST['attendance_status'] ?? 'present');
        $new_pts    = calcPoints($new_status);

        if ($att_id > 0) {
            $upd = "UPDATE event_attendance
                    SET attendance_status = '$new_status', points_awarded = $new_pts
                    WHERE attendance_id = $att_id AND event_id = $event_id";
            if (mysqli_query($conn, $upd)) {
                header("Location: manage_event_attendance.php?event_id=$event_id&success=updated");
                exit();
            } else {
                $flash_message = "✗ Update failed: " . mysqli_error($conn);
                $flash_type    = 'error';
            }
        }
    }

    // ---- DELETE ----
    if ($action === 'delete_attendance') {
        $att_id = intval($_POST['attendance_id'] ?? 0);
        if ($att_id > 0) {
            $del = "DELETE FROM event_attendance WHERE attendance_id = $att_id AND event_id = $event_id";
            if (mysqli_query($conn, $del)) {
                header("Location: manage_event_attendance.php?event_id=$event_id&success=deleted");
                exit();
            } else {
                $flash_message = "✗ Delete failed: " . mysqli_error($conn);
                $flash_type    = 'error';
            }
        }
    }
}

// Flash from redirect
if (empty($flash_message)) {
    if (isset($_GET['success'])) {
        $msgs = ['marked' => '✓ Attendance marked successfully.',
                 'updated' => '✓ Attendance updated.',
                 'deleted' => '✓ Record deleted.'];
        $flash_message = $msgs[$_GET['success']] ?? '✓ Done.';
        $flash_type    = 'success';
    }
}

// =============================================
// FETCH ATTENDANCE LIST FOR THIS EVENT
// =============================================
$search_q = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

$list_sql = "SELECT * FROM event_attendance WHERE event_id = $event_id";
if (!empty($search_q)) {
    $list_sql .= " AND (student_id LIKE '%$search_q%' OR student_name LIKE '%$search_q%')";
}
$list_sql .= " ORDER BY check_in_time ASC, created_at ASC";

$list_result = mysqli_query($conn, $list_sql);
$attendance_list = [];
if ($list_result) {
    while ($row = mysqli_fetch_assoc($list_result)) {
        $attendance_list[] = $row;
    }
}

// =============================================
// SUMMARY STATS FOR THIS EVENT
// =============================================
$stats = ['present' => 0, 'late' => 0, 'absent' => 0, 'volunteer' => 0, 'total' => 0];
foreach ($attendance_list as $rec) {
    $stats[$rec['attendance_status']] = ($stats[$rec['attendance_status']] ?? 0) + 1;
    $stats['total']++;
}

$attended = $stats['present'] + $stats['volunteer'] + $stats['late'];
$att_rate = $stats['total'] > 0 ? round($attended / $stats['total'] * 100) : 0;

// =============================================
// FETCH REGISTERED STUDENTS (from event_registrations)
// so we can show who hasn't been marked yet
// =============================================
$reg_result = mysqli_query($conn, "
    SELECT er.user_id, er.student_name, er.student_email,
           u.student_id
    FROM event_registrations er
    LEFT JOIN users u ON u.user_id = er.user_id
    WHERE er.event_id = $event_id
    ORDER BY er.student_name ASC
");
$registered_students = [];
if ($reg_result) {
    while ($r = mysqli_fetch_assoc($reg_result)) {
        $registered_students[] = $r;
    }
}

// IDs already marked
$marked_user_ids = array_column($attendance_list, 'user_id');

?>

<?php include '../Includes/header_admin.php'; ?>
<?php include '../Includes/sidebar_admin.php'; ?>

<style>
/* =============================================
   MANAGE EVENT ATTENDANCE (PER EVENT) STYLES
============================================= */
.main-content {
    margin-left: 260px;
    padding: 28px 30px;
    min-height: 100vh;
    background: #f4f7fb;
}

/* Breadcrumb */
.breadcrumb-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 18px;
}

.breadcrumb-bar a {
    color: #1a73e8;
    text-decoration: none;
}

.breadcrumb-bar a:hover { text-decoration: underline; }
.breadcrumb-bar i { font-size: 11px; }

/* Page Header */
.page-header {
    background: #fff;
    border-radius: 16px;
    padding: 22px 26px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
}

.event-title { font-size: 20px; font-weight: 700; color: #0b1f4d; margin: 0 0 6px; }
.event-details { display: flex; flex-wrap: wrap; gap: 14px; }
.event-detail-item { font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.event-detail-item i { color: #9ca3af; }

.btn-back {
    background: #f1f5f9;
    color: #374151;
    border: none;
    border-radius: 10px;
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    transition: background 0.2s;
    white-space: nowrap;
}
.btn-back:hover { background: #e2e8f0; color: #0b1f4d; text-decoration: none; }

/* Flash Messages */
.flash {
    padding: 13px 18px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.flash.success { background: #d1f5df; color: #15803d; }
.flash.error   { background: #ffe0e0; color: #dc2626; }
.flash.warning { background: #fff4db; color: #d97706; }

/* Stat Cards */
.stats-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}

.stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    text-align: center;
}
.stat-card .sv { font-size: 28px; font-weight: 800; line-height: 1; }
.stat-card .sl { font-size: 12px; color: #6b7280; margin-top: 4px; font-weight: 500; }
.stat-card .ss { font-size: 11px; color: #9ca3af; margin-top: 3px; }

.sc-total    .sv { color: #1a73e8; }
.sc-present  .sv { color: #188038; }
.sc-late     .sv { color: #e37400; }
.sc-absent   .sv { color: #dc2626; }
.sc-volunteer .sv { color: #7c3aed; }

/* Two-column layout */
.two-col {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}

/* Panel / Card */
.panel {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    overflow: hidden;
}

.panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: #0b1f4d;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.panel-title i { color: #1a73e8; }

/* Search */
.search-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 7px 12px;
}
.search-wrap input {
    border: none;
    background: transparent;
    outline: none;
    font-size: 13px;
    color: #374151;
    width: 180px;
}
.search-wrap i { color: #9ca3af; font-size: 12px; }

.btn-export {
    background: #f1f5f9;
    border: none;
    border-radius: 8px;
    padding: 7px 14px;
    font-size: 12.5px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    display: flex; align-items: center; gap: 6px;
    transition: background 0.2s;
}
.btn-export:hover { background: #e2e8f0; }

/* Table */
.table-wrap { overflow-x: auto; }
table.att-table { width: 100%; border-collapse: collapse; }
table.att-table th {
    background: #f8fafc;
    color: #374151;
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 11px 14px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}
table.att-table td {
    padding: 11px 14px;
    font-size: 13.5px;
    color: #374151;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
table.att-table tr:last-child td { border-bottom: none; }
table.att-table tbody tr:hover { background: #fafbfc; }

.status-pill {
    display: inline-block;
    padding: 4px 11px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
}

.pts-cell { font-weight: 700; }

.action-btns { display: flex; gap: 6px; }
.btn-edit, .btn-del {
    border: none;
    border-radius: 7px;
    padding: 6px 10px;
    font-size: 12px;
    cursor: pointer;
    display: flex; align-items: center; gap: 5px;
    font-weight: 600;
    transition: opacity 0.2s;
}
.btn-edit { background: #e8f0fe; color: #1a73e8; }
.btn-del  { background: #ffe0e0; color: #dc2626; }
.btn-edit:hover, .btn-del:hover { opacity: 0.8; }

.empty-row td { text-align: center; color: #9ca3af; font-size: 14px; padding: 36px 14px !important; }

/* --- MARK ATTENDANCE FORM (right column) --- */
.form-panel {
    position: sticky;
    top: 20px;
}

.form-inner { padding: 20px; }

.form-group { margin-bottom: 14px; }
.form-group label {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
}
.form-group input,
.form-group select {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 13.5px;
    color: #374151;
    outline: none;
    transition: border-color 0.2s;
    box-sizing: border-box;
}
.form-group input:focus,
.form-group select:focus { border-color: #1a73e8; }

.form-group .locked-field {
    background: #f8fafc;
    color: #6b7280;
    cursor: not-allowed;
}

.btn-mark {
    width: 100%;
    background: linear-gradient(135deg, #1a73e8, #4f9cf9);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 11px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: opacity 0.2s;
    margin-top: 4px;
}
.btn-mark:hover { opacity: 0.9; }

.points-hint {
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 16px;
    font-size: 12px;
    color: #6b7280;
    line-height: 1.7;
}
.points-hint strong { color: #374151; }

/* Registered students quick-pick */
.quick-pick {
    margin-top: 12px;
}
.quick-pick-label {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}
.reg-student-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
}
.reg-student-item {
    padding: 8px 12px;
    font-size: 12.5px;
    color: #374151;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background 0.15s;
}
.reg-student-item:last-child { border-bottom: none; }
.reg-student-item:hover { background: #f0f7ff; }
.reg-student-item.marked { background: #f0fdf4; color: #15803d; cursor: default; }
.reg-student-item.marked:hover { background: #f0fdf4; }
.reg-badge-done {
    font-size: 10.5px;
    background: #d1f5df;
    color: #15803d;
    border-radius: 10px;
    padding: 2px 8px;
    font-weight: 700;
}

/* QR code */
.qr-section {
    padding: 16px 20px;
    border-top: 1px solid #f1f5f9;
    text-align: center;
}
.qr-section p {
    font-size: 12px;
    color: #9ca3af;
    margin: 8px 0 0;
}
#qrcode canvas, #qrcode img {
    border-radius: 8px;
}

/* Edit Modal */
.modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 999;
    align-items: center;
    justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: #fff;
    border-radius: 16px;
    padding: 28px 30px;
    width: 380px;
    max-width: 95%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.modal-title {
    font-size: 16px;
    font-weight: 700;
    color: #0b1f4d;
    margin: 0 0 18px;
}
.modal-actions { display: flex; gap: 10px; margin-top: 18px; }
.btn-confirm {
    flex: 1;
    background: #1a73e8;
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
}
.btn-cancel {
    flex: 1;
    background: #f1f5f9;
    color: #374151;
    border: none;
    border-radius: 9px;
    padding: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}
</style>

<div class="main-content">

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <a href="event_attendance_list.php"><i class="fa-solid fa-house"></i> Events List</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span><?php echo htmlspecialchars($event_name); ?></span>
        <i class="fa-solid fa-chevron-right"></i>
        <span>Manage Attendance</span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="event-title">
                <i class="fa-solid fa-calendar-check" style="color:#1a73e8; margin-right:8px;"></i>
                <?php echo htmlspecialchars($event_name); ?>
            </h1>
            <div class="event-details">
                <div class="event-detail-item">
                    <i class="fa-solid fa-calendar-day"></i>
                    <?php echo date('d M Y', strtotime($event_date)); ?>
                    &nbsp;·&nbsp; <?php echo $event_time; ?>
                </div>
                <?php if (!empty($event_location)): ?>
                <div class="event-detail-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <?php echo htmlspecialchars($event_location); ?>
                </div>
                <?php endif; ?>
                <div class="event-detail-item">
                    <i class="fa-solid fa-users"></i>
                    <?php echo count($registered_students); ?> registered
                </div>
            </div>
        </div>
        <a href="event_attendance_list.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Back to Events
        </a>
    </div>

    <!-- Flash Message -->
    <?php if (!empty($flash_message)): ?>
    <div class="flash <?php echo $flash_type; ?>">
        <i class="fa-solid <?php echo $flash_type === 'success' ? 'fa-circle-check' : ($flash_type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-xmark'); ?>"></i>
        <?php echo htmlspecialchars($flash_message); ?>
    </div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card sc-total">
            <div class="sv"><?php echo $stats['total']; ?></div>
            <div class="sl">Total Marked</div>
            <div class="ss"><?php echo count($registered_students); ?> registered</div>
        </div>
        <div class="stat-card sc-present">
            <div class="sv"><?php echo $stats['present']; ?></div>
            <div class="sl">Present</div>
            <div class="ss">+10 pts each</div>
        </div>
        <div class="stat-card sc-late">
            <div class="sv"><?php echo $stats['late']; ?></div>
            <div class="sl">Late</div>
            <div class="ss">+5 pts each</div>
        </div>
        <div class="stat-card sc-absent">
            <div class="sv"><?php echo $stats['absent']; ?></div>
            <div class="sl">Absent</div>
            <div class="ss">-10 pts each</div>
        </div>
        <div class="stat-card sc-volunteer">
            <div class="sv"><?php echo $stats['volunteer']; ?></div>
            <div class="sl">Volunteer</div>
            <div class="ss">+15 pts each</div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="two-col">

        <!-- LEFT: Attendance List -->
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i class="fa-solid fa-list"></i>
                    Attendance Records
                    <span style="font-size:12px; background:#e8f0fe; color:#1a73e8; border-radius:20px; padding:2px 10px; font-weight:600; margin-left:6px;">
                        <?php echo $stats['total']; ?> records
                    </span>
                </h2>
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <form method="GET" style="display:contents;">
                        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                        <div class="search-wrap">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search"
                                   placeholder="Search student..."
                                   value="<?php echo htmlspecialchars($search_q); ?>"
                                   onchange="this.form.submit()">
                        </div>
                    </form>
                    <button class="btn-export" onclick="exportCSV()">
                        <i class="fa-solid fa-download"></i> Export CSV
                    </button>
                </div>
            </div>

            <div class="table-wrap">
                <table class="att-table" id="attTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Club</th>
                            <th>Check-in</th>
                            <th>Status</th>
                            <th>Points</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendance_list)): ?>
                        <tr class="empty-row">
                            <td colspan="8">
                                <i class="fa-solid fa-clipboard" style="display:block; font-size:32px; margin-bottom:8px; opacity:0.3;"></i>
                                No attendance records yet for this event.<br>
                                <span style="font-size:12px;">Use the form on the right to mark students.</span>
                            </td>
                        </tr>
                        <?php else: foreach ($attendance_list as $i => $rec):
                            $sc = statusColor($rec['attendance_status']);
                            $pts = intval($rec['points_awarded']);
                        ?>
                        <tr>
                            <td style="color:#9ca3af; font-size:12px;"><?php echo $i + 1; ?></td>
                            <td><strong><?php echo htmlspecialchars($rec['student_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($rec['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($rec['club_name']); ?></td>
                            <td style="font-size:12.5px; color:#6b7280;">
                                <?php echo $rec['check_in_time'] ? date('g:i A', strtotime($rec['check_in_time'])) : '—'; ?>
                            </td>
                            <td>
                                <span class="status-pill" style="background:<?php echo $sc; ?>20; color:<?php echo $sc; ?>;">
                                    <?php echo ucfirst($rec['attendance_status']); ?>
                                </span>
                            </td>
                            <td class="pts-cell" style="color:<?php echo $pts >= 0 ? '#188038' : '#dc2626'; ?>;">
                                <?php echo ($pts >= 0 ? '+' : '') . $pts; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn-edit"
                                            onclick="openEditModal(<?php echo $rec['attendance_id']; ?>, '<?php echo $rec['attendance_status']; ?>', '<?php echo htmlspecialchars($rec['student_name'], ENT_QUOTES); ?>')">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <button class="btn-del"
                                            onclick="openDeleteModal(<?php echo $rec['attendance_id']; ?>, '<?php echo htmlspecialchars($rec['student_name'], ENT_QUOTES); ?>')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div><!-- /panel left -->

        <!-- RIGHT: Mark Attendance Form -->
        <div class="panel form-panel">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i class="fa-solid fa-circle-plus"></i>
                    Mark Attendance
                </h2>
            </div>

            <div class="form-inner">

                <!-- Points Legend -->
                <div class="points-hint">
                    <strong>Points Guide:</strong><br>
                    ✅ Present on time: <strong style="color:#188038;">+10 pts</strong><br>
                    🕐 Late arrival: <strong style="color:#e37400;">+5 pts</strong><br>
                    ❌ Absent: <strong style="color:#dc2626;">−10 pts</strong><br>
                    🤝 Volunteer/Helper: <strong style="color:#7c3aed;">+15 pts</strong>
                </div>

                <form method="POST" id="markForm">
                    <input type="hidden" name="action" value="mark_attendance">
                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

                    <div class="form-group">
                        <label>Event</label>
                        <input type="text" class="locked-field" value="<?php echo htmlspecialchars($event_name); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Date</label>
                        <input type="text" class="locked-field" value="<?php echo date('d M Y', strtotime($event_date)); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Student ID <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="student_id" id="studentIdInput"
                               placeholder="Enter Student ID e.g. CB22029"
                               required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label>Attendance Status</label>
                        <select name="attendance_status" required>
                            <option value="present">✅ Present on time (+10 pts)</option>
                            <option value="late">🕐 Late arrival (+5 pts)</option>
                            <option value="absent">❌ Absent without notice (−10 pts)</option>
                            <option value="volunteer">🤝 Volunteer / Helper (+15 pts)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-mark">
                        <i class="fa-solid fa-check"></i> Mark Attendance
                    </button>
                </form>

                <!-- Registered students quick-pick -->
                <?php if (!empty($registered_students)): ?>
                <div class="quick-pick">
                    <div class="quick-pick-label">
                        <i class="fa-solid fa-user-check" style="color:#1a73e8;"></i>
                        Registered Students (click to fill)
                    </div>
                    <div class="reg-student-list">
                        <?php foreach ($registered_students as $rs):
                            $is_marked = in_array($rs['user_id'], $marked_user_ids);
                            $display_sid = $rs['student_id'] ?: $rs['user_id'];
                        ?>
                        <div class="reg-student-item <?php echo $is_marked ? 'marked' : ''; ?>"
                             <?php if (!$is_marked): ?>
                             onclick="fillStudentId('<?php echo htmlspecialchars($display_sid, ENT_QUOTES); ?>')"
                             title="Click to fill Student ID"
                             <?php endif; ?>>
                            <span>
                                <strong><?php echo htmlspecialchars($display_sid); ?></strong>
                                — <?php echo htmlspecialchars($rs['student_name']); ?>
                            </span>
                            <?php if ($is_marked): ?>
                            <span class="reg-badge-done">✓ Marked</span>
                            <?php else: ?>
                            <i class="fa-solid fa-arrow-up-right-from-square" style="color:#9ca3af; font-size:10px;"></i>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /form-inner -->

            <!-- QR Code Section -->
            <div class="qr-section">
                <div id="qrcode"></div>
                <p><i class="fa-solid fa-qrcode"></i> QR code for this event</p>
                <button onclick="downloadQR()" style="margin-top:8px; background:#f1f5f9; border:none; border-radius:8px; padding:7px 14px; font-size:12px; font-weight:600; cursor:pointer; color:#374151;">
                    <i class="fa-solid fa-download"></i> Download QR
                </button>
            </div>

        </div><!-- /form-panel -->

    </div><!-- /two-col -->

</div><!-- /main-content -->

<!-- =============================================
     EDIT MODAL
============================================= -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <h3 class="modal-title"><i class="fa-solid fa-pen" style="color:#1a73e8;"></i> Edit Attendance</h3>
        <p id="editModalName" style="font-size:13.5px; color:#6b7280; margin:0 0 16px;"></p>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="edit_attendance">
            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
            <input type="hidden" name="attendance_id" id="editAttId">
            <div class="form-group">
                <label>Attendance Status</label>
                <select name="attendance_status" id="editStatusSelect">
                    <option value="present">✅ Present on time (+10 pts)</option>
                    <option value="late">🕐 Late arrival (+5 pts)</option>
                    <option value="absent">❌ Absent without notice (−10 pts)</option>
                    <option value="volunteer">🤝 Volunteer / Helper (+15 pts)</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-confirm">Save Changes</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <h3 class="modal-title"><i class="fa-solid fa-trash" style="color:#dc2626;"></i> Delete Record</h3>
        <p id="deleteModalName" style="font-size:13.5px; color:#6b7280; margin:0 0 16px;"></p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete_attendance">
            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
            <input type="hidden" name="attendance_id" id="deleteAttId">
            <div class="modal-actions">
                <button type="submit" class="btn-confirm" style="background:#dc2626;">Delete</button>
                <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- QR code library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// =============================================
// QR Code Generation
// =============================================
window.addEventListener('DOMContentLoaded', function () {
    var qrData = "event_id=<?php echo $event_id; ?>&event=<?php echo urlencode($event_name); ?>&date=<?php echo $event_date; ?>";
    new QRCode(document.getElementById('qrcode'), {
        text: qrData,
        width: 160,
        height: 160,
        colorDark: '#0b1f4d',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
});

function downloadQR() {
    var canvas = document.querySelector('#qrcode canvas');
    if (!canvas) return alert('QR not ready yet.');
    var link = document.createElement('a');
    link.download = 'attendance_qr_event_<?php echo $event_id; ?>.png';
    link.href = canvas.toDataURL();
    link.click();
}

// =============================================
// Quick-pick: fill student ID input
// =============================================
function fillStudentId(sid) {
    document.getElementById('studentIdInput').value = sid;
    document.getElementById('studentIdInput').focus();
}

// =============================================
// Modals
// =============================================
function openEditModal(attId, status, name) {
    document.getElementById('editAttId').value = attId;
    document.getElementById('editStatusSelect').value = status;
    document.getElementById('editModalName').textContent = 'Student: ' + name;
    document.getElementById('editModal').classList.add('open');
}

function openDeleteModal(attId, name) {
    document.getElementById('deleteAttId').value = attId;
    document.getElementById('deleteModalName').textContent =
        'Are you sure you want to delete the attendance record for ' + name + '?';
    document.getElementById('deleteModal').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (e.target === el) el.classList.remove('open');
    });
});

// =============================================
// Export CSV
// =============================================
function exportCSV() {
    var table = document.getElementById('attTable');
    var rows = table.querySelectorAll('tr');
    var csv = [];
    rows.forEach(function(row) {
        var cols = row.querySelectorAll('th, td');
        var rowData = [];
        // skip last column (Action)
        for (var i = 0; i < cols.length - 1; i++) {
            var text = cols[i].innerText.replace(/\n/g, ' ').replace(/,/g, ';');
            rowData.push('"' + text + '"');
        }
        csv.push(rowData.join(','));
    });
    var blob = new Blob([csv.join('\n')], {type: 'text/csv'});
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href = url;
    a.download = 'attendance_<?php echo $event_id; ?>_<?php echo $event_date; ?>.csv';
    a.click();
}
</script>

<?php include '../Includes/footer.php'; ?>
