<?php
// Start session to check if the user is logged in
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "unauthorized";
    exit();
}

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'appointment_system';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user ID is provided
if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Update the 'served' column to 1 for the given user ID
    $sql = "UPDATE users SET served = 1 WHERE id = $user_id";

    if ($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "invalid";
}

// Close the connection
$conn->close();
?>
