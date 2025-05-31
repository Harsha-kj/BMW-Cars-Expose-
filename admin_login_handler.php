<?php
ob_start();
require_once __DIR__ . '/session_init.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header("Location: admin_login.php");
    exit();
}

// CSRF validation
if (empty($_POST['csrf_token']) || empty($_SESSION['admin_csrf_token']) || 
    !hash_equals($_SESSION['admin_csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = 'Security token mismatch!';
    header("Location: admin_login.php");
    exit();
}

// Brute-force protection
$_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
if ($_SESSION['login_attempts'] > 5) {
    $_SESSION['error'] = 'Too many attempts. Please wait 15 minutes.';
    header("Location: admin_login.php");
    exit();
}

// TEST CREDENTIALS - TEMPORARY (REMOVE IN PRODUCTION)
if ($_POST['admin_id'] === 'admin1' && $_POST['admin_key'] === 'Admin@123') {
    // Successful login
    unset($_SESSION['login_attempts']);
    session_regenerate_id(true);
    
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = 'admin1';
    $_SESSION['admin_role'] = 'super_admin';
    $_SESSION['last_login'] = date('Y-m-d H:i:s');
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    
    header("Location: admin_dashboard.php");
    exit();
}

// Database authentication (for real implementation)
try {
    $conn = new mysqli("localhost", "root", "", "brand_fusion_rentals");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("SELECT id, admin_id, admin_key, role FROM admins WHERE admin_id = ?");
    if (!$stmt) throw new Exception("Prepare failed");
    
    $admin_id = trim($_POST['admin_id']);
    $stmt->bind_param("s", $admin_id);
    
    if (!$stmt->execute()) throw new Exception("Execute failed");
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($_POST['admin_key'], $admin['admin_key'])) {
            // Successful login
            unset($_SESSION['login_attempts']);
            session_regenerate_id(true);
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['last_login'] = date('Y-m-d H:i:s');
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            
            header("Location: admin_dashboard.php");
            exit();
        }
    }
    
    // Failed login
    $_SESSION['error'] = 'Invalid admin ID or security key';
    header("Location: admin_login.php");
    exit();

} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    $_SESSION['error'] = 'System error. Please try again later.';
    header("Location: admin_login.php");
    exit();
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    ob_end_flush();
}
?>