<?php

// DBConn.php — Database connection for ClothingStore

$conn = new mysqli("localhost", "root", "", "ClothingStore");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
