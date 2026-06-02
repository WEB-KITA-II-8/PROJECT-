<?php
// =============================================
// MANAGE EVENT ATTENDANCE
// FILE: manage_event_attendance.php
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
    header("Location: Project/index.php");
    exit();
}

// =============================================
// USER SESSION DATA
// =============================================
$user_id = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'admin';

// =============================================
// INITIALIZE EVENT ATTENDANCE TABLE IF NOT EXISTS
// =============================================
$create_table_query = "
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
        attendance_status ENUM('present', 'late', 'absent', 'volunteer') DEFAULT 'present',
        points_awarded INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )
";
mysqli_query($conn, $create_table_query);

// =============================================
// FETCH SUMMARY STATISTICS
// =============================================
$today = date('Y-m-d');

// Total Registered (today)
$total_registered = 0;
$new_today = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM event_attendance WHERE attendance_date = '$today'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_registered = $row['count'] ?? 0;
    $new_today = $total_registered;
}

// Present Count
$present_count = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM event_attendance WHERE attendance_date = '$today' AND attendance_status IN ('present', 'volunteer')");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $present_count = $row['count'] ?? 0;
}

// Calculate attendance percentage
$attendance_rate = $total_registered > 0 ? round(($present_count / $total_registered) * 100, 1) : 0;

// Late Arrivals
$late_count = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM event_attendance WHERE attendance_date = '$today' AND attendance_status = 'late'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $late_count = $row['count'] ?? 0;
}

// Absent
$absent_count = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM event_attendance WHERE attendance_date = '$today' AND attendance_status = 'absent'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $absent_count = $row['count'] ?? 0;
}

// =============================================
// HANDLE MARK ATTENDANCE
// =============================================
$mark_message = '';
$mark_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'mark_attendance') {
    $event_name = mysqli_real_escape_string($conn, $_POST['event_name'] ?? '');
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date'] ?? '');
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id'] ?? '');
    $attendance_status = mysqli_real_escape_string($conn, $_POST['attendance_status'] ?? 'present');
    $check_in_time = date('H:i:s');

    if (!empty($event_name) && !empty($event_date) && !empty($student_id)) {
        // Get student info
        $student_query = "SELECT user_id, full_name FROM users WHERE user_id = '$student_id' AND role = 'student'";
        $student_result = mysqli_query($conn, $student_query);

        if ($student_result && mysqli_num_rows($student_result) > 0) {
            $student = mysqli_fetch_assoc($student_result);
            $student_name = $student['full_name'];
            $user_id_found = $student['user_id'];

            // Get club info
            $club_query = "SELECT c.club_name FROM memberships m 
                          JOIN clubs c ON m.club_id = c.club_id 
                          WHERE m.user_id = '$user_id_found' LIMIT 1";
            $club_result = mysqli_query($conn, $club_query);
            $club_name = 'N/A';
            if ($club_result && mysqli_num_rows($club_result) > 0) {
                $club = mysqli_fetch_assoc($club_result);
                $club_name = $club['club_name'];
            }

            // Calculate points
            $points = 0;
            switch ($attendance_status) {
                case 'present':
                    $points = 10;
                    break;
                case 'late':
                    $points = 5;
                    break;
                case 'absent':
                    $points = -10;
                    break;
                case 'volunteer':
                    $points = 15;
                    break;
            }

            // Insert attendance record
            $insert_query = "INSERT INTO event_attendance 
                            (event_name, student_id, student_name, club_name, check_in_time, attendance_date, attendance_status, points_awarded, user_id)
                            VALUES ('$event_name', '$student_id', '$student_name', '$club_name', '$check_in_time', '$event_date', '$attendance_status', $points, $user_id_found)";

            if (mysqli_query($conn, $insert_query)) {
                $mark_message = "✓ Attendance marked successfully for $student_name";
                $mark_type = 'success';
                // Redirect to refresh the page and show updated list
                header("Location: manage_event_attendance.php");
                exit();
            } else {
                $mark_message = "✗ Error marking attendance: " . mysqli_error($conn);
                $mark_type = 'error';
            }
        } else {
            $mark_message = "✗ Student ID not found";
            $mark_type = 'error';
        }
    } else {
        $mark_message = "✗ Please fill all required fields";
        $mark_type = 'error';
    }
}

// =============================================
// HANDLE EDIT ATTENDANCE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_attendance') {
    $attendance_id = intval($_POST['attendance_id'] ?? 0);
    $attendance_status = mysqli_real_escape_string($conn, $_POST['attendance_status'] ?? 'present');

    if ($attendance_id > 0) {
        // Calculate new points
        $points = 0;
        switch ($attendance_status) {
            case 'present':
                $points = 10;
                break;
            case 'late':
                $points = 5;
                break;
            case 'absent':
                $points = -10;
                break;
            case 'volunteer':
                $points = 15;
                break;
        }

        $update_query = "UPDATE event_attendance SET attendance_status = '$attendance_status', points_awarded = $points WHERE attendance_id = $attendance_id";

        if (mysqli_query($conn, $update_query)) {
            $mark_message = "✓ Attendance updated successfully";
            $mark_type = 'success';
            header("Location: manage_event_attendance.php");
            exit();
        } else {
            $mark_message = "✗ Error updating attendance: " . mysqli_error($conn);
            $mark_type = 'error';
        }
    }
}

