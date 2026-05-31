<?php
// =============================================
// PARTICIPATION POINTS & RANKING
// FILE: participant_points.php
// =============================================

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include '../db_connect.php';

// =============================================
// SECURITY CHECK
// =============================================
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'committee'])) {
    header("Location: ../index.php");
    exit();
}

// =============================================
// USER SESSION DATA
// =============================================
$user_id = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'admin';

// =============================================
// FETCH STUDENTS WITH POINTS
// =============================================
$students = [];
$search_query = $_GET['search'] ?? '';

// Base query to fetch students
$query = "
    SELECT 
        u.user_id,
        u.full_name,
        u.email,
        COUNT(DISTINCT m.club_id) as clubs_joined,
        COALESCE(COUNT(DISTINCT m.club_id), 0) as events_attended,
        (COALESCE(COUNT(DISTINCT m.club_id), 0) * 10) + (COUNT(DISTINCT m.club_id) * 5) as total_points
    FROM users u
    LEFT JOIN memberships m ON u.user_id = m.user_id
    WHERE u.role = 'student'
";

// Add search filter if provided
if (!empty($search_query)) {
    $search_query = mysqli_real_escape_string($conn, $search_query);
    $query .= " AND (u.full_name LIKE '%$search_query%' OR u.user_id LIKE '%$search_query%')";
}

$query .= " GROUP BY u.user_id, u.full_name, u.email ORDER BY total_points DESC";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['recognition'] = getRecognitionLevel($row['total_points']);
        $students[] = $row;
    }
}

// =============================================
// RECOGNITION LEVEL FUNCTION
// =============================================
function getRecognitionLevel($points) {
    if ($points >= 80) return 'Outstanding';
    elseif ($points >= 50) return 'Active Student';
    elseif ($points >= 20) return 'Eligible';
    else return 'Warning';
}

// =============================================
// GET TOP STUDENTS (Top 4)
// =============================================
$top_students = array_slice($students, 0, 4);

// =============================================
// RECOGNITION LEVELS DATA
// =============================================
$recognition_levels = [
    [
        'level' => 'Warning',
        'icon' => 'fa-triangle-exclamation',
        'color' => '#ef4444',
        'range' => '< 20 points',
        'description' => 'Reminder to participate',
    ],
    [
        'level' => 'Eligible',
        'icon' => 'fa-bookmark',
        'color' => '#3b82f6',
        'range' => '20-49 points',
        'description' => 'Participation certificate',
    ],
    [
        'level' => 'Active Student',
        'icon' => 'fa-star',
        'color' => '#10b981',
        'range' => '50-79 points',
        'description' => 'Active student award',
    ],
    [
        'level' => 'Outstanding',
        'icon' => 'fa-crown',
        'color' => '#f59e0b',
        'range' => '80+ points',
        'description' => 'Leadership award',
    ],
];

?>

<title>Participation Points & Ranking</title>

<?php include '../Includes/header_admin.php'; ?>
<?php include '../Includes/sidebar_admin.php'; ?>

