<?php
// logout.php

// Always start the session first
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session completely
session_destroy();

// Redirect the user back to the login page
header("location: login.php");
exit;
?>