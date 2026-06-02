<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$can_committee = false;

$check = mysqli_query($conn, "
    SELECT *
    FROM memberships
    WHERE user_id = '$user_id'
    AND membership_type = 'committee'
    AND membership_status = 'Active'
    LIMIT 1
");

if ($check && mysqli_num_rows($check) > 0) {
    $can_committee = true;
}
?>

<h2>Choose Dashboard</h2>

<a href="Student/dashboard_student.php">
    Enter as Student
</a>

<?php if ($can_committee): ?>
    <br><br>
    <a href="Committee/dashboard_committee.php">
        Enter as Committee
    </a>
<?php endif; ?>