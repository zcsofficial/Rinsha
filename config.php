<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'u324921317_rinsha';         // Replace with your MySQL username
$db_pass = 'Adnan@66202';             // Replace with your MySQL password
$db_name = 'u324921317_villagesys';

// Connect to the database
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8");
?>
