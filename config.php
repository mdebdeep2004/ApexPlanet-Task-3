<?php
$servername = "localhost";
$username = "root";
$password = ""; // leave empty for default XAMPP setup
$database = "blog";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
