<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();
// Include the database from the folder above (Fashion/db.php)
include('../db.php');

$error = "";

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // 1. Check if DB connection works
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // 2. Prepare statement to find the admin
    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // 3. Validation Logic
    if ($admin) {
        // Compare plain text password (admin123)
        // trim() removes any hidden spaces from the database or input
        if (trim($pass) === trim($admin['password'])) {
            
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_token'] = 'active'; // Required by your auth_check.php

            // REDIRECT: Make sure the filename below is EXACTLY correct
            header("Location: admin_dashboard.php");
            exit(); 
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: sans-serif; background: #f1f5f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #0f172a; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .error { color: red; font-size: 0.8rem; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>