<?php
// =============================================
// DATABASE CONNECTION SETTINGS
// FILE: db_connect.php
// =============================================
$host = "localhost";          // Database host
$username = "root";          // XAMPP default username
$password = "";              // XAMPP default password
$database = "fk_student_club_event"; // Your database name

// Create MySQL connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>