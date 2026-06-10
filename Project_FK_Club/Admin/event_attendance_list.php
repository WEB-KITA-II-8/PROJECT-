<?php
// =============================================
// EVENT ATTENDANCE LIST
// FILE: Admin/event_attendance_list.php
// MODULE 4 — Lists all events, each with a
//             "Manage Attendance" button that
//             opens the per-event attendance page
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
// FILTERS
// =============================================
$search_event = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';  // all | upcoming | past

// =============================================
// FETCH EVENTS WITH ATTENDANCE STATS
// We merge both events tables (events from Admin
// and events_comm from Committee) so all events
// appear in the list regardless of who created them.
// =============================================

$today = date('Y-m-d');

// ============================================================
// DETECT WHICH EVENTS TABLE(S) EXIST IN THIS DATABASE
// events_comm = committee events (richer, has datetime/capacity)
// events      = simpler admin table
// We support both so the page never crashes regardless of
// which tables have been imported into the local database.
// ============================================================
$has_events_comm = mysqli_query($conn, "SELECT 1 FROM events_comm LIMIT 1") !== false;
$has_events      = mysqli_query($conn, "SELECT 1 FROM events LIMIT 1") !== false;

// Build search/status filters
$where_search = !empty($search_event) ? "AND e.event_name LIKE '%" . $search_event . "%'" : '';
$where_status = '';
if ($filter_status === 'upcoming') $where_status = "AND e.event_date >= '$today'";
elseif ($filter_status === 'past')  $where_status = "AND e.event_date < '$today'";

// ============================================================
// BUILD UNIFIED SOURCE QUERY (UNION of both tables if needed)
// ============================================================
$parts = [];

if ($has_events_comm) {
    $parts[] = "SELECT
        ec.event_id,
        ec.event_name,
        DATE(ec.event_start_datetime) AS event_date,
        TIME(ec.event_start_datetime) AS event_time,
        ec.event_location,
        COALESCE(ec.event_capacity, 0) AS event_capacity,
        ec.event_status
    FROM events_comm ec";
}

if ($has_events) {
    $excl = $has_events_comm ? "AND ev.event_id NOT IN (SELECT event_id FROM events_comm)" : "";
    $parts[] = "SELECT
        ev.event_id,
        ev.event_name,
        ev.event_date AS event_date,
        NULL          AS event_time,
        ev.event_location,
        0             AS event_capacity,
        'Active'      AS event_status
    FROM events ev WHERE 1=1 $excl";
}

$events = [];

if (!empty($parts)) {
    $union_sql = implode(" UNION ALL ", $parts);

    $events_query = "
        SELECT
            e.event_id, e.event_name, e.event_date, e.event_time,
            e.event_location, e.event_capacity, e.event_status,
            COALESCE(reg.registered_count, 0) AS registered_count,
            COALESCE(att.attended_count,   0) AS attended_count,
            COALESCE(att.present_count,    0) AS present_count,
            COALESCE(att.late_count,       0) AS late_count,
            COALESCE(att.absent_count,     0) AS absent_count,
            COALESCE(att.volunteer_count,  0) AS volunteer_count
        FROM ($union_sql) e
        LEFT JOIN (
            SELECT event_id, COUNT(*) AS registered_count
            FROM event_registrations GROUP BY event_id
        ) reg ON reg.event_id = e.event_id
        LEFT JOIN (
            SELECT event_id,
                COUNT(*)                             AS attended_count,
                SUM(attendance_status='present')     AS present_count,
                SUM(attendance_status='late')        AS late_count,
                SUM(attendance_status='absent')      AS absent_count,
                SUM(attendance_status='volunteer')   AS volunteer_count
            FROM event_attendance GROUP BY event_id
        ) att ON att.event_id = e.event_id
        WHERE 1=1 $where_search $where_status
        ORDER BY e.event_date DESC
    ";

    $events_result = mysqli_query($conn, $events_query);
    if ($events_result) {
        while ($row = mysqli_fetch_assoc($events_result)) {
            $events[] = $row;
        }
    }
}

// =============================================
// SUMMARY STATS (for the stat cards)
// =============================================
$total_events   = count($events);
$upcoming_count = 0;
$past_count     = 0;
foreach ($events as $ev) {
    if (!empty($ev['event_date']) && $ev['event_date'] >= $today) $upcoming_count++;
    else $past_count++;
}

// Total attendance records ever
$total_att_result = mysqli_query($conn, "SELECT COUNT(*) AS c FROM event_attendance");
$total_attendance = $total_att_result ? mysqli_fetch_assoc($total_att_result)['c'] : 0;

?>

<?php include '../Includes/header_admin.php'; ?>
<?php include '../Includes/sidebar_admin.php'; ?>

