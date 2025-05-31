<?php
// confirm_testdrive.php
session_start();

if (!isset($_SESSION['is_admin'])) {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $conn = new mysqli("localhost", "root", "", "test_drive");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $id = intval($_POST['id']);
    
    $stmt = $conn->prepare("UPDATE test_drive_submissions SET status = 'confirmed' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "Test drive confirmed successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request";
}
?>