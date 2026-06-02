<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../index.php");
    exit();
}

include('../db_connect.php');

$query = "
    SELECT *
    FROM events_comm
    WHERE event_end_datetime >= NOW()
    ORDER BY event_start_datetime ASC
";

$events = mysqli_query($conn, $query);

if (!$events) {
    die("Database error: " . mysqli_error($conn));
}
?>

<title>Upcoming Events</title>

<?php include('../Includes/header_stud.php'); ?>
<?php include('../Includes/sidebar_stud.php'); ?>

<div class="main-content">

    <section class="event-section">

        <div class="container-fluid">

            <h1 class="event-title">Upcoming Events</h1>
            <p class="event-subtitle">Stay updated with our latest activities and programs</p>

            <div class="row g-4">

                <?php if (mysqli_num_rows($events) > 0) { ?>

                    <?php while ($event = mysqli_fetch_assoc($events)) { ?>

                        <?php
                        $current = date('Y-m-d H:i:s');
                        $start = $event['event_start_datetime'];
                        $end = $event['event_end_datetime'];

                        if ($current < $start) {
                            $status = "Upcoming";
                            $badge = "bg-primary";
                        } elseif ($current >= $start && $current <= $end) {
                            $status = "Ongoing";
                            $badge = "bg-success";
                        } else {
                            $status = "Completed";
                            $badge = "bg-secondary";
                        }
                        ?>

                        <div class="col-lg-4 col-md-6">
                            <div class="card event-card">

                                <div class="position-relative">
                                    <?php
                                        $image = !empty($event['event_image'])
                                            ? '../' . $event['event_image']
                                            : 'https://images.unsplash.com/photo-1511578314322-379afb476865?q=80&w=1200&auto=format&fit=crop';
                                        ?>

                                    <img src="<?= htmlspecialchars($image) ?>" class="w-100 event-image">

                                    <div class="event-date">
                                        <span>
                                            <?= date('d', strtotime($event['event_start_datetime'])) ?>
                                        </span>
                                        <small>
                                            <?= strtoupper(date('M', strtotime($event['event_start_datetime']))) ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="event-info">

                                    <h5>
                                        <?= htmlspecialchars($event['event_name']) ?>
                                    </h5>

                                    <span class="badge <?= $badge ?> mb-3">
                                        <?= $status ?>
                                    </span>

                                    <div class="event-meta">
                                        <i class="fa-solid fa-calendar"></i>
                                        <?= date('d M Y', strtotime($event['event_start_datetime'])) ?>
                                    </div>

                                    <div class="event-meta">
                                        <i class="fa-solid fa-clock"></i>
                                        <?= date('h:i A', strtotime($event['event_start_datetime'])) ?>
                                        -
                                        <?= date('h:i A', strtotime($event['event_end_datetime'])) ?>
                                    </div>

                                    <div class="event-meta">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?= htmlspecialchars($event['event_location']) ?>
                                    </div>

                                    <div class="event-meta">
                                        <i class="fa-solid fa-users"></i>
                                        Capacity:
                                        <?= htmlspecialchars($event['event_capacity']) ?>
                                    </div>

                                    <p class="text-muted mt-3">
                                        <?= nl2br(htmlspecialchars($event['event_description'])) ?>
                                    </p>

                                    <a href="student_reg_form.php?event_id=<?= $event['event_id'] ?>"
                                       class="btn btn-primary event-btn">
                                        Register Now
                                    </a>

                                </div>

                            </div>
                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="col-12">
                        <div class="alert alert-info">
                            No upcoming or ongoing events available.
                        </div>
                    </div>

                <?php } ?>

            </div>

        </div>

    </section>

</div>

<?php include('../Includes/footer.php'); ?>