<style>
/* =============================================
   EVENT ATTENDANCE LIST — INLINE STYLES
   Matching the project's existing design language
============================================= */
.main-content {
    margin-left: 260px;
    padding: 30px;
    min-height: 100vh;
    background: #f4f7fb;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}

.page-title {
    font-size: 24px;
    font-weight: 700;
    color: #0b1f4d;
    margin: 0;
}

.page-subtitle {
    font-size: 13px;
    color: #6b7280;
    margin: 4px 0 0;
}

/* Stat Cards */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 28px;
}

.stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-icon.blue   { background: #e8f0fe; color: #1a73e8; }
.stat-icon.green  { background: #e6f4ea; color: #188038; }
.stat-icon.orange { background: #fef3e2; color: #e37400; }
.stat-icon.purple { background: #f3e8fd; color: #7c3aed; }

.stat-info .stat-value {
    font-size: 26px;
    font-weight: 700;
    color: #0b1f4d;
    line-height: 1;
}

.stat-info .stat-label {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

/* Filter Bar */
.filter-bar {
    background: #fff;
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.filter-bar input[type="text"] {
    flex: 1;
    min-width: 200px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 13.5px;
    color: #374151;
    outline: none;
    transition: border-color 0.2s;
}

.filter-bar input[type="text"]:focus {
    border-color: #1a73e8;
}

.filter-bar select {
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 13.5px;
    color: #374151;
    outline: none;
    background: #fff;
    cursor: pointer;
}

.btn-search {
    background: #1a73e8;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 9px 20px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: background 0.2s;
}

.btn-search:hover { background: #1558c0; }

.btn-reset {
    background: #f1f5f9;
    color: #374151;
    border: none;
    border-radius: 8px;
    padding: 9px 16px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
}

.btn-reset:hover { background: #e2e8f0; }

/* Events Grid */
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

/* Event Card */
.event-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}

.event-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.event-card-header {
    padding: 18px 20px 14px;
    border-bottom: 1px solid #f1f5f9;
}

.event-card-header-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
}

.event-name {
    font-size: 15.5px;
    font-weight: 700;
    color: #0b1f4d;
    line-height: 1.3;
}

.event-status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
}

.badge-approved  { background: #d1f5df; color: #15803d; }
.badge-pending   { background: #fff4db; color: #d97706; }
.badge-rejected  { background: #ffe0e0; color: #dc2626; }
.badge-past      { background: #f1f5f9; color: #64748b; }
.badge-upcoming  { background: #e8f0fe; color: #1a73e8; }

.event-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.event-meta-item {
    font-size: 12.5px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 6px;
}

.event-meta-item i {
    width: 14px;
    color: #9ca3af;
}

/* Attendance Mini Stats */
.event-card-stats {
    padding: 14px 20px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    background: #fafbfc;
    border-bottom: 1px solid #f1f5f9;
}

.mini-stat {
    text-align: center;
}

.mini-stat .mini-val {
    font-size: 18px;
    font-weight: 700;
    line-height: 1;
}

.mini-stat .mini-lbl {
    font-size: 10.5px;
    color: #9ca3af;
    margin-top: 3px;
}

.mini-stat.present .mini-val  { color: #188038; }
.mini-stat.late .mini-val     { color: #e37400; }
.mini-stat.absent .mini-val   { color: #dc2626; }
.mini-stat.volunteer .mini-val{ color: #7c3aed; }

/* Progress bar */
.attendance-bar-wrap {
    padding: 8px 20px 4px;
    background: #fafbfc;
    border-bottom: 1px solid #f1f5f9;
}

.attendance-bar-label {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 5px;
}

.attendance-bar {
    height: 6px;
    background: #e5e7eb;
    border-radius: 99px;
    overflow: hidden;
}

.attendance-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #1a73e8, #4f9cf9);
    border-radius: 99px;
    transition: width 0.4s;
}

/* Card Footer / Action */
.event-card-footer {
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: auto;
}

.registered-note {
    font-size: 12px;
    color: #6b7280;
}

.registered-note strong {
    color: #0b1f4d;
}

.btn-manage-attendance {
    background: linear-gradient(135deg, #1a73e8, #4f9cf9);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: opacity 0.2s, transform 0.15s;
    white-space: nowrap;
}

.btn-manage-attendance:hover {
    opacity: 0.92;
    transform: scale(1.03);
    color: #fff;
    text-decoration: none;
}

/* Empty State */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 14px;
    display: block;
    opacity: 0.4;
}

.empty-state p {
    font-size: 15px;
    margin: 0;
}
</style>

<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fa-solid fa-calendar-check" style="color:#1a73e8; margin-right:10px;"></i>
                Event Attendance Management
            </h1>
            <p class="page-subtitle">Select an event below to record or view its attendance.</p>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-calendar"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $total_events; ?></div>
                <div class="stat-label">Total Events</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-calendar-plus"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $upcoming_count; ?></div>
                <div class="stat-label">Upcoming Events</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fa-solid fa-calendar-xmark"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $past_count; ?></div>
                <div class="stat-label">Past Events</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fa-solid fa-clipboard-check"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $total_attendance; ?></div>
                <div class="stat-label">Total Attendance Records</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="">
        <div class="filter-bar">
            <i class="fa-solid fa-magnifying-glass" style="color:#9ca3af;"></i>
            <input type="text" name="search" placeholder="Search event name..."
                   value="<?php echo htmlspecialchars($search_event); ?>">
            <select name="status">
                <option value="all"      <?php echo $filter_status === 'all'      ? 'selected' : ''; ?>>All Events</option>
                <option value="upcoming" <?php echo $filter_status === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                <option value="past"     <?php echo $filter_status === 'past'     ? 'selected' : ''; ?>>Past</option>
            </select>
            <button type="submit" class="btn-search">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <a href="event_attendance_list.php" class="btn-reset">Reset</a>
        </div>
    </form>

    <!-- Events Grid -->
    <div class="events-grid">

        <?php if (empty($events)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-calendar-xmark"></i>
            <p>No events found. Try adjusting your filters.</p>
        </div>

        <?php else: foreach ($events as $event):

            // Determine if upcoming or past
            $is_upcoming = ($event['event_date'] >= $today);

            // Status badge
            $status_raw = strtolower($event['event_status'] ?? '');
            if ($status_raw === 'approved') {
                $badge_class = 'badge-approved';
                $badge_label = 'Approved';
            } elseif ($status_raw === 'pending') {
                $badge_class = 'badge-pending';
                $badge_label = 'Pending';
            } elseif ($status_raw === 'rejected') {
                $badge_class = 'badge-rejected';
                $badge_label = 'Rejected';
            } else {
                $badge_class = $is_upcoming ? 'badge-upcoming' : 'badge-past';
                $badge_label = $is_upcoming ? 'Upcoming' : 'Past';
            }

            // Attendance rate
            $reg   = intval($event['registered_count']);
            $att   = intval($event['attended_count']);
            $rate  = $reg > 0 ? round($att / $reg * 100) : 0;

            // Date display
            $date_display = $event['event_date']
                ? date('d M Y', strtotime($event['event_date']))
                : 'TBA';
            $time_display = $event['event_time']
                ? date('g:i A', strtotime($event['event_time']))
                : '';
        ?>

        <div class="event-card">

            <!-- Header -->
            <div class="event-card-header">
                <div class="event-card-header-top">
                    <div class="event-name">
                        <?php echo htmlspecialchars($event['event_name']); ?>
                    </div>
                    <span class="event-status-badge <?php echo $badge_class; ?>">
                        <?php echo $badge_label; ?>
                    </span>
                </div>
                <div class="event-meta">
                    <div class="event-meta-item">
                        <i class="fa-solid fa-calendar-day"></i>
                        <?php echo $date_display; ?>
                        <?php if ($time_display): ?>
                            &nbsp;·&nbsp; <?php echo $time_display; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($event['event_location'])): ?>
                    <div class="event-meta-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <?php echo htmlspecialchars($event['event_location']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mini Attendance Stats -->
            <div class="event-card-stats">
                <div class="mini-stat present">
                    <div class="mini-val"><?php echo $event['present_count']; ?></div>
                    <div class="mini-lbl">Present</div>
                </div>
                <div class="mini-stat late">
                    <div class="mini-val"><?php echo $event['late_count']; ?></div>
                    <div class="mini-lbl">Late</div>
                </div>
                <div class="mini-stat absent">
                    <div class="mini-val"><?php echo $event['absent_count']; ?></div>
                    <div class="mini-lbl">Absent</div>
                </div>
                <div class="mini-stat volunteer">
                    <div class="mini-val"><?php echo $event['volunteer_count']; ?></div>
                    <div class="mini-lbl">Volunteer</div>
                </div>
            </div>

            <!-- Attendance Progress Bar -->
            <div class="attendance-bar-wrap">
                <div class="attendance-bar-label">
                    <span>Attendance Rate</span>
                    <span><?php echo $rate; ?>%</span>
                </div>
                <div class="attendance-bar">
                    <div class="attendance-bar-fill" style="width: <?php echo $rate; ?>%;"></div>
                </div>
            </div>

            <!-- Footer / Action -->
            <div class="event-card-footer">
                <div class="registered-note">
                    <strong><?php echo $reg; ?></strong> registered
                    &nbsp;·&nbsp;
                    Capacity: <strong><?php echo $event['event_capacity'] ?: '—'; ?></strong>
                </div>
                <a href="manage_event_attendance.php?event_id=<?php echo $event['event_id']; ?>"
                   class="btn-manage-attendance">
                    <i class="fa-solid fa-clipboard-check"></i>
                    Manage Attendance
                </a>
            </div>

        </div>

        <?php endforeach; endif; ?>

    </div><!-- /events-grid -->

</div><!-- /main-content -->

<?php include '../Includes/footer.php'; ?>
