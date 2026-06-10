<?php
// =============================================
// PARTICIPATION POINTS & RANKING
// FILE: Admin/participant_points.php
// MODULE 4 - Points, Rankings & Recognition
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

// =============================================
// SEARCH / FILTER
// =============================================
$search     = isset($_GET['search'])      ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$filter_rec = isset($_GET['recognition']) ? mysqli_real_escape_string($conn, $_GET['recognition'])  : 'all';

// =============================================
// FETCH ALL STUDENTS WITH REAL POINTS
// (JOIN users + event_attendance)
// =============================================
$having_clause = '';
if ($filter_rec === 'Outstanding')    $having_clause = 'HAVING total_points >= 80';
elseif ($filter_rec === 'Active')     $having_clause = 'HAVING total_points BETWEEN 50 AND 79';
elseif ($filter_rec === 'Eligible')   $having_clause = 'HAVING total_points BETWEEN 20 AND 49';
elseif ($filter_rec === 'Warning')    $having_clause = 'HAVING total_points < 20';

$search_clause = '';
if (!empty($search)) {
    $search_clause = "AND (u.full_name LIKE '%$search%' OR u.student_id LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$query = "
    SELECT
        u.user_id,
        u.student_id,
        u.full_name,
        u.email,
        COUNT(DISTINCT m.club_id)       AS clubs_joined,
        COUNT(DISTINCT ea.attendance_id) AS events_attended,
        COALESCE(SUM(ea.points_awarded), 0) AS total_points
    FROM users u
    LEFT JOIN memberships m       ON u.user_id = m.user_id AND m.membership_status = 'Active'
    LEFT JOIN event_attendance ea ON u.user_id = ea.user_id
    WHERE u.role = 'student'
    $search_clause
    GROUP BY u.user_id
    $having_clause
    ORDER BY total_points DESC
";

$result   = mysqli_query($conn, $query);
$students = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['recognition'] = getRecognitionLevel($row['total_points']);
    $students[] = $row;
}

// Top 4 for leaderboard
$top_students = array_slice($students, 0, 4);