// =============================================
// HANDLE DELETE ATTENDANCE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_attendance') {
    $attendance_id = intval($_POST['attendance_id'] ?? 0);

    if ($attendance_id > 0) {
        $delete_query = "DELETE FROM event_attendance WHERE attendance_id = $attendance_id";

        if (mysqli_query($conn, $delete_query)) {
            $mark_message = "✓ Attendance record deleted successfully";
            $mark_type = 'success';
            header("Location: manage_event_attendance.php");
            exit();
        } else {
            $mark_message = "✗ Error deleting attendance: " . mysqli_error($conn);
            $mark_type = 'error';
        }
    }
}

// =============================================
// FETCH ATTENDANCE LIST
// =============================================
$search_query = $_GET['search'] ?? '';
$attendance_list = [];

$list_query = "SELECT * FROM event_attendance WHERE attendance_date = '$today'";

if (!empty($search_query)) {
    $search_query = mysqli_real_escape_string($conn, $search_query);
    $list_query .= " AND (student_id LIKE '%$search_query%' OR student_name LIKE '%$search_query%')";
}

$list_query .= " ORDER BY check_in_time DESC";

$result = mysqli_query($conn, $list_query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $attendance_list[] = $row;
    }
}

?>

<title>Manage Event Attendance</title>

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
        <h1>Manage Event Attendance</h1>
        <p>Record and manage student attendance for club events</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">

        <div class="summary-card">
            <div class="card-icon" style="background: #dbeafe;">
                <i class="fa-solid fa-users" style="color: #3b82f6;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $total_registered; ?></h3>
                <p>Total Registered</p>
                <span class="card-meta" style="color: #10b981;"><i class="fa-solid fa-arrow-up"></i> <?php echo $new_today; ?> new today</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon" style="background: #d1fae5;">
                <i class="fa-solid fa-check" style="color: #10b981;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $present_count; ?></h3>
                <p>Present</p>
                <span class="card-meta"><?php echo $attendance_rate; ?>% attendance</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon" style="background: #fed7aa;">
                <i class="fa-solid fa-clock" style="color: #f59e0b;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $late_count; ?></h3>
                <p>Late Arrivals</p>
                <span class="card-meta">+5 pts each</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon" style="background: #fee2e2;">
                <i class="fa-solid fa-xmark" style="color: #ef4444;"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $absent_count; ?></h3>
                <p>Absent (No Notice)</p>
                <span class="card-meta">-10 pts each</span>
            </div>
        </div>

    </div>

    <!-- QR Code Check-in Section -->
    <div class="checkin-section">

        <div class="qr-container">
            <div class="qr-code-box">
                <div class="qr-code-display" id="qrcode"></div>
                <p class="qr-label">
                    <i class="fa-solid fa-qrcode"></i> QR Code
                </p>
                <button class="download-btn" onclick="downloadQRCode()">
                    <i class="fa-solid fa-download"></i> Download QR
                </button>
            </div>
        </div>

        <div class="form-container">
            <h2>
                <i class="fa-solid fa-check-circle"></i>
                QR Code Check-in — Tech Talk 2025
            </h2>

            <?php if (!empty($mark_message)): ?>
                <div class="message <?php echo $mark_type; ?>">
                    <?php echo $mark_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="attendance-form">
                <input type="hidden" name="action" value="mark_attendance">

                <div class="form-group">
                    <label>Event Name</label>
                    <input type="text" name="event_name" placeholder="e.g. Tech Talk 2025" value="Tech Talk 2025" required>
                </div>

                <div class="form-group">
                    <label>Event Date</label>
                    <input type="date" name="event_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" placeholder="e.g. CB22029" required>
                </div>

                <div class="form-group">
                    <label>Attendance Status</label>
                    <select name="attendance_status" required>
                        <option value="present">Present on time (+10 pts)</option>
                        <option value="late">Late arrival (+5 pts)</option>
                        <option value="absent">Absent without notice (-10 pts)</option>
                        <option value="volunteer">Volunteer/Helper in event (+15 pts)</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-check"></i> Mark Attendance
                    </button>
                    <button type="reset" class="btn-secondary">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- Attendance List Section -->
    <div class="attendance-list-section">

        <div class="section-header">
            <h2>
                <i class="fa-solid fa-list"></i>
                Attendance List
            </h2>

            <div class="header-actions">
                <div class="search-box">
                    <form method="GET" class="search-form">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search student ID or name..." 
                            class="search-input"
                            value="<?php echo htmlspecialchars($search_query); ?>"
                        >
                    </form>
                </div>

                <button class="export-btn" onclick="exportToCSV()">
                    <i class="fa-solid fa-download"></i> Export
                </button>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="table-container">
            <table class="attendance-table" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Club</th>
                        <th>Check-in Time</th>
                        <th>Status</th>
                        <th>Points</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendance_list)): ?>
                        <?php foreach ($attendance_list as $record): ?>
                            <tr>
                                <td><?php echo $record['student_id']; ?></td>
                                <td><?php echo $record['student_name']; ?></td>
                                <td><?php echo $record['club_name']; ?></td>
                                <td><?php echo $record['check_in_time'] ?: '-'; ?></td>
                                <td>
                                    <span class="status-badge" style="background: <?php echo getStatusColor($record['attendance_status']); ?>20; color: <?php echo getStatusColor($record['attendance_status']); ?>;">
                                        <?php echo ucfirst(str_replace('_', ' ', $record['attendance_status'])); ?>
                                    </span>
                                </td>
                                <td class="points-cell" style="color: <?php echo $record['points_awarded'] >= 0 ? '#10b981' : '#ef4444'; ?>;">
                                    <strong><?php echo ($record['points_awarded'] >= 0 ? '+' : '') . $record['points_awarded']; ?></strong>
                                </td>
                                <td>
                                    <button type="button" class="edit-btn" data-id="<?php echo $record['attendance_id']; ?>" data-status="<?php echo $record['attendance_status']; ?>">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <button type="button" class="delete-btn" data-id="<?php echo $record['attendance_id']; ?>">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                                No attendance records found for today
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- =============================================
EDIT ATTENDANCE MODAL
============================================= -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Attendance</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" class="edit-form">
            <input type="hidden" name="action" value="edit_attendance">
            <input type="hidden" name="attendance_id" id="editAttendanceId" value="">

            <div class="form-group">
                <label>Attendance Status</label>
                <select name="attendance_status" id="editAttendanceStatus" required>
                    <option value="present">Present on time (+10 pts)</option>
                    <option value="late">Late arrival (+5 pts)</option>
                    <option value="absent">Absent without notice (-10 pts)</option>
                    <option value="volunteer">Volunteer/Helper in event (+15 pts)</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
                <button type="button" class="btn-secondary" onclick="closeEditModal()">
                    <i class="fa-solid fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =============================================
