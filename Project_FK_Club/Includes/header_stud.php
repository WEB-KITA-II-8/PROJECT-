<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../index.php");
    exit();
}

$student_name = $_SESSION['full_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard</title>

    <!-- Main CSS -->
    <link rel="stylesheet" href="../CSS_stud/student.css">
    <link rel="stylesheet" href="../CSS_stud/membership_student.css">
    <link rel="stylesheet" href="../CSS_stud/upcoming_events.css">
    <link rel="stylesheet" href="../CSS_stud/event_reg.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <!-- Font Awesome FIXED -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          referrerpolicy="no-referrer">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<!-- =============================================
TOPBAR
============================================= -->
<div class="topbar">

    <div class="profile-menu">

        <button type="button"
            class="profile-btn"
            onclick="toggleDropdown()">

            <div class="profile-info">

                <span class="profile-name">
                    <?php echo htmlspecialchars(strtoupper($student_name)); ?>
                </span>

                <span class="profile-role">
                    Student
                </span>

            </div>

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

        </button>

        <div class="dropdown-content" id="profileDropdown">

            <a href="profile_student.php">
                <i class="fa-solid fa-user"></i>
                Manage Profile
            </a>

            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>

            <form method="POST" action="../change_role.php">
                <button type="submit" name="role" value="committee"
                    onclick="return confirm('Are you sure you want to switch to Committee role?')">
                    Switch to Committee Role
                </button>
            </form>

        </div>

    </div>

</div>

<script>
function toggleDropdown() {

    const dropdown = document.getElementById("profileDropdown");

    if(dropdown){
        dropdown.classList.toggle("show");
    }
}
</script>