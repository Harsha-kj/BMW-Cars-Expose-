<?php
require_once 'session_init.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Clear previous admin data
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_role']);

// Generate CSRF token only once per session
if (empty($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}

// Error handling
$error_message = '';
if (!empty($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | BRAND FUSION</title>
    <style>
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(3deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        body {
            margin: 0;
            background: linear-gradient(-45deg, #0a0a0a, #1a1a1a, #2a1e0a);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
            overflow: hidden;
        }

        .auth-container {
            background: rgba(25, 25, 25, 0.95);
            backdrop-filter: blur(15px);
            padding: 3rem;
            border-radius: 20px;
            width: 550px;
            border: 1px solid rgba(245, 183, 84, 0.2);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }

        .auth-title {
            font-size: 2.3rem;
            margin-bottom: 1.5rem;
            text-align: center;
            background: linear-gradient(45deg, #fff, #F5B754);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 5px rgba(245, 183, 84, 0.3));
            animation: float 6s ease-in-out infinite;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .form-group {
            position: relative;
        }

        input {
            width: 100%;
            padding: 1rem;
            border: none;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        input:focus {
            outline: none;
            border-color: #F5B754;
            background: rgba(245, 183, 84, 0.05);
            box-shadow: 0 0 15px rgba(245, 183, 84, 0.2);
        }

        .auth-button {
            padding: 1rem;
            background: linear-gradient(45deg, #F5B754, #d89d40);
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .auth-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(245, 183, 84, 0.3);
        }

        .error-message {
            color: #ff4444;
            margin: 10px 0;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <h1 class="auth-title">ADMIN PAGE</h1>
        <form id="adminLoginForm" class="auth-form" action="admin_login_handler.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
            <div class="form-group">
                <input type="text" placeholder="Admin ID" name="admin_id" autocomplete="off" required>  
            </div>
            <div class="form-group">
                <input type="password" placeholder="Security Key" name="admin_key" autocomplete="off" required> 
            </div>
            <button type="submit" class="auth-button">Authenticate</button>
        </form>
    </div>
    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Authenticating...';
        });
    </script>
</body>
</html>