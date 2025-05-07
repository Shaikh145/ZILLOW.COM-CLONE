<?php
// Database connection parameters
$host = "localhost";
$username = "uklz9ew3hrop3";
$password = "zyrbspyjlzjb";
$database = "dbbxwfvewdhinh";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?>
