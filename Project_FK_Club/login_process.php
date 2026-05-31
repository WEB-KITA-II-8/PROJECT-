<?php
// =============================================
// LOGIN PROCESS WITH NORMAL PASSWORD
// FILE: login_process.php
// =============================================

// Start session
session_start();

// Database connection
include 'db_connect.php';

// =============================================
// GET FORM INPUT
// =============================================
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

// =============================================
// CHECK USER BY EMAIL + ROLE
// =============================================
$stmt = $conn->prepare(
    "SELECT * FROM users WHERE email = ? AND role = ?"
);

$stmt->bind_param("ss", $email, $role);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

// =============================================
// VERIFY LOGIN
// =============================================
if ($user && $password === $user['password']) {

    // Security session refresh
    session_regenerate_id(true);

    // Save session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];

    // =============================================
    // REDIRECT BASED ON ROLE
    // =============================================
    if ($role == 'admin') {

        header("Location: Admin/dashboard_admin.php");

    } elseif ($role == 'student') {

        header("Location: Student/dashboard_student.php");

    } elseif ($role == 'committee') {

        header("Location: Committee/dashboard_committee.php");

    }

    exit();

} else {

    // =============================================
    // INVALID LOGIN
    // =============================================
    header("Location: index.php?error=1");
    exit();
}
?>