<!-- =============================================
TOPBAR
============================================= -->
<div class="topbar">

    <div class="profile-menu">

        <button class="profile-btn" onclick="toggleDropdown()">

            <div class="profile-info">
                <span class="profile-name">
                    <?php echo strtoupper($user_name); ?>
                </span>

                <span class="profile-role">
                    <?php echo ucfirst($user_role); ?>
                </span>
            </div>

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

        </button>

        <div class="dropdown-content" id="profileDropdown">

            <a href="#">
                <i class="fa-solid fa-user"></i>
                Manage Profile
            </a>

            <a href="Project/logout.php">
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

    <!-- Page Header -->
    <div class="page-header">
        <h1>Participation Points & Ranking</h1>
        <p>Student recognition levels based on accumulated points</p>
    </div>

    <!-- Recognition Levels & Top Students Section -->
    <div class="section-container">

        <!-- Recognition Levels -->
        <div class="recognition-section">
            <h2>
                <i class="fa-solid fa-shield"></i>
                Recognition Levels
            </h2>
            <div class="levels-grid">
                <?php foreach ($recognition_levels as $level): ?>
                    <div class="level-card" style="border-left: 4px solid <?php echo $level['color']; ?>;">
                        <div class="level-icon" style="color: <?php echo $level['color']; ?>;">
                            <i class="fa-solid <?php echo $level['icon']; ?>"></i>
                        </div>
                        <div class="level-content">
                            <h3><?php echo $level['level']; ?></h3>
                            <p class="level-range"><?php echo $level['range']; ?></p>
                            <p class="level-description"><?php echo $level['description']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Students This Semester -->
        <div class="top-students-section">
            <h2>
                <i class="fa-solid fa-chart-line"></i>
                Top Students This Semester
            </h2>
            <div class="ranking-list">
                <?php 
                $rank = 1;
                foreach ($top_students as $student): 
                    $recognition = getRecognitionLevel($student['total_points']);
                    $status_color = getStatusColor($recognition);
                ?>
                    <div class="ranking-item">
                        <div class="rank-number"><?php echo $rank; ?></div>
                        <div class="student-info">
                            <h4><?php echo $student['full_name']; ?></h4>
                            <span class="student-email"><?php echo $student['email']; ?></span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: <?php echo min(($student['total_points'] / 100) * 100, 100); ?>%; background: <?php echo $status_color; ?>;"></div>
                        </div>
                        <div class="points-info">
                            <span class="points"><?php echo $student['total_points']; ?> pts</span>
                            <span class="status-badge" style="background: <?php echo $status_color; ?>20; color: <?php echo $status_color; ?>;">
                                <?php echo $recognition; ?>
                            </span>
                        </div>
                    </div>
                <?php 
                $rank++;
                endforeach; 
                ?>
            </div>
        </div>

    </div>

    <!-- All Students - Points & Status -->
    <div class="all-students-section">

        <div class="section-header">
            <h2>
                <i class="fa-solid fa-users"></i>
                All Students — Points & Status
            </h2>
            
            <div class="search-box">
                <form method="GET" class="search-form">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search by name or ID..." 
                        class="search-input"
                        value="<?php echo htmlspecialchars($search_query); ?>"
                    >
                </form>
            </div>
        </div>

        <!-- Students Table -->
        <div class="table-container">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Clubs Joined</th>
                        <th>Events Attended</th>
                        <th>Total Points</th>
                        <th>Recognition</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <?php $recognition = getRecognitionLevel($student['total_points']); ?>
                            <tr>
                                <td><?php echo $student['user_id']; ?></td>
                                <td><?php echo $student['full_name']; ?></td>
                                <td><?php echo $student['clubs_joined']; ?></td>
                                <td><?php echo $student['events_attended']; ?></td>
                                <td class="points-cell" style="color: <?php echo getStatusColor($recognition); ?>;">
                                    <strong><?php echo $student['total_points']; ?> pts</strong>
                                </td>
                                <td>
                                    <span class="recognition-badge" style="background: <?php echo getStatusColor($recognition); ?>20; color: <?php echo getStatusColor($recognition); ?>;">
                                        <?php echo $recognition; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="view-btn" onclick="alert('View details for <?php echo $student['full_name']; ?>')">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                                No students found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- =============================================
SCRIPTS
============================================= -->
<script>
// Toggle profile dropdown
function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('profileDropdown');
    const profileBtn = document.querySelector('.profile-btn');
    if (!profileBtn.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Auto-submit search form
const searchInput = document.querySelector('.search-input');
if (searchInput) {
    searchInput.addEventListener('keyup', function() {
        document.querySelector('.search-form').submit();
    });
}
</script>

</body>
</html>

<?php
// =============================================
// HELPER FUNCTION - GET STATUS COLOR
// =============================================
function getStatusColor($recognition) {
    switch ($recognition) {
        case 'Outstanding':
            return '#f59e0b';
        case 'Active Student':
            return '#10b981';
        case 'Eligible':
            return '#3b82f6';
        case 'Warning':
            return '#ef4444';
        default:
            return '#64748b';
    }
}
?>
