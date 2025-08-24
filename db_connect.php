<?php
// db_connect.php

// --- Database Credentials ---
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is blank
$dbname = "university_db";

// --- Create Connection ---
$conn = new mysqli($servername, $username, $password, $dbname);

// --- Check Connection ---
if ($conn->connect_error) {
    // If connection fails, stop everything and show the error.
    die("Database Connection Failed: " . $conn->connect_error);
}
?>