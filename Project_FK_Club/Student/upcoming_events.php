<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../index.php");
    exit();
}

?>

<title>Upcoming Events</title>

<?php include('../Includes/header_stud.php'); ?>
<?php include('../Includes/sidebar_stud.php'); ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <section class="event-section">

        <div class="container-fluid">

            <h1 class="event-title">Upcoming Events</h1>
            <p class="event-subtitle">Stay updated with our latest activities and programs</p>

            <div class="row g-4">

                <!-- EVENT 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="card event-card">

                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?q=80&w=1200&auto=format&fit=crop"
                                 class="w-100 event-image">

                            <div class="event-date">
                                <span>25</span>
                                <small>MAY</small>
                            </div>
                        </div>

                        <div class="event-info">
                            <h5>Tech Innovation Seminar</h5>

                            <div class="event-meta">
                                <i class="fa-solid fa-location-dot"></i>
                                Main Hall UMPSA
                            </div>

                            <div class="event-meta">
                                <i class="fa-solid fa-clock"></i>
                                9:00 AM - 1:00 PM
                            </div>

                            <p class="text-muted mt-3">
                                Join industry experts discussing the latest trends in technology.
                            </p>

                            <button class="btn btn-primary event-btn">
                                Register Now
                            </button>
                        </div>

                    </div>
                </div>

                <!-- EVENT 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="card event-card">

                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?q=80&w=1200&auto=format&fit=crop"
                                 class="w-100 event-image">

                            <div class="event-date bg-success">
                                <span>02</span>
                                <small>JUN</small>
                            </div>
                        </div>

                        <div class="event-info">
                            <h5>Community Charity Program</h5>

                            <div class="event-meta">
                                <i class="fa-solid fa-location-dot"></i>
                                Kuantan City Center
                            </div>

                            <div class="event-meta">
                                <i class="fa-solid fa-clock"></i>
                                8:30 AM - 4:00 PM
                            </div>

                            <p class="text-muted mt-3">
                                Participate in our charity activities together.
                            </p>

                            <button class="btn btn-success event-btn">
                                Join Event
                            </button>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </section>

</div>

<?php include('../Includes/footer.php'); ?>