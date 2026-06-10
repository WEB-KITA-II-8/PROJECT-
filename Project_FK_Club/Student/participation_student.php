<?php
// =============================================
// STUDENT PARTICIPATION HISTORY & POINTS
// FILE: Student/participation_student.php
// MODULE 4 - Student-side view
// =============================================

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$user_id      = $_SESSION['user_id'];
$student_name = $_SESSION['full_name'] ?? 'Student';

// =============================================
// MY TOTAL POINTS
// =============================================
$r = mysqli_query($conn, "
    SELECT COALESCE(SUM(points_awarded),0) AS total FROM event_attendance WHERE user_id = $user_id
");
$my_points = (int)(mysqli_fetch_assoc($r)['total'] ?? 0);

// Recognition level
function getRecognitionLevel($points) {
    if ($points >= 80)     return ['label'=>'Outstanding',    'color'=>'#f59e0b','icon'=>'fa-crown',                'desc'=>'Eligible for leadership award & priority event registration'];
    elseif ($points >= 50) return ['label'=>'Active Student', 'color'=>'#10b981','icon'=>'fa-star',                 'desc'=>'Eligible for active student award / bonus points'];
    elseif ($points >= 20) return ['label'=>'Eligible',       'color'=>'#3b82f6','icon'=>'fa-bookmark',             'desc'=>'Eligible for participation certificate'];
    else                   return ['label'=>'Warning',        'color'=>'#ef4444','icon'=>'fa-triangle-exclamation', 'desc'=>'Please participate in more events'];
}
$recognition = getRecognitionLevel($my_points);

// Progress to next level
if ($my_points < 20)      { $next = 20;  $next_label = 'Eligible';       $progress_max = 20; }
elseif ($my_points < 50)  { $next = 50;  $next_label = 'Active Student';  $progress_max = 50; }
elseif ($my_points < 80)  { $next = 80;  $next_label = 'Outstanding';     $progress_max = 80; }
else                      { $next = null; $next_label = 'Max Level';      $progress_max = 80; }
$progress_pct = $next ? min(round(($my_points / $next) * 100), 100) : 100;

// =============================================
// MY RANKING  (among all students)
// =============================================
$rank_result = mysqli_query($conn, "
    SELECT user_id, COALESCE(SUM(points_awarded),0) AS pts
    FROM event_attendance
    GROUP BY user_id
    ORDER BY pts DESC
");
$rank = 1; $total_ranked = 0;
while ($row = mysqli_fetch_assoc($rank_result)) {
    $total_ranked++;
    if ($row['user_id'] == $user_id) $my_rank = $rank;
    $rank++;
}
$my_rank = $my_rank ?? $total_ranked;

// =============================================
// MY EVENT STATS
// =============================================
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM event_attendance WHERE user_id=$user_id");
$total_events_attended = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM event_attendance WHERE user_id=$user_id AND attendance_status='present'");
$present_count = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM event_attendance WHERE user_id=$user_id AND attendance_status='volunteer'");
$volunteer_count = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM event_attendance WHERE user_id=$user_id AND attendance_status='late'");
$late_count = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM event_attendance WHERE user_id=$user_id AND attendance_status='absent'");
$absent_count = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

// =============================================
// MY ATTENDANCE HISTORY  (all records)
// =============================================
$history_result = mysqli_query($conn, "
    SELECT event_name, attendance_date, check_in_time,
           attendance_status, points_awarded, club_name
    FROM event_attendance
    WHERE user_id = $user_id
    ORDER BY attendance_date DESC, created_at DESC
");

// Monthly points trend for chart
$trend_result = mysqli_query($conn, "
    SELECT DATE_FORMAT(attendance_date,'%b %Y') AS month,
           DATE_FORMAT(attendance_date,'%Y-%m') AS mk,
           SUM(points_awarded) AS pts
    FROM event_attendance
    WHERE user_id = $user_id
    GROUP BY mk, month
    ORDER BY mk ASC
    LIMIT 6
");
$trend_labels = []; $trend_pts = [];
while ($row = mysqli_fetch_assoc($trend_result)) {
    $trend_labels[] = $row['month'];
    $trend_pts[]    = (int)$row['pts'];
}
?>

<title>My Participation</title>

<?php include '../Includes/header_stud.php'; ?>
<?php include '../Includes/sidebar_stud.php'; ?>

<div class="main-content">

    <!-- PAGE HEADER -->
    <div class="sp-header">
        <div>
            <h1><i class="fa-solid fa-chart-line" style="color:#3b82f6;"></i> My Participation</h1>
            <p>Track your event attendance, points, and recognition level</p>
        </div>
    </div>

    <!-- TOP CARDS ROW -->
    <div class="sp-top-row">

        <!-- Points + Recognition Card -->
        <div class="sp-points-card" style="border-top:4px solid <?php echo $recognition['color']; ?>;">
            <div class="sp-points-icon" style="background:<?php echo $recognition['color']; ?>20;color:<?php echo $recognition['color']; ?>;">
                <i class="fa-solid <?php echo $recognition['icon']; ?>"></i>
            </div>
            <div class="sp-points-body">
                <div class="sp-pts-number" style="color:<?php echo $recognition['color']; ?>;">
                    <?php echo $my_points; ?> <span>pts</span>
                </div>
                <div class="sp-rec-badge" style="background:<?php echo $recognition['color']; ?>20;color:<?php echo $recognition['color']; ?>;">
                    <?php echo $recognition['label']; ?>
                </div>
                <p><?php echo $recognition['desc']; ?></p>

                <!-- Progress bar -->
                <?php if ($next): ?>
                <div class="sp-progress-section">
                    <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-bottom:6px;">
                        <span><?php echo $my_points; ?> pts</span>
                        <span>Next: <strong><?php echo $next_label; ?></strong> (<?php echo $next; ?> pts)</span>
                    </div>
                    <div class="sp-progress-bar-bg">
                        <div class="sp-progress-fill" style="width:<?php echo $progress_pct; ?>%;background:<?php echo $recognition['color']; ?>;"></div>
                    </div>
                    <div style="font-size:12px;color:#94a3b8;margin-top:4px;"><?php echo $next - $my_points; ?> pts to next level</div>
                </div>
                <?php else: ?>
                    <div style="font-size:13px;font-weight:700;color:#f59e0b;margin-top:10px;">
                        <i class="fa-solid fa-trophy"></i> You've reached the highest level!
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rank Card -->
        <div class="sp-rank-card">
            <i class="fa-solid fa-ranking-star sp-rank-icon"></i>
            <div class="sp-rank-num">#<?php echo $my_rank; ?></div>
            <div class="sp-rank-label">Your Rank</div>
            <div class="sp-rank-sub">out of <?php echo $total_ranked; ?> students</div>
        </div>

        <!-- Stats Grid -->
        <div class="sp-stats-grid">
            <div class="sp-stat-item" style="border-left:4px solid #3b82f6;">
                <strong><?php echo $total_events_attended; ?></strong>
                <span>Events Attended</span>
            </div>
            <div class="sp-stat-item" style="border-left:4px solid #10b981;">
                <strong><?php echo $present_count; ?></strong>
                <span>Present</span>
            </div>
            <div class="sp-stat-item" style="border-left:4px solid #3b82f6;">
                <strong><?php echo $volunteer_count; ?></strong>
                <span>Volunteer</span>
            </div>
            <div class="sp-stat-item" style="border-left:4px solid #f59e0b;">
                <strong><?php echo $late_count; ?></strong>
                <span>Late</span>
            </div>
            <div class="sp-stat-item" style="border-left:4px solid #ef4444;">
                <strong><?php echo $absent_count; ?></strong>
                <span>Absent</span>
            </div>
            <div class="sp-stat-item" style="border-left:4px solid #a855f7;">
                <strong><?php echo $my_points; ?></strong>
                <span>Total Points</span>
            </div>
        </div>

    </div>

    <!-- POINTS TREND CHART + RECOGNITION GUIDE -->
    <div class="sp-mid-row">

        <div class="sp-card sp-chart-card">
            <h2><i class="fa-solid fa-chart-line"></i> My Points Trend</h2>
            <?php if (empty($trend_labels)): ?>
                <div class="sp-empty"><i class="fa-solid fa-chart-area"></i><p>No data yet — attend events to earn points!</p></div>
            <?php else: ?>
                <canvas id="trendChart" height="160"></canvas>
            <?php endif; ?>
        </div>

        <div class="sp-card sp-guide-card">
            <h2><i class="fa-solid fa-shield"></i> Recognition Guide</h2>
            <div class="sp-guide-list">
                <?php
                $levels = [
                    ['label'=>'Warning',       'range'=>'< 20 pts',  'color'=>'#ef4444','icon'=>'fa-triangle-exclamation','desc'=>'Reminder to participate'],
                    ['label'=>'Eligible',      'range'=>'20–49 pts', 'color'=>'#3b82f6','icon'=>'fa-bookmark',            'desc'=>'Participation certificate'],
                    ['label'=>'Active Student','range'=>'50–79 pts', 'color'=>'#10b981','icon'=>'fa-star',                'desc'=>'Active student award'],
                    ['label'=>'Outstanding',   'range'=>'80+ pts',   'color'=>'#f59e0b','icon'=>'fa-crown',               'desc'=>'Leadership award & priority'],
                ];
                foreach ($levels as $lvl):
                    $is_current = ($recognition['label'] === $lvl['label']);
                ?>
                <div class="sp-guide-item <?php echo $is_current ? 'sp-guide-current' : ''; ?>"
                     style="border-left:4px solid <?php echo $lvl['color']; ?>;">
                    <div class="sp-guide-icon" style="color:<?php echo $lvl['color']; ?>;">
                        <i class="fa-solid <?php echo $lvl['icon']; ?>"></i>
                    </div>
                    <div>
                        <strong><?php echo $lvl['label']; ?></strong>
                        <span class="sp-guide-range"><?php echo $lvl['range']; ?></span>
                        <small><?php echo $lvl['desc']; ?></small>
                    </div>
                    <?php if ($is_current): ?>
                        <span class="sp-you-badge">YOU</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- ATTENDANCE HISTORY TABLE -->
    <div class="sp-card" style="margin-top:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h2><i class="fa-solid fa-clock-rotate-left"></i> My Attendance History</h2>
            <span style="font-size:13px;color:#94a3b8;"><?php echo mysqli_num_rows($history_result); ?> record(s)</span>
        </div>

        <?php if (!mysqli_num_rows($history_result)): ?>
            <div class="sp-empty"><i class="fa-solid fa-calendar-xmark"></i><p>No attendance records yet. Start attending events!</p></div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="sp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Event Name</th>
                        <th>Club</th>
                        <th>Date</th>
                        <th>Check-in Time</th>
                        <th>Status</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $n = 1;
                    $status_styles = [
                        'present'   => ['bg'=>'#d1fae5','color'=>'#065f46'],
                        'late'      => ['bg'=>'#fef3c7','color'=>'#92400e'],
                        'absent'    => ['bg'=>'#fee2e2','color'=>'#991b1b'],
                        'volunteer' => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                    ];
                    while ($row = mysqli_fetch_assoc($history_result)):
                        $ss = $status_styles[$row['attendance_status']] ?? ['bg'=>'#f1f5f9','color'=>'#374151'];
                        $pts = (int)$row['points_awarded'];
                    ?>
                    <tr>
                        <td><?php echo $n++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['event_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['club_name'] ?? 'N/A'); ?></td>
                        <td><?php echo $row['attendance_date'] ?? '-'; ?></td>
                        <td><?php echo $row['check_in_time'] ?? '-'; ?></td>
                        <td>
                            <span style="padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;
                                background:<?php echo $ss['bg']; ?>;color:<?php echo $ss['color']; ?>;">
                                <?php echo ucfirst($row['attendance_status']); ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color:<?php echo $pts < 0 ? '#ef4444' : '#10b981'; ?>;">
                                <?php echo ($pts >= 0 ? '+' : '') . $pts; ?>
                            </strong>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- end main-content -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if (!empty($trend_labels)): ?>
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($trend_labels); ?>,
        datasets: [{
            label: 'Points Earned',
            data: <?php echo json_encode($trend_pts); ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.12)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#3b82f6',
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
<?php endif; ?>
</script>

<style>
.sp-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.sp-header h1{font-size:24px;font-weight:800;color:#1e293b;margin:0;display:flex;align-items:center;gap:10px;}
.sp-header p{font-size:14px;color:#64748b;margin:4px 0 0;}
.sp-top-row{display:grid;grid-template-columns:1.6fr 0.6fr 1fr;gap:20px;margin-bottom:24px;}
.sp-points-card{background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.05);
    display:flex;gap:18px;}
.sp-points-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;
    justify-content:center;font-size:26px;flex-shrink:0;}
.sp-points-body{flex:1;}
.sp-pts-number{font-size:40px;font-weight:900;line-height:1;}
.sp-pts-number span{font-size:18px;font-weight:600;}
.sp-rec-badge{display:inline-block;padding:4px 14px;border-radius:20px;font-size:13px;
    font-weight:700;margin:8px 0 6px;}
.sp-points-body p{font-size:13px;color:#64748b;margin:0 0 12px;}
.sp-progress-bar-bg{background:#e2e8f0;border-radius:6px;height:10px;overflow:hidden;}
.sp-progress-fill{height:100%;border-radius:6px;transition:width .5s ease;}
.sp-rank-card{background:linear-gradient(135deg,#3b82f6,#1d4ed8);border-radius:16px;padding:24px;
    display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;text-align:center;
    box-shadow:0 2px 10px rgba(59,130,246,.3);}
.sp-rank-icon{font-size:32px;margin-bottom:8px;opacity:.9;}
.sp-rank-num{font-size:48px;font-weight:900;line-height:1;}
.sp-rank-label{font-size:14px;font-weight:700;opacity:.9;}
.sp-rank-sub{font-size:12px;opacity:.75;margin-top:4px;}
.sp-stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.sp-stat-item{background:#fff;border-radius:12px;padding:14px 16px;
    box-shadow:0 2px 8px rgba(0,0,0,.05);}
.sp-stat-item strong{display:block;font-size:24px;font-weight:800;color:#1e293b;}
.sp-stat-item span{font-size:12px;color:#64748b;}
.sp-mid-row{display:grid;grid-template-columns:1.4fr 1fr;gap:20px;}
.sp-card{background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.05);}
.sp-card h2{font-size:16px;font-weight:700;color:#1e293b;margin-bottom:18px;display:flex;align-items:center;gap:10px;}
.sp-guide-list{display:flex;flex-direction:column;gap:10px;}
.sp-guide-item{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;background:#f8fafc;position:relative;}
.sp-guide-current{background:#fffbeb;border:1px solid #fde68a;}
.sp-guide-icon{font-size:18px;width:28px;text-align:center;}
.sp-guide-item strong{display:block;font-size:14px;color:#1e293b;}
.sp-guide-range{font-size:11px;font-weight:700;color:#64748b;display:block;}
.sp-guide-item small{font-size:11px;color:#94a3b8;}
.sp-you-badge{margin-left:auto;background:#f59e0b;color:#fff;padding:2px 8px;
    border-radius:10px;font-size:11px;font-weight:700;}
.sp-empty{text-align:center;padding:40px;color:#94a3b8;}
.sp-empty i{font-size:40px;display:block;margin-bottom:12px;}
.sp-table{width:100%;border-collapse:collapse;font-size:14px;}
.sp-table thead th{background:#f8fafc;padding:12px 14px;text-align:left;
    font-weight:700;color:#374151;font-size:13px;border-bottom:2px solid #e2e8f0;}
.sp-table tbody td{padding:11px 14px;border-bottom:1px solid #f1f5f9;color:#374151;}
.sp-table tbody tr:hover{background:#f8fafc;}
@media(max-width:1000px){.sp-top-row{grid-template-columns:1fr 1fr;}.sp-mid-row{grid-template-columns:1fr;}}
@media(max-width:600px){.sp-top-row{grid-template-columns:1fr;}}
</style>

<?php include '../Includes/footer.php'; ?>
