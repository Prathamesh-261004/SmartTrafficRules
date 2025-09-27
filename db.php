<?php
// Database connection settings
$host = "localhost";
$user = "root";       // your MySQL username
$pass = "";           // your MySQL password
$db   = "traffic_app"; // database name

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
