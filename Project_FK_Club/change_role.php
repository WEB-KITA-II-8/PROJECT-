<?php
session_start();
include 'db_connect.php';

/* ===============================
   SECURITY CHECK
================================ */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

/* ===============================
   VALIDATE ROLE
================================ */
$allowed_roles = ['student', 'committee'];

$new_role = $_POST['role'] ?? '';

if (!in_array($new_role, $allowed_roles)) {
    die("Invalid role request.");
}

/* ===============================
   CHECK COMMITTEE MEMBERSHIP
================================ */
if ($new_role === 'committee') {

    $stmt = $conn->prepare("
        SELECT membership_id
        FROM memberships
        WHERE user_id = ?
        AND membership_type = 'committee'
        AND membership_status = 'Active'
        LIMIT 1
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("You are not allowed to become committee.");
    }
}

/* ===============================
   UPDATE USER ROLE
================================ */
$stmt = $conn->prepare("
    UPDATE users
    SET role = ?
    WHERE user_id = ?
");

$stmt->bind_param("si", $new_role, $user_id);
$stmt->execute();

/* ===============================
   UPDATE SESSION
================================ */
$_SESSION['role'] = $new_role;

/* ===============================
   REDIRECT
================================ */
if ($new_role === 'committee') {
    header("Location: Committee/dashboard_committee.php");
} else {
    header("Location: Student/dashboard_student.php");
}

exit();
?>