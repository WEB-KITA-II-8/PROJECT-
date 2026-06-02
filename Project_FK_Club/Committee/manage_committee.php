<?php
// =============================================
// MANAGE COMMITTEE PAGE
// FILE: admin/manage_committee.php
// =============================================
session_start();

$user_name = $_SESSION['full_name'] ?? 'Admin';
$user_role = 'Administrator';

// Dummy Data Example
$committeeMembers = [

    [
        'id' => 1,
        'name' => 'Muhammad Danish',
        'position' => 'President',
        'email' => 'danish@std.umpsa.edu.my',
        'phone' => '012-3456789'
    ],

    [
        'id' => 2,
        'name' => 'Aisyah Humaira',
        'position' => 'Vice President',
        'email' => 'aisyah@std.umpsa.edu.my',
        'phone' => '013-4567890'
    ],

    [
        'id' => 3,
        'name' => 'Irfan Hakimi',
        'position' => 'Secretary',
        'email' => 'irfan@std.umpsa.edu.my',
        'phone' => '014-5678901'
    ],

    [
        'id' => 4,
        'name' => 'Nur Izzati',
        'position' => 'Treasurer',
        'email' => 'izzati@std.umpsa.edu.my',
        'phone' => '015-6789012'
    ],

    [
        'id' => 5,
        'name' => 'Hakim Nazmi',
        'position' => 'Committee Member',
        'email' => 'hakim@std.umpsa.edu.my',
        'phone' => '017-7890123'
    ]

];
?>

<title>Manage Committee</title>

<?php include('../Includes/header_admin.php'); ?>
<?php include('../Includes/sidebar_admin.php'); ?>

<!-- TOPBAR -->
<div class="topbar">

    <div class="profile-menu">

        <button type="button" class="profile-btn" id="profileButton">

            <div class="profile-info">
                <span class="profile-name">
                    <?= htmlspecialchars($user_name); ?>
                </span>

                <span class="profile-role">
                    <?= $user_role; ?>
                </span>
            </div>

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

        </button>

        <div class="dropdown-content" id="profileDropdown">
            <a href="profile_admin.php"><i class="fa-solid fa-user"></i> Manage Profile</a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>

    </div>

</div>

<div class="main-content_comm">

<div class="committee-wrapper">

    <!-- PAGE HEADER -->

    <div class="page-header">
        <h1>
            Manage Committee
            </h1>
        <div>

            <p>
                Manage all committee members for club activities.
            </p>

        </div>

        <a href="dashboard_admin.php?page=addcommittee"
        class="add-member-btn">

            <i class="fas fa-plus"></i>
            Add Member

        </a>

    </div>

    <!-- FILTER -->

    <div class="filter-section">

        <div class="search-box">

            <i class="fas fa-search"></i>

            <input
            type="text"
            id="searchInput"
            placeholder="Search member name...">

        </div>

        <select
        id="roleFilter"
        class="role-filter">

            <option value="">
                All Roles
            </option>

            <option value="President">
                President
            </option>

            <option value="Vice President">
                Vice President
            </option>

            <option value="Secretary">
                Secretary
            </option>

            <option value="Treasurer">
                Treasurer
            </option>

            <option value="Committee Member">
                Committee Member
            </option>

        </select>

    </div>

    <!-- TABLE -->

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

            <?php foreach($committeeMembers as $index => $member){ ?>

                <tr
                data-name="<?= strtolower($member['name']); ?>"
                data-role="<?= $member['position']; ?>">

                    <td>
                        <?= $index + 1 ?>
                    </td>

                    <td>
                        <?= $member['name']; ?>
                    </td>

                    <td>
                        <?= $member['position']; ?>
                    </td>

                    <td>
                        <?= $member['email']; ?>
                    </td>

                    <td>
                        <?= $member['phone']; ?>
                    </td>

                    <td>

                        <div class="action-buttons">

                            <a href="#"
                            class="action-btn view-btn">

                                <i class="fas fa-eye"></i>

                            </a>

                            <a href="#"
                            class="action-btn edit-btn">

                                <i class="fas fa-pen"></i>

                            </a>

                            <button
                            class="action-btn delete-btn"

                            onclick="openDeleteModal(
                            <?= $member['id']; ?>,
                            '<?= $member['name']; ?>'
                            )">

                                <i class="fas fa-trash"></i>

                            </button>

                        </div>

                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

</div>

<!-- DELETE MODAL -->

<div id="deleteModal"
class="modal-overlay">

    <div class="delete-modal">

        <div class="warning-icon">

            <i class="fas fa-exclamation-triangle"></i>

        </div>

        <h3>
            Confirm Delete
        </h3>

        <p id="deleteText">
            Are you sure want to delete?
        </p>

        <div class="modal-buttons">

            <button
            class="cancel-btn"
            onclick="closeDeleteModal()">

                Cancel

            </button>

            <a id="deleteLink"
            href="#"
            class="confirm-delete-btn">

                Delete

            </a>

        </div>

    </div>

</div>

<script>

/* SEARCH */

const searchInput =
document.getElementById("searchInput");

const roleFilter =
document.getElementById("roleFilter");

searchInput.addEventListener(
"keyup",
filterTable
);

roleFilter.addEventListener(
"change",
filterTable
);

function filterTable(){

    let searchValue =
    searchInput.value.toLowerCase();

    let selectedRole =
    roleFilter.value;

    let rows =
    document.querySelectorAll(
    "#committeeTable tr"
    );

    rows.forEach(row => {

        let name =
        row.dataset.name;

        let role =
        row.dataset.role;

        let matchSearch =
        name.includes(searchValue);

        let matchRole =
        selectedRole === ""
        ||
        role === selectedRole;

        row.style.display =
        (
        matchSearch
        &&
        matchRole
        )
        ?
        ""
        :
        "none";
    });
}

/* DELETE MODAL */

function openDeleteModal(id,name){

document
.getElementById("deleteModal")
.style.display = "flex";

document
.getElementById("deleteText")
.innerHTML =

"Are you sure you want to delete <strong>"
+ name +
"</strong>?";

document
.getElementById("deleteLink")
.href =
"delete_committee.php?id=" + id;
}

function closeDeleteModal(){

document
.getElementById("deleteModal")
.style.display = "none";
}

</script>

<?php include('../Includes/footer.php'); ?>