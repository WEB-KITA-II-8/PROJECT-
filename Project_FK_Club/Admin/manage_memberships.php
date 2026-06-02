<?php
// =============================================
// ADMIN MANAGE MEMBERSHIPS
// FILE: admin/manage_memberships.php
// =============================================

// Start session
session_start();

// =============================================
// DATABASE CONNECTION
// =============================================
include '../db_connect.php';

// =============================================
// SEARCH FUNCTION
// =============================================
$search = "";
$whereClause = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {

    $search = mysqli_real_escape_string($conn, trim($_GET['search']));

    $whereClause = "WHERE users.full_name LIKE '%$search%'
                    OR users.student_id LIKE '%$search%'
                    OR clubs.club_name LIKE '%$search%'
                    OR memberships.membership_type LIKE '%$search%'
                    OR memberships.committee_role LIKE '%$search%'";
}

// =============================================
// FETCH MEMBERSHIP DATA
// =============================================
$query = "
    SELECT memberships.*,
           users.full_name,
           users.student_id,
           users.email,
           clubs.club_name
    FROM memberships
    JOIN users ON memberships.user_id = users.user_id
    JOIN clubs ON memberships.club_id = clubs.club_id
    $whereClause
    ORDER BY memberships.joined_date DESC
";

$result = mysqli_query($conn, $query);

?>

<title>

<?php echo isset($pageTitle)
? $pageTitle . ' | Manage Memberships'
: 'Manage Memberships'; ?>

</title>

<?php include '../Includes/header_admin.php'; ?>    

<?php include '../Includes/sidebar_admin.php'; ?> 

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Header -->
    <div class="page-header">
        <h1>Manage Memberships</h1>

        <a href="add_membership.php" class="add-btn">
            <i class="fa-solid fa-user-plus"></i>
            Add Membership
        </a>
    </div>

    <!-- SEARCH -->
    <form method="GET" class="search-form">

        <input type="text"
               name="search"
               placeholder="     Search student, club, role..."
               value="<?php echo htmlspecialchars($search); ?>">

        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
            Search
        </button>

    </form>

    <!-- TABLE -->
<div class="table-container">

    <table>

        <!-- TABLE HEADER -->
        <thead>
            <tr>
                <th>ID</th>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Club</th>
                <th>Membership Type</th>
                <th>Committee Role</th>
                <th>Joined Date</th>
                <th>Action</th>
            </tr>
        </thead>

        <!-- TABLE BODY -->
        <tbody>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>

            <?php
            // =============================================
            // DISPLAY NUMBER COUNTER
            // =============================================
            $counter = 1;

            while ($row = mysqli_fetch_assoc($result)):
            ?>

                <tr>

                    <!-- ID -->
                    <td><?php echo $counter++; ?></td>

                    <!-- STUDENT ID -->
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>

                    <!-- FULL NAME -->
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>

                    <!-- CLUB -->
                    <td><?php echo htmlspecialchars($row['club_name']); ?></td>

                    <!-- MEMBERSHIP TYPE -->
                    <td>
                        <span class="membership-badge">
                            <?php echo ucfirst($row['membership_type']); ?>
                        </span>
                    </td>

                    <!-- COMMITTEE ROLE -->
                    <td>
                        <?php
                        echo !empty($row['committee_role'])
                            ? htmlspecialchars($row['committee_role'])
                            : '-';
                        ?>
                    </td>

                    <!-- JOINED DATE -->
                    <td>
                        <?php echo date('d M Y', strtotime($row['joined_date'])); ?>
                    </td>

                    <!-- ACTION -->
                    <td class="action-buttons">

                        <!-- EDIT -->
                        <a href="edit_membership.php?id=<?php echo $row['membership_id']; ?>"
                           class="edit-btn"
                           title="Edit Membership">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        <!-- DELETE -->
                        <a href="#"
                           class="delete-btn"
                           title="Delete Membership"
                           onclick="openDeleteModal(<?php echo $row['membership_id']; ?>)">
                            <i class="fa-solid fa-trash"></i>
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <!-- NO DATA -->
            <tr>
                <td colspan="8" class="no-data">
                    No memberships found.
                </td>
            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

<!-- =============================================
     DELETE MODAL
============================================= -->
<div id="deleteModal" class="custom-modal">
    <div class="custom-modal-content">

        <div class="modal-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h3>Delete Membership?</h3>
        <p>This action cannot be undone.</p>

        <div class="modal-buttons">
            <button class="cancel-modal-btn" onclick="closeDeleteModal()">
                Cancel
            </button>

            <a id="confirmDeleteBtn" href="#" class="confirm-modal-btn">
                Delete
            </a>
        </div>

    </div>
</div>

<!-- =============================================
     SCRIPTS
============================================= -->
<script>
// OPEN DELETE MODAL
function openDeleteModal(id) {
    document.getElementById("deleteModal").style.display = "flex";
    document.getElementById("confirmDeleteBtn").href =
        "delete_membership.php?id=" + id;
}

// CLOSE DELETE MODAL
function closeDeleteModal() {
    document.getElementById("deleteModal").style.display = "none";
}
</script>

<?php include '../Includes/footer.php'; ?>