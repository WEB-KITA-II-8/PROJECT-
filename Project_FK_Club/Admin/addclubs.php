<?php

session_start();
include '../db_connect.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $club_name   = mysqli_real_escape_string($conn, $_POST['club_name']);
    $advisor     = mysqli_real_escape_string($conn, $_POST['advisor']);
    $members     = mysqli_real_escape_string($conn, $_POST['members']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "INSERT INTO clubs_comm
            (club_name, advisor_name, total_members, status, description)
            VALUES
            ('$club_name', '$advisor', '$members', '$status', '$description')";

    if (mysqli_query($conn, $sql)) {
        $message = "<div class='success-msg'>Club added successfully!</div>";
    } else {
        $message = "<div class='error-msg'>Failed to add club.</div>";
    }
}
?>

<title>Add Club</title>

<?php include('../Includes/header_admin.php'); ?>
<?php include('../Includes/sidebar_admin.php'); ?>

<!-- ✅ SAME DASHBOARD WRAPPER -->
<div class="main-content">

    <!-- SAME HEADER STYLE AS USERS PAGE -->
    <div class="page-header">

        <h1>Add New Club</h1>

        <a href="manage_clubs.php" class="add-btn">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>

    </div>

    <!-- DESCRIPTION (same style as other pages) -->
    <p style="margin-bottom:20px; color:#64748b;">
        Create and register a new student club.
    </p>

    <!-- MESSAGE -->
    <?= $message; ?>

    <!-- ✅ USE SAME CARD STYLE AS TABLES -->
    <div class="table-container">

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label>Club Name</label>
                    <input type="text" name="club_name" required>
                </div>

                <div class="form-group">
                    <label>Advisor Name</label>
                    <input type="text" name="advisor" required>
                </div>

                <div class="form-group">
                    <label>Total Members</label>
                    <input type="number" name="members" required>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label>Club Description</label>
                    <textarea name="description" required></textarea>
                </div>

            </div>

            <!-- ACTION BUTTONS (same style pattern) -->
            <div class="form-actions">

                <a href="manage_clubs.php" class="cancel-btn">
                    Cancel
                </a>

                <button type="submit" class="add-btn">
                    <i class="fas fa-save"></i>
                    Save Club
                </button>

            </div>

        </form>

    </div>

</div>

<!-- ONLY MINIMAL STYLES (MATCH DASHBOARD) -->
<style>

/* FORM GRID (consistent with admin system) */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* FORM FIELDS */
.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 600;
    color: #1e3a5f;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px;
    border: 1px solid #dbe2ea;
    border-radius: 10px;
    font-size: 15px;
    outline: none;
}

.form-group textarea {
    min-height: 120px;
    resize: none;
}

.full-width {
    grid-column: 1 / -1;
}

/* BUTTON AREA (MATCH TABLE ACTION STYLE) */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 25px;
}

/* CANCEL */
.cancel-btn {
    background: #e5e7eb;
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
    color: #111827;
    font-weight: 600;
}

/* SAVE (reuse add-btn style) */
.add-btn {
    background: #2563eb;
    color: white;
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
}

.add-btn:hover {
    background: #1d4ed8;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
}

</style>

<?php include('../Includes/footer.php'); ?>