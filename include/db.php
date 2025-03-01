<?php
$servername = "localhost";
$username = "root";
$password = "";  
$dbname = "cvsu";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add error reporting (remove in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Add database charset
$conn->set_charset("utf8mb4");