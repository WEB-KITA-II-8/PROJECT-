<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Include your database connection file
include '../db_connect.php';

// 2. Fetch the club data dynamically from the database
$query = "SELECT id, club_name, advisor_name, total_members, status FROM clubs_comm ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$clubs = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $clubs[] = $row;
    }
} else {
    // Optional error handler if the query fails
    $db_error = "Error fetching clubs: " . mysqli_error($conn);
}
?>

<?php include('../Includes/header_admin.php'); ?>
<?php include('../Includes/sidebar_admin.php'); ?>

<div class="main-content">

    <div class="page-header">
        <h1>Manage Clubs</h1>

        <a href="addclubs.php" class="add-btn">
            <i class="fa-solid fa-plus"></i>
            Add Club
        </a>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="error-msg" style="color: red; margin-bottom: 15px;"><?= $db_error; ?></div>
    <?php endif; ?>

    <div class="search-form">

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Search club name...">
        </div>

        <select class="status-dropdown">
            <option>All Status</option>
            <option>Active</option>
            <option>Inactive</option>
        </select>

    </div>

    <div class="table-container">

        <table class="club-table">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Club Name</th>
                    <th>Advisor</th>
                    <th>Members</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            <?php if (!empty($clubs)){ ?>
                <?php foreach($clubs as $index => $club){ ?>
                    <tr>
                        <td><?= $index + 1 ?></td>

                        <td><?= htmlspecialchars($club['club_name']); ?></td>

                        <td><?= htmlspecialchars($club['advisor_name']); ?></td>

                        <td><?= htmlspecialchars($club['total_members']); ?></td>

                        <td>
                            <?php if($club['status'] == "Active"){ ?>
                                <span class="status-badge active">Active</span>
                            <?php } else { ?>
                                <span class="status-badge inactive">Inactive</span>
                            <?php } ?>
                        </td>

                        <td class="action-buttons">

                            <a href="edit_club.php?id=<?= $club['id']; ?>" class="edit-btn">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <a href="#" class="delete-btn"
                               onclick="openDeleteModal(<?= $club['id']; ?>, '<?= addslashes($club['club_name']); ?>')">
                                <i class="fa-solid fa-trash"></i>
                            </a>

                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 20px;">
                        No clubs found. Click "Add Club" to create one!
                    </td>
                </tr>
            <?php } ?>
            </tbody>

        </table>

    </div>

</div>

<div id="deleteModal" class="custom-modal">

    <div class="custom-modal-content">

        <div class="modal-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h3>Confirm Delete</h3>

        <p id="deleteText">Are you sure?</p>

        <div class="modal-buttons">

            <button class="cancel-modal-btn" onclick="closeDeleteModal()">
                Cancel
            </button>

            <a id="deleteLink" href="#" class="confirm-modal-btn">
                Delete
            </a>

        </div>

    </div>

</div>

<script>

// DELETE MODAL
function openDeleteModal(id, clubName){
    document.getElementById("deleteModal").style.display = "flex";
    document.getElementById("deleteText").innerHTML =
        "Are you sure you want to delete <strong>" + clubName + "</strong>?";

    document.getElementById("deleteLink").href =
        "delete_club.php?id=" + id;
}

function closeDeleteModal(){
    document.getElementById("deleteModal").style.display = "none";
}

// FILTER (Your original client-side filter still works perfectly)
document.addEventListener("DOMContentLoaded", function(){

    const searchInput = document.querySelector('.search-box input');
    const statusFilter = document.querySelector('.status-dropdown');
    const tableRows = document.querySelectorAll('.club-table tbody tr');

    function filterTable(){
        const searchValue = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;

        tableRows.forEach(row => {
            // Skips processing if the row displays the "No clubs found" text 
            if(row.children.length < 6) return; 

            const clubName = row.children[1].textContent.toLowerCase();
            const clubStatus = row.children[4].textContent.trim();

            const matchSearch = clubName.includes(searchValue);
            const matchStatus = selectedStatus === "All Status" || clubStatus === selectedStatus;

            row.style.display = (matchSearch && matchStatus) ? "" : "none";
        });
    }

    if(searchInput && statusFilter) {
        searchInput.addEventListener('keyup', filterTable);
        statusFilter.addEventListener('change', filterTable);
    }
});

</script>

<?php include('../Includes/footer.php'); ?>