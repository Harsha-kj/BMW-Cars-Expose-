<?php
$conn = new mysqli("localhost", "root", "", "brand_fusion_rentals");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!";
}
?>