<?php
// =============================================
// ADMIN DELETE MEMBERSHIP
// FILE: admin/delete_membership.php
// =============================================

// Start session
session_start();


// =============================================
// SECURITY CHECK
// Only admin allowed
// =============================================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}


// =============================================
// DATABASE CONNECTION
// =============================================
include '../db_connect.php';


// =============================================
// CHECK MEMBERSHIP ID
// =============================================
if (isset($_GET['id']) && is_numeric($_GET['id'])) {

    // Get membership ID
    $membership_id = intval($_GET['id']);


    // =============================================
    // DELETE MEMBERSHIP QUERY
    // =============================================
    $deleteQuery = "
        DELETE FROM memberships
        WHERE membership_id = '$membership_id'
    ";


    // Execute deletion
    if (mysqli_query($conn, $deleteQuery)) {

        // Redirect success
        header("Location: manage_memberships.php?deleted=1");
        exit();

    } else {

        // Redirect fail
        header("Location: manage_memberships.php?error=1");
        exit();

    }

} else {

    // Invalid ID
    header("Location: manage_memberships.php");
    exit();

}
?>