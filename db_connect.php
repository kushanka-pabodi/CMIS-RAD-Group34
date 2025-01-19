<?php
// db_connect.php

// Database credentials
$host = "localhost";      // XAMPP default host
$user = "root";           // XAMPP default user
$pass = "";               // XAMPP default password (often empty)
$db   = "cms";            // Your database name

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optionally, you can echo or log success for debugging
// echo "Database connected successfully!";
?>
