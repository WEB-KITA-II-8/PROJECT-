<?php
// =============================================
// MANAGE COMMITTEE PAGE
// FILE: admin/manage_committee.php
// =============================================
session_start();

$user_name = $_SESSION['full_name'] ?? 'Admin';
$user_role = 'Administrator';

// 1. DATABASE CONNECTION CONFIGURATION
$host     = "localhost";
$db_user  = "root";
$db_pass  = "";
$db_name  = "fk_student_club_event"; 

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check if connection succeeded
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 2. FETCH DYNAMIC DATA FROM THE DATABASE
$query = "SELECT id, fullname, position, email, phone FROM committee_members ORDER BY id DESC";
$result = $conn->query($query);

$committeeMembers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $committeeMembers[] = $row;
    }
}
$conn->close();
?>

<title>Manage Committee</title>

<?php include('../Includes/header_admin.php'); ?>
<?php include('../Includes/sidebar_admin.php'); ?>

<div class="main-content">

<div class="committee-wrapper">

    <div class="page-header">

        <div>
            <h2>Committee Members</h2> 
            <p>Manage all committee members for club activities.</p>
        </div>

        <a href="addcommittee.php" class="add-member-btn">
            <i class="fas fa-plus"></i> Add Member
        </a>

    </div>

    <div class="filter-section">

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search member name...">
        </div>

        <select id="roleFilter" class="role-filter">
            <option value="">All Roles</option>
            <option value="President">President</option>
            <option value="Vice President">Vice President</option>
            <option value="Secretary">Secretary</option>
            <option value="Treasurer">Treasurer</option>
            <option value="Committee Member">Committee Member</option>
        </select>

    </div>

    <div class="committee-card">

        <table class="committee-table">

            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody id="committeeTable">

            <?php if (!empty($committeeMembers)){ ?>
                <?php foreach($committeeMembers as $index => $member){ ?>

                    <tr data-name="<?= strtolower(htmlspecialchars($member['fullname'])); ?>"
                        data-role="<?= htmlspecialchars($member['position']); ?>">

                        <td><?= $index + 1 ?></td>

                        <td><?= htmlspecialchars($member['fullname']); ?></td>

                        <td><?= htmlspecialchars($member['position']); ?></td>

                        <td><?= htmlspecialchars($member['email']); ?></td>

                        <td><?= htmlspecialchars($member['phone']); ?></td>

                        <td>
                            <div class="action-buttons">

                                <a href="view_committee.php?id=<?= $member['id']; ?>" class="action-btn view-btn">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="edit_committee.php?id=<?= $member['id']; ?>" class="action-btn edit-btn">
                                    <i class="fas fa-pen"></i>
                                </a>

                                <button class="action-btn delete-btn"
                                        onclick="openDeleteModal(<?= $member['id']; ?>, '<?= addslashes(htmlspecialchars($member['fullname'])); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>

                            </div>
                        </td>

                    </tr>

                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 25px;">
                        No committee members found in database.
                    </td>
                </tr>
            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

</div>

<div id="deleteModal" class="modal-overlay">

    <div class="delete-modal">

        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <h3>Confirm Delete</h3>
        <p id="deleteText">Are you sure want to delete?</p>

        <div class="modal-buttons">
            <button class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
            <a id="deleteLink" href="#" class="confirm-delete-btn">Delete</a>
        </div>

    </div>

</div>

<script>
/* SEARCH AND FILTER LOGIC */
const searchInput = document.getElementById("searchInput");
const roleFilter = document.getElementById("roleFilter");

if (searchInput && roleFilter) {
    searchInput.addEventListener("keyup", filterTable);
    roleFilter.addEventListener("change", filterTable);
}

function filterTable(){
    let searchValue = searchInput.value.toLowerCase();
    let selectedRole = roleFilter.value;
    let rows = document.querySelectorAll("#committeeTable tr");

    rows.forEach(row => {
        // Prevent filtering logic from breaking on empty message row layout
        if (!row.dataset.name) return;

        let name = row.dataset.name;
        let role = row.dataset.role;

        let matchSearch = name.includes(searchValue);
        let matchRole = selectedRole === "" || role === selectedRole;

        row.style.display = (matchSearch && matchRole) ? "" : "none";
    });
}

/* DELETE MODAL CONTROLS */
function openDeleteModal(id, name){
    document.getElementById("deleteModal").style.display = "flex";
    document.getElementById("deleteText").innerHTML = "Are you sure you want to delete <strong>" + name + "</strong>?";
    document.getElementById("deleteLink").href = "delete_committee.php?id=" + id;
}

function closeDeleteModal(){
    document.getElementById("deleteModal").style.display = "none";
}
</script>

<?php include '../Includes/footer.php'; ?>