SCRIPTS
============================================= -->
<script>
// Generate QR Code
function generateQRCode() {
    document.getElementById('qrcode').innerHTML = '';
    const qrData = 'Event: Tech Talk 2025 | Date: ' + document.querySelector('input[name="event_date"]').value;
    new QRCode(document.getElementById('qrcode'), {
        text: qrData,
        width: 200,
        height: 200,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
}

// Download QR Code
function downloadQRCode() {
    const canvas = document.querySelector('#qrcode canvas');
    if (canvas) {
        const link = document.createElement('a');
        link.href = canvas.toDataURL();
        link.download = 'tech-talk-2025-qr.png';
        link.click();
    }
}

// Export to CSV
function exportToCSV() {
    const table = document.getElementById('attendanceTable');
    let csv = 'Student ID,Full Name,Club,Check-in Time,Status,Points\n';
    
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        if (row.querySelector('td:nth-child(7) button') !== null) { // Check if it's a data row
            const cells = row.querySelectorAll('td');
            const rowData = [
                cells[0].textContent.trim(),
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim(),
                cells[5].textContent.trim()
            ];
            csv += rowData.map(cell => '"' + cell + '"').join(',') + '\n';
        }
    });

    const link = document.createElement('a');
    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    link.download = 'attendance-' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
}

// Edit Attendance Modal
function openEditModal(attendanceId, status) {
    document.getElementById('editAttendanceId').value = attendanceId;
    document.getElementById('editAttendanceStatus').value = status;
    document.getElementById('editModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
}

// Delete Attendance
function deleteAttendance(attendanceId) {
    if (confirm('Are you sure you want to delete this attendance record? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_attendance">
            <input type="hidden" name="attendance_id" value="${attendanceId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Event listeners for edit and delete buttons
document.addEventListener('DOMContentLoaded', function() {
    // Edit button event listeners
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const attendanceId = this.getAttribute('data-id');
            const status = this.getAttribute('data-status');
            openEditModal(attendanceId, status);
        });
    });

    // Delete button event listeners
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const attendanceId = this.getAttribute('data-id');
            deleteAttendance(attendanceId);
        });
    });
});

// Toggle profile dropdown
function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('profileDropdown');
    const profileBtn = document.querySelector('.profile-btn');
    if (profileBtn && !profileBtn.contains(event.target)) {
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

// Generate QR code on page load
document.addEventListener('DOMContentLoaded', generateQRCode);

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.classList.remove('show');
    }
});
</script>

</body>
</html>

<?php
// =============================================
// HELPER FUNCTION - GET STATUS COLOR
// =============================================
function getStatusColor($status) {
    switch ($status) {
        case 'present':
            return '#10b981';
        case 'late':
            return '#f59e0b';
        case 'absent':
            return '#ef4444';
        case 'volunteer':
            return '#8b5cf6';
        default:
            return '#64748b';
    }
}
?>

<?php include '../Includes/footer.php'; ?>