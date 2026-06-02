<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$clubs = [
    ["id" => 1, "name" => "Robotics Club", "advisor" => "Dr. Ahmad", "members" => 45, "status" => "Active"],
    ["id" => 2, "name" => "Photography Club", "advisor" => "Pn. Sarah", "members" => 30, "status" => "Inactive"],
    ["id" => 3, "name" => "Coding Club", "advisor" => "Mr. Daniel", "members" => 60, "status" => "Active"]
];
?>

<?php include('../Includes/header_admin.php'); ?>
<?php include('../Includes/sidebar_admin.php'); ?>

<!-- MAIN CONTENT (MATCH USERS STYLE) -->
<div class="main-content">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h1>Manage Clubs</h1>

        <a href="addclubs.php" class="add-btn">
            <i class="fa-solid fa-plus"></i>
            Add Club
        </a>
    </div>

    <!-- SEARCH AREA (same as users style) -->
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

    <!-- TABLE -->
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
            <?php foreach($clubs as $index => $club){ ?>
                <tr>
                    <td><?= $index + 1 ?></td>

                    <td><?= htmlspecialchars($club['name']); ?></td>

                    <td><?= htmlspecialchars($club['advisor']); ?></td>

                    <td><?= $club['members']; ?></td>

                    <td>
                        <?php if($club['status'] == "Active"){ ?>
                            <span class="status-badge active">Active</span>
                        <?php } else { ?>
                            <span class="status-badge inactive">Inactive</span>
                        <?php } ?>
                    </td>

                    <td class="action-buttons">

                        <a href="#" class="edit-btn">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        <a href="#" class="delete-btn"
                           onclick="openDeleteModal(<?= $club['id']; ?>, '<?= addslashes($club['name']); ?>')">
                            <i class="fa-solid fa-trash"></i>
                        </a>

                    </td>
                </tr>
            <?php } ?>
            </tbody>

        </table>

    </div>

</div>

<!-- DELETE MODAL (same style as users page) -->
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

<!-- SCRIPT (YOUR ORIGINAL LOGIC KEPT) -->
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

// FILTER (YOUR ORIGINAL CLIENT-SIDE FILTER)
document.addEventListener("DOMContentLoaded", function(){

    const searchInput = document.querySelector('.search-box input');
    const statusFilter = document.querySelector('.status-dropdown');
    const tableRows = document.querySelectorAll('.club-table tbody tr');

    function filterTable(){
        const searchValue = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;

        tableRows.forEach(row => {

            const clubName = row.children[1].textContent.toLowerCase();
            const clubStatus = row.children[4].textContent.trim();

            const matchSearch = clubName.includes(searchValue);
            const matchStatus = selectedStatus === "All Status" || clubStatus === selectedStatus;

            row.style.display = (matchSearch && matchStatus) ? "" : "none";
        });
    }

    searchInput.addEventListener('keyup', filterTable);
    statusFilter.addEventListener('change', filterTable);

});

</script>

<?php include('../Includes/footer.php'); ?>