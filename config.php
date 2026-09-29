<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "hotel_booking";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
