<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../index.php");
    exit();
}

include('../db_connect.php');

$user_id = $_SESSION['user_id'];

$query = "
SELECT
r.registration_id,
r.registered_at,
e.event_name,
e.event_location,
e.event_start_datetime
FROM event_registrations r
JOIN events_comm e
ON r.event_id = e.event_id
WHERE r.user_id = $user_id
ORDER BY r.registered_at DESC
";

$result = mysqli_query($conn, $query);

?>

<title>Event Registration</title>

<?php include('../Includes/header_stud.php'); ?>
<?php include('../Includes/sidebar_stud.php'); ?>

<div class="main-content">

<h1>My Registered Events</h1>

<p style="color:#64748b">
View all events you joined
</p>

<div class="row g-4">

<?php if(mysqli_num_rows($result)>0): ?>

<?php while($event=mysqli_fetch_assoc($result)): ?>

<div class="col-lg-4">

<div class="card p-4 shadow-sm">

<h4>

<?= htmlspecialchars($event['event_name']) ?>

</h4>

<p>

<i class="fa-solid fa-calendar"></i>

<?= date(
'd M Y h:i A',
strtotime($event['event_start_datetime'])
) ?>

</p>

<p>

<i class="fa-solid fa-location-dot"></i>

<?= htmlspecialchars(
$event['event_location']
) ?>

</p>

<p>

Registration ID:

<strong>

REG-
<?= $event['registration_id'] ?>

</strong>

</p>

<span class="badge bg-success">

Registered

</span>

</div>

</div>

<?php endwhile; ?>

<?php else: ?>

<div class="alert alert-info">

No registered events yet.

</div>

<?php endif; ?>

</div>

</div>

<?php include('../Includes/footer.php'); ?>