// =============================================
// RECOGNITION LEVEL COUNTS (summary)
// =============================================
$count_result = mysqli_query($conn, "
    SELECT
        SUM(CASE WHEN pts < 20               THEN 1 ELSE 0 END) AS warning,
        SUM(CASE WHEN pts BETWEEN 20 AND 49  THEN 1 ELSE 0 END) AS eligible,
        SUM(CASE WHEN pts BETWEEN 50 AND 79  THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN pts >= 80              THEN 1 ELSE 0 END) AS outstanding
    FROM (
        SELECT u.user_id, COALESCE(SUM(ea.points_awarded),0) AS pts
        FROM users u
        LEFT JOIN event_attendance ea ON u.user_id = ea.user_id
        WHERE u.role='student'
        GROUP BY u.user_id
    ) sub
");
$counts = mysqli_fetch_assoc($count_result);

// =============================================
// HELPER FUNCTIONS
// =============================================
function getRecognitionLevel($points) {
    if ($points >= 80)     return ['label' => 'Outstanding',    'color' => '#f59e0b', 'icon' => 'fa-crown'];
    elseif ($points >= 50) return ['label' => 'Active Student', 'color' => '#10b981', 'icon' => 'fa-star'];
    elseif ($points >= 20) return ['label' => 'Eligible',       'color' => '#3b82f6', 'icon' => 'fa-bookmark'];
    else                   return ['label' => 'Warning',        'color' => '#ef4444', 'icon' => 'fa-triangle-exclamation'];
}

$recognition_levels = [
    ['level'=>'Warning',       'icon'=>'fa-triangle-exclamation','color'=>'#ef4444','range'=>'< 20 pts',   'description'=>'Reminder to participate more',       'count' => $counts['warning']     ?? 0],
    ['level'=>'Eligible',      'icon'=>'fa-bookmark',            'color'=>'#3b82f6','range'=>'20–49 pts',  'description'=>'Eligible for participation cert.',   'count' => $counts['eligible']    ?? 0],
    ['level'=>'Active Student','icon'=>'fa-star',                'color'=>'#10b981','range'=>'50–79 pts',  'description'=>'Eligible for active student award',  'count' => $counts['active']      ?? 0],
    ['level'=>'Outstanding',   'icon'=>'fa-crown',               'color'=>'#f59e0b','range'=>'80+ pts',    'description'=>'Leadership award & priority access', 'count' => $counts['outstanding'] ?? 0],
];
?>

<title>Participation Points & Ranking</title>

<?php include '../Includes/header_admin.php'; ?>
<?php include '../Includes/sidebar_admin.php'; ?>

<div class="main-content">

    <!-- PAGE HEADER -->
    <div class="pp-page-header">
        <div>
            <h1><i class="fa-solid fa-crown" style="color:#f59e0b;"></i> Participation Points & Ranking</h1>
            <p>Student recognition levels based on accumulated event attendance points</p>
        </div>
        <a href="participation_dashboard.php" class="pp-btn-back">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <!-- RECOGNITION LEVEL CARDS -->
    <div class="pp-levels-grid">
        <?php foreach ($recognition_levels as $lvl): ?>
        <div class="pp-level-card" style="border-top:4px solid <?php echo $lvl['color']; ?>;">
            <div class="pp-level-icon" style="background:<?php echo $lvl['color']; ?>20;color:<?php echo $lvl['color']; ?>;">
                <i class="fa-solid <?php echo $lvl['icon']; ?>"></i>
            </div>
            <div class="pp-level-body">
                <h3><?php echo $lvl['level']; ?></h3>
                <span class="pp-level-range"><?php echo $lvl['range']; ?></span>
                <p><?php echo $lvl['description']; ?></p>
                <div class="pp-level-count"><?php echo $lvl['count']; ?> students</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- TOP STUDENTS LEADERBOARD -->
    <div class="pp-section-row">

        <div class="pp-card pp-leaderboard">
            <h2><i class="fa-solid fa-trophy"></i> Top Students This Semester</h2>
            <?php if (empty($top_students)): ?>
                <div class="pp-empty"><i class="fa-solid fa-users"></i><p>No student data yet</p></div>
            <?php else: ?>
            <div class="pp-ranking-list">
                <?php foreach ($top_students as $i => $s):
                    $rec = $s['recognition'];
                    $medals = ['fa-medal','fa-medal','fa-medal','fa-medal'];
                    $medal_colors = ['#f59e0b','#94a3b8','#cd7c2a','#64748b'];
                ?>
                <div class="pp-rank-item">
                    <div class="pp-rank-num" style="color:<?php echo $medal_colors[$i]; ?>;">
                        <i class="fa-solid <?php echo $medals[$i]; ?>"></i>
                        <?php echo $i+1; ?>
                    </div>
                    <div class="pp-rank-info">
                        <strong><?php echo htmlspecialchars($s['full_name']); ?></strong>
                        <span><?php echo htmlspecialchars($s['student_id'] ?? '-'); ?></span>
                    </div>
                    <div class="pp-rank-progress">
                        <div class="pp-progress-bar">
                            <div style="width:<?php echo min(($s['total_points']/100)*100, 100); ?>%;background:<?php echo $rec['color']; ?>;height:100%;border-radius:4px;"></div>
                        </div>
                    </div>
                    <div class="pp-rank-pts">
                        <strong style="color:<?php echo $rec['color']; ?>;"><?php echo $s['total_points']; ?></strong>
                        <small>pts</small>
                    </div>
                    <span class="pp-badge" style="background:<?php echo $rec['color']; ?>20;color:<?php echo $rec['color']; ?>;">
                        <?php echo $rec['label']; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Points Chart -->
        <div class="pp-card pp-chart-card">
            <h2><i class="fa-solid fa-chart-pie"></i> Points Distribution</h2>

            <div class="pp-chart-container">
            <canvas id="pointsDistChart"></canvas>
            </div>
        </div>

    </div>

    <!-- ALL STUDENTS TABLE -->
    <div class="pp-card" style="margin-top:24px;">
        <div class="pp-table-header">
            <h2><i class="fa-solid fa-users"></i> All Students — Points & Status</h2>
            <div class="pp-table-controls">
                <!-- Search -->
                <form method="GET" style="display:flex;gap:8px;align-items:center;">
                    <div class="pp-search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" placeholder="Search name / ID..."
                               value="<?php echo htmlspecialchars($search); ?>" class="pp-search-input">
                    </div>
                    <!-- Recognition filter -->
                    <select name="recognition" class="pp-filter-select" onchange="this.form.submit()">
                        <option value="all">All Levels</option>
                        <option value="Warning"      <?php echo $filter_rec==='Warning'      ? 'selected':'' ?>>Warning</option>
                        <option value="Eligible"     <?php echo $filter_rec==='Eligible'     ? 'selected':'' ?>>Eligible</option>
                        <option value="Active"       <?php echo $filter_rec==='Active'       ? 'selected':'' ?>>Active Student</option>
                        <option value="Outstanding"  <?php echo $filter_rec==='Outstanding'  ? 'selected':'' ?>>Outstanding</option>
                    </select>
                    <button type="submit" class="pp-search-btn"><i class="fa-solid fa-search"></i></button>
                    <?php if (!empty($search) || $filter_rec !== 'all'): ?>
                        <a href="participant_points.php" class="pp-clear-btn">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="pp-table-wrap">
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Clubs</th>
                        <th>Events</th>
                        <th>Total Points</th>
                        <th>Recognition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:#94a3b8;">No students found</td></tr>
                    <?php else: ?>
                    <?php foreach ($students as $i => $s):
                        $rec = $s['recognition'];
                    ?>
                    <tr>
                        <td><strong><?php echo $i+1; ?></strong></td>
                        <td><?php echo htmlspecialchars($s['student_id'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($s['email']); ?></td>
                        <td><?php echo $s['clubs_joined']; ?></td>
                        <td><?php echo $s['events_attended']; ?></td>
                        <td><strong style="color:<?php echo $rec['color']; ?>;"><?php echo $s['total_points']; ?> pts</strong></td>
                        <td>
                            <span class="pp-badge" style="background:<?php echo $rec['color']; ?>20;color:<?php echo $rec['color']; ?>;">
                                <i class="fa-solid <?php echo $rec['icon']; ?>"></i>
                                <?php echo $rec['label']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pp-table-footer">
            Showing <?php echo count($students); ?> student(s)
        </div>
    </div>

</div><!-- end main-content -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const distCtx = document.getElementById('pointsDistChart').getContext('2d');
new Chart(distCtx, {
    type: 'doughnut',
    data: {
        labels: ['Warning (<20)', 'Eligible (20-49)', 'Active (50-79)', 'Outstanding (80+)'],
        datasets: [{
            data: [
                <?php echo (int)($counts['warning']     ?? 0); ?>,
                <?php echo (int)($counts['eligible']    ?? 0); ?>,
                <?php echo (int)($counts['active']      ?? 0); ?>,
                <?php echo (int)($counts['outstanding'] ?? 0); ?>
            ],
            backgroundColor: ['#ef4444','#3b82f6','#10b981','#f59e0b'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 14 } }
        }
    }
});
</script>

<style>
.pp-page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.pp-page-header h1{font-size:24px;font-weight:800;color:#1e293b;margin:0;display:flex;align-items:center;gap:10px;}
.pp-page-header p{font-size:14px;color:#64748b;margin:4px 0 0;}
.pp-btn-back{display:inline-flex;align-items:center;gap:8px;background:#fff;color:#374151;
    border:1.5px solid #e2e8f0;padding:10px 18px;border-radius:12px;text-decoration:none;
    font-size:14px;font-weight:600;transition:all .2s;}
.pp-btn-back:hover{border-color:#3b82f6;color:#3b82f6;}
.pp-levels-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:24px;}
.pp-level-card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 10px rgba(0,0,0,.05);
    display:flex;gap:16px;align-items:flex-start;}
.pp-level-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;
    justify-content:center;font-size:20px;flex-shrink:0;}
.pp-level-body h3{font-size:15px;font-weight:700;color:#1e293b;margin:0 0 2px;}
.pp-level-range{font-size:12px;font-weight:700;color:#64748b;}
.pp-level-body p{font-size:12px;color:#94a3b8;margin:4px 0 8px;}
.pp-level-count{font-size:22px;font-weight:800;color:#1e293b;}
.pp-section-row{display:grid;grid-template-columns:1.4fr 1fr;gap:20px;margin-bottom:0;}
.pp-card h2{font-size:16px;font-weight:700;color:#1e293b;margin-bottom:18px;display:flex;align-items:center;gap:10px;}
.pp-chart-card{
    background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.05);
    display:flex;
    flex-direction:column;
    align-items:center;
}

.pp-chart-container{
    width:100%;
    max-width:425px;   /* adjust size here */
    height:425px;
    margin:auto;
}
.pp-ranking-list{display:flex;flex-direction:column;gap:12px;}
.pp-rank-item{display:flex;align-items:center;gap:12px;padding:12px;border-radius:12px;
    background:#f8fafc;border:1px solid #f1f5f9;}
.pp-rank-num{display:flex;align-items:center;gap:4px;font-weight:700;font-size:15px;width:36px;}
.pp-rank-info{flex:1;}
.pp-rank-info strong{display:block;font-size:14px;color:#1e293b;}
.pp-rank-info span{font-size:12px;color:#64748b;}
.pp-rank-progress{flex:1;max-width:120px;}
.pp-progress-bar{background:#e2e8f0;border-radius:4px;height:8px;overflow:hidden;}
.pp-rank-pts{text-align:right;min-width:50px;}
.pp-rank-pts strong{font-size:18px;display:block;}
.pp-rank-pts small{font-size:11px;color:#64748b;}
.pp-badge{padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap;}
.pp-empty{text-align:center;padding:40px;color:#94a3b8;}
.pp-empty i{font-size:40px;display:block;margin-bottom:12px;}
.pp-table-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;}
.pp-table-header h2{margin:0;font-size:16px;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:10px;}
.pp-table-controls{display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.pp-search-box{display:flex;align-items:center;gap:8px;border:1.5px solid #e2e8f0;
    border-radius:10px;padding:8px 14px;background:#f8fafc;}
.pp-search-box i{color:#94a3b8;}
.pp-search-input{border:none;background:transparent;outline:none;font-size:14px;width:180px;color:#1e293b;}
.pp-filter-select{padding:8px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;background:#f8fafc;color:#1e293b;}
.pp-search-btn{padding:8px 14px;background:#3b82f6;color:#fff;border:none;border-radius:10px;cursor:pointer;}
.pp-clear-btn{padding:8px 12px;background:#fee2e2;color:#dc2626;border-radius:10px;
    text-decoration:none;font-size:13px;font-weight:600;}
.pp-table-wrap{overflow-x:auto;}
.pp-table{width:100%;border-collapse:collapse;font-size:14px;}
.pp-table thead th{background:#f8fafc;padding:12px 16px;text-align:left;
    font-weight:700;color:#374151;font-size:13px;border-bottom:2px solid #e2e8f0;}
.pp-table tbody td{padding:12px 16px;border-bottom:1px solid #f1f5f9;color:#374151;}
.pp-table tbody tr:hover{background:#f8fafc;}
.pp-table-footer{text-align:right;font-size:13px;color:#94a3b8;margin-top:12px;}
@media(max-width:1100px){.pp-levels-grid{grid-template-columns:repeat(2,1fr);}.pp-section-row{grid-template-columns:1fr;}}
@media(max-width:600px){.pp-levels-grid{grid-template-columns:1fr;}}
</style>

<?php include '../Includes/footer.php'; ?>
