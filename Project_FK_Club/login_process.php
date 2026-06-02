<?php
// =============================================
// LOGIN PROCESS
// FILE: login_process.php
// =============================================

session_start();

include 'db_connect.php';

/* =============================================
   GET FORM INPUT
============================================= */
$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

/* =============================================
   CHECK USER BY EMAIL ONLY
============================================= */
$stmt = $conn->prepare(
    "SELECT * FROM users WHERE email = ? LIMIT 1"
);

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

/* =============================================
   VERIFY LOGIN
============================================= */
if ($user && $password === $user['password']) {

    session_regenerate_id(true);

    $_SESSION['user_id']    = $user['user_id'];
    $_SESSION['student_id'] = $user['student_id'];
    $_SESSION['full_name']  = $user['full_name'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['role']       = $user['role'];

    /* Admin goes directly to admin dashboard */
    if ($user['role'] === 'admin') {
        header("Location: Admin/dashboard_admin.php");
        exit();
    }

    /* Student / Committee choose dashboard */
    header("Location: choose_role.php");
    exit();

} else {

    header("Location: index.php?error=1");
    exit();
}
?>