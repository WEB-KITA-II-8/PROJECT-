<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../index.php");
    exit();
}

include('../db_connect.php');

$user_id = $_SESSION['user_id'];
$student_name = $_SESSION['full_name'] ?? '';

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

$event_query = mysqli_query($conn, "
    SELECT *
    FROM events_comm
    WHERE event_id = $event_id
");

if (!$event_query || mysqli_num_rows($event_query) == 0) {
    die("Event not found.");
}

$event = mysqli_fetch_assoc($event_query);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_email = mysqli_real_escape_string($conn, $_POST['student_email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $check = mysqli_query($conn, "
        SELECT *
        FROM event_registrations
        WHERE event_id = $event_id
        AND user_id = $user_id
    ");

    if (mysqli_num_rows($check) > 0) {

        $message = "You already registered for this event.";

    } else {

        $insert = mysqli_query($conn, "
            INSERT INTO event_registrations (
                event_id,
                user_id,
                student_name,
                student_email,
                phone
            ) VALUES (
                $event_id,
                $user_id,
                '$student_name',
                '$student_email',
                '$phone'
            )
        ");

        if ($insert) {

            $registration_id = mysqli_insert_id($conn);
            $event_name = mysqli_real_escape_string($conn, $event['event_name']);

            mysqli_query($conn, "
                INSERT INTO participation (
                    registration_id,
                    student_name,
                    event_name,
                    attendance_status,
                    participation_date
                ) VALUES (
                    $registration_id,
                    '$student_name',
                    '$event_name',
                    'Pending',
                    CURDATE()
                )
            ");

            $message = "Registration successful!";

        } else {

            $message = "Registration failed: " . mysqli_error($conn);

        }
    }
}
?>

<title>Register Event</title>

<?php include('../Includes/header_stud.php'); ?>
<?php include('../Includes/sidebar_stud.php'); ?>

<div class="main-content">

    <div class="container-fluid">

        <h1 class="event-title">Event Registration</h1>

        <?php if ($message != "") { ?>
            <div class="alert alert-info">
                <?= $message ?>
            </div>
        <?php } ?>

        <div class="card p-4">

            <h4><?= htmlspecialchars($event['event_name']) ?></h4>

            <p>
                <strong>Date:</strong>
                <?= date('d M Y h:i A', strtotime($event['event_start_datetime'])) ?>
            </p>

            <p>
                <strong>Location:</strong>
                <?= htmlspecialchars($event['event_location']) ?>
            </p>

            <p>
                <strong>Description:</strong>
                <?= htmlspecialchars($event['event_description']) ?>
            </p>

            <hr>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Student Name</label>
                    <input type="text"
                           class="form-control"
                           value="<?= htmlspecialchars($student_name) ?>"
                           readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Student Email</label>
                    <input type="email"
                           name="student_email"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text"
                           name="phone"
                           class="form-control"
                           required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Confirm Registration
                </button>

                <a href="upcoming_events.php" class="btn btn-secondary">
                    Back
                </a>

            </form>

        </div>

    </div>

</div>

<?php include('../Includes/footer.php'); ?>