<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// DATABASE CONFIGURATION & INSERTION LOGIC
// ==========================================
$message = "";
$message_type = ""; // 'success' or 'error'

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_member'])) {
    
    // Replace these with your actual database credentials
    $host     = "localhost";
    $db_user  = "root";
    $db_pass  = "";
    $db_name  = "fk_student_club_event"; 

    $conn = new mysqli($host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        $message = "Database connection failed: " . $conn->connect_error;
        $message_type = "error";
    } else {
        // Sanitize and capture input values
        $fullname = trim($_POST['fullname']);
        $position = trim($_POST['position']);
        $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $phone    = trim($_POST['phone']);

        // Simple Backend Validation
        if (empty($fullname) || empty($position) || empty($email) || empty($phone)) {
            $message = "All fields are required.";
            $message_type = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
            $message_type = "error";
        } else {
            // Prepared statement to prevent SQL Injection
            $stmt = $conn->prepare("INSERT INTO committee_members (fullname, position, email, phone) VALUES (?, ?, ?, ?)");
            
            if ($stmt) {
                $stmt->bind_param("ssss", $fullname, $position, $email, $phone);
                
                if ($stmt->execute()) {
                    $message = "Committee member added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error executing query: " . $stmt->error;
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "Database preparation failed: " . $conn->error;
                $message_type = "error";
            }
        }
        $conn->close();
    }
}

$user_name = $_SESSION['full_name'] ?? 'Admin';
$user_role = 'Administrator';
?>

<title>Add Committee Member</title>

<?php include('../Includes/header_admin.php'); ?>
<?php include('../Includes/sidebar_admin.php'); ?>

<div class="main-content">

    <div class="page-header">
        <div class="page-left">
            <h1>Add Committee Member</h1>
            <p>Assign committee role for club management</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            <div class="form-grid">

                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        placeholder="Enter full name"
                        value="<?php echo isset($_POST['fullname']) && $message_type === 'error' ? htmlspecialchars($_POST['fullname']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="position">Position</label>
                    <select id="position" name="position" required>
                        <option value="">Select Position</option>
                        <option value="President" <?php echo (isset($_POST['position']) && $_POST['position'] === 'President' && $message_type === 'error') ? 'selected' : ''; ?>>President</option>
                        <option value="Vice President" <?php echo (isset($_POST['position']) && $_POST['position'] === 'Vice President' && $message_type === 'error') ? 'selected' : ''; ?>>Vice President</option>
                        <option value="Secretary" <?php echo (isset($_POST['position']) && $_POST['position'] === 'Secretary' && $message_type === 'error') ? 'selected' : ''; ?>>Secretary</option>
                        <option value="Treasurer" <?php echo (isset($_POST['position']) && $_POST['position'] === 'Treasurer' && $message_type === 'error') ? 'selected' : ''; ?>>Treasurer</option>
                        <option value="Committee Member" <?php echo (isset($_POST['position']) && $_POST['position'] === 'Committee Member' && $message_type === 'error') ? 'selected' : ''; ?>>Committee Member</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter email"
                        value="<?php echo isset($_POST['email']) && $message_type === 'error' ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        placeholder="Enter phone number"
                        value="<?php echo isset($_POST['phone']) && $message_type === 'error' ? htmlspecialchars($_POST['phone']) : ''; ?>"
                        required>
                </div>

                <div class="button-group">
                    <a href="manage_committee.php" class="cancel-btn">Cancel</a>
                    <button type="submit" name="save_member" class="save-btn">
                        <i class="fas fa-save"></i> Save Member
                    </button>
                </div>

            </div>

        </form>
    </div>

</div>

<?php include '../Includes/footer.php'; ?>