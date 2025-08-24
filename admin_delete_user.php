<?php
// FILE: admin_delete_user.php (New File)
// PURPOSE: Deletes a user from the database.

require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }
if (isset($_GET['id'])) {
    $user_id_to_delete = $_GET['id'];
    // Prevent an admin from deleting their own account
    if ($user_id_to_delete != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id_to_delete);
        if ($stmt->execute()) {
            header("Location: admin_users.php?status=deleted");
            exit();
        }
    }
}
header("Location: admin_users.php"); // Redirect if no ID or trying to self-delete
exit();
?>
