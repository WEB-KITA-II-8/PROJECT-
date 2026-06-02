<?php
/*=============================================
REGISTERED STUDENT MEMBERSHIP MANAGEMENT
FILE: student/membership_student.php
=============================================*/

session_start();
include("../db_connect.php");

$user_id   = $_SESSION['user_id'];

/* FETCH STUDENT DATA */
$userQuery = mysqli_query($conn, "
    SELECT * FROM users
    WHERE user_id = '$user_id'
    LIMIT 1
");
$user = mysqli_fetch_assoc($userQuery);

/* HANDLE JOIN CLUB */
if (isset($_POST['join_club'])) {
    $club_id = mysqli_real_escape_string($conn, $_POST['club_id']);

    $checkMembership = mysqli_query($conn, "
        SELECT * FROM memberships
        WHERE user_id = '$user_id'
        AND club_id = '$club_id'
        AND membership_status = 'Active'
    ");

    if (mysqli_num_rows($checkMembership) > 0) {
        header("Location: membership_student.php?exists=1");
        exit();
    }

    $insertMembership = mysqli_query($conn, "
        INSERT INTO memberships (
            user_id,
            club_id,
            membership_type,
            committee_role,
            joined_date,
            membership_status
        ) VALUES (
            '$user_id',
            '$club_id',
            'Student',
            NULL,
            NOW(),
            'Active'
        )
    ");

    if ($insertMembership) {
        header("Location: membership_student.php?joined=1");
        exit();
    }
}

/* HANDLE LEAVE CLUB */
if (isset($_POST['leave_membership'])) {
    $membership_id = mysqli_real_escape_string($conn, $_POST['membership_id']);

    mysqli_query($conn, "
        UPDATE memberships
        SET membership_status = 'Inactive'
        WHERE membership_id = '$membership_id'
        AND user_id = '$user_id'
    ");

    header("Location: membership_student.php?left=1");
    exit();
}

/* FETCH ACTIVE MEMBERSHIP */
$membershipQuery = mysqli_query($conn, "
    SELECT memberships.*, clubs.club_name, clubs.description, clubs.advisor_name
    FROM memberships
    JOIN clubs ON memberships.club_id = clubs.club_id
    WHERE memberships.user_id = '$user_id'
    AND memberships.membership_status = 'Active'
");

/* FETCH AVAILABLE CLUBS */
$clubsQuery = mysqli_query($conn, "
    SELECT * FROM clubs
    WHERE status = 'active' OR status = 'Active'
");
?>

<title>Student Membership</title>

<?php include('../Includes/header_stud.php'); ?>
<?php include('../Includes/sidebar_stud.php'); ?>

<div class="main-content">
    <h1>Manage Membership</h1>

    <div class="membership-overview-card">
        <h2><i class="fa-solid fa-user-group"></i> My Membership</h2>

        <?php if (mysqli_num_rows($membershipQuery) > 0): ?>
            <?php while ($membership = mysqli_fetch_assoc($membershipQuery)): ?>
                <div class="membership-item">
                    <div class="membership-details">
                        <h3><?php echo htmlspecialchars($membership['club_name']); ?></h3>
                        <p><strong>Membership Type:</strong> <?php echo htmlspecialchars($membership['membership_type']); ?></p>
                        <p><strong>Advisor:</strong> <?php echo htmlspecialchars($membership['advisor_name']); ?></p>
                        <p><strong>Joined Date:</strong> <?php echo htmlspecialchars($membership['joined_date']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($membership['membership_status']); ?></p>
                    </div>
                    <button type="button" class="leave-btn"
                        onclick="showLeavePopup('<?php echo $membership['membership_id']; ?>','<?php echo htmlspecialchars($membership['club_name']); ?>')">
                        Leave Club
                    </button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-message">You have not joined any club yet.</p>
        <?php endif; ?>
    </div>

    <div class="available-clubs-card">
        <div class="section-header">
            <h2><i class="fa-solid fa-layer-group"></i> Available Clubs</h2>
            <div class="search-bar">
                <input type="text" id="clubSearch" placeholder="Search club...">
                <button type="button" id="searchBtn"><i class="fa-solid fa-search"></i></button>
            </div>
        </div>

        <div class="clubs-grid" id="clubsContainer">
            <?php while ($club = mysqli_fetch_assoc($clubsQuery)): ?>
                <div class="club-card">
                    <h3><?php echo htmlspecialchars($club['club_name']); ?></h3>
                    <p><?php echo htmlspecialchars($club['description']); ?></p>
                    <div class="club-info">
                        <span><strong>Advisor:</strong> <?php echo htmlspecialchars($club['advisor_name']); ?></span>
                        <span><strong>Status:</strong> <?php echo htmlspecialchars($club['status']); ?></span>
                    </div>
                    <button type="button" class="join-btn"
                        onclick="showJoinPopup('<?php echo $club['club_id']; ?>','<?php echo htmlspecialchars($club['club_name']); ?>')">
                        Join Club
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- POPUP JOIN CLUB (HIJAU) -->
<div id="joinPopup" class="popup-overlay" style="display:none;">
    <div class="popup-box join-popup-box">
        <div class="icon"><i class="fa-solid fa-circle-check"></i></div>
        <h2>Join Club?</h2>
        <p id="joinPopupText">Are you sure you want to join this club?</p>
        <div class="popup-buttons">
            <button type="button" class="cancel-popup-btn" onclick="closeJoinPopup()">Cancel</button>
            <form method="POST">
                <input type="hidden" name="club_id" id="popupClubId">
                <button type="submit" name="join_club" class="confirm-join-btn">Join</button>
            </form>
        </div>
    </div>
</div>

<!-- POPUP LEAVE CLUB (KEKAL MERAH) -->
<div id="leavePopup" class="popup-overlay" style="display:none;">
    <div class="popup-box leave-popup-box">
        <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h2>Leave Club?</h2>
        <p id="leavePopupText">Are you sure you want to leave this club?</p>
        <div class="popup-buttons">
            <button type="button" class="cancel-popup-btn" onclick="closeLeavePopup()">Cancel</button>
            <form method="POST">
                <input type="hidden" name="membership_id" id="popupMembershipId">
                <button type="submit" name="leave_membership" class="confirm-leave-btn">Leave</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const profileBtn = document.getElementById("profileButton");
    const profileDropdown = document.getElementById("profileDropdown");
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener("click", function (event) {
            event.stopPropagation();
            profileDropdown.classList.toggle("show");
        });
        document.addEventListener("click", function (event) {
            if (!profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove("show");
            }
        });
    }

    // Fungsi carian kelab (Boleh taip atau klik butang kanta)
    const searchInput = document.getElementById("clubSearch");
    const searchBtn = document.getElementById("searchBtn");
    const clubCards = document.querySelectorAll(".club-card");

    function filterClubs() {
        const searchValue = searchInput.value.toLowerCase();
        clubCards.forEach(card => {
            const clubName = card.querySelector("h3").textContent.toLowerCase();
            card.style.display = clubName.includes(searchValue) ? "flex" : "none";
        });
    }

    if (searchInput) {
        searchInput.addEventListener("keyup", filterClubs);
    }
    if (searchBtn) {
        searchBtn.addEventListener("click", filterClubs);
    }
});

function showJoinPopup(clubId, clubName) {
    document.getElementById("popupClubId").value = clubId;
    document.getElementById("joinPopupText").innerHTML =
        "Are you sure you want to join <strong>" + clubName + "</strong>?";
    document.getElementById("joinPopup").style.display = "flex";
}
function closeJoinPopup() {
    document.getElementById("joinPopup").style.display = "none";
}
function showLeavePopup(membershipId, clubName) {
    document.getElementById("popupMembershipId").value = membershipId;
    document.getElementById("leavePopupText").innerHTML =
        "Are you sure you want to leave <strong>" + clubName + "</strong>?";
    document.getElementById("leavePopup").style.display = "flex";
}
function closeLeavePopup() {
    document.getElementById("leavePopup").style.display = "none";
}
window.addEventListener("click", function (event) {
    const joinPopup = document.getElementById("joinPopup");
    const leavePopup = document.getElementById("leavePopup");
    if (event.target === joinPopup) closeJoinPopup();
    if (event.target === leavePopup) closeLeavePopup();
});
</script>

<?php include('../Includes/footer.php'); ?>