<?php
$servername = "localhost";
$username = "root";
$password = "";   // XAMPP default has no password
$dbname = "smart_plumbing_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
