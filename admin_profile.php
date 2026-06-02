<?php
session_start();

/** 
 * 1. DATABASE CONFIGURATION
 */
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'fashion'; 

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/** 
 * 2. MAGIC LINK CHECK (Security Note: Recommended to remove in production)
 */
$magic_token = "ACCESS_ME_ANYTIME"; 

if (isset($_GET['token']) && $_GET['token'] === $magic_token) {
    $_SESSION['user_token'] = 'bypass_active'; 
    $_SESSION['is_admin'] = true;
    $_SESSION['username'] = 'Super Admin'; // Default for bypass
    $_SESSION['admin_id'] = 1;             // Default for bypass
}

/**
 * 3. SECURITY CHECK
 */
if (!isset($_SESSION['user_token'])) {
    header("Location: admin_login.php"); 
    exit();
}

/**
 * 4. PROFILE UPDATE LOGIC (Handles Password Change)
 */
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass === $confirm_pass) {
        // In a real app, use password_hash. 
        // If your login system uses plain text (not recommended), remove password_hash.
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        $admin_id = $_SESSION['admin_id'];

        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $admin_id);
        
        if ($stmt->execute()) {
            $message = "<div style='color: #166534; background: #dcfce7; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Password updated successfully!</div>";
        } else {
            $message = "<div style='color: #991b1b; background: #fee2e2; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Database error occurred.</div>";
        }
    } else {
        $message = "<div style='color: #991b1b; background: #fee2e2; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Passwords do not match!</div>";
    }
}

/**
 * 5. ROUTING LOGIC
 */
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$titles = [
    'dashboard' => 'Admin Dashboard',
    'gowns'     => 'Manage Gowns',
    'cms'       => 'Facebook CMS',
    'payments'  => 'Payment Management',
    'reports'   => 'Sales & Traffic Reports',
    'admin_panel' => 'Admin Control',
    'makeup_management' => 'Makeup Services',
    'package_management' => 'Package Management',
    'admin_profile' => 'My Profile'
];
$current_title = isset($titles[$page]) ? $titles[$page] : 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $current_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #0f172a;
            --secondary-color: #1e293b;
            --accent-color: #6366f1;
            --text-main: #334155;
            --text-light: #f8fafc;
            --bg-body: #f1f5f9;
            --transition: all 0.3s ease;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-body); color: var(--text-main); overflow-x: hidden; }

        .sidebar { width: var(--sidebar-width); background-color: var(--primary-color); height: 100vh; position: fixed; left: 0; top: 0; color: var(--text-light); display: flex; flex-direction: column; transition: var(--transition); z-index: 1000; }
        .sidebar-header { padding: 25px 20px; text-align: center; font-size: 1.4rem; font-weight: 700; letter-spacing: 1px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { list-style: none; padding: 20px 0; flex-grow: 1; }
        .sidebar-menu li { padding: 5px 15px; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 15px; color: #94a3b8; text-decoration: none; border-radius: 8px; transition: var(--transition); }
        .sidebar-menu li a i { margin-right: 12px; width: 20px; text-align: center; font-size: 1.1rem;}
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: var(--accent-color); color: white; }
        
        .logout-section { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn { color: #fca5a5; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 10px; }

        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; transition: var(--transition); }
        .top-bar { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 900; }
        
        .admin-profile-link { text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer; transition: color 0.2s; }
        .admin-profile-link:hover { color: var(--accent-color); }

        .page-content { padding: 30px; }

        /* Dashboard specific styles */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display: flex; align-items: center; gap: 15px; border: 1px solid #e2e8f0; }
        .stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: white; flex-shrink: 0; }
        .bg-blue { background: #3b82f6; }
        .bg-emerald { background: #10b981; }
        .bg-amber { background: #f59e0b; }
        .bg-indigo { background: #6366f1; }

        .data-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
        .custom-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .custom-table th { text-align: left; padding: 12px; background: #f8fafc; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        .custom-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }

        /* Form Styling for Profile */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 0.9rem; }
        .form-control { width: 100%; max-width: 400px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; outline: none; }
        .form-control:focus { border-color: var(--accent-color); }
        .btn-primary { background: var(--accent-color); color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-primary:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header"><i class="fas fa-crown"></i> GOWN ADMIN</div>
        <ul class="sidebar-menu">
            <li><a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="?page=gowns" class="<?= $page == 'gowns' ? 'active' : '' ?>"><i class="fas fa-cut"></i> Manage Gowns</a></li>
            <li><a href="?page=makeup_management" class="<?= $page == 'makeup_management' ? 'active' : '' ?>"><i class="fas fa-magic"></i> Makeup Artists</a></li>
            <li><a href="?page=package_management" class="<?= $page == 'package_management' ? 'active' : '' ?>"><i class="fas fa-box-open"></i> Packages</a></li>
            <li><a href="?page=payments" class="<?= $page == 'payments' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Payments</a></li>
            <li><a href="?page=admin_panel" class="<?= $page == 'admin_panel' ? 'active' : '' ?>"><i class="fas fa-user-cog"></i> Admin Panel</a></li>
            <li><a href="?page=cms" class="<?= $page == 'cms' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> CMS</a></li>
            <li><a href="?page=reports" class="<?= $page == 'reports' ? 'active' : '' ?>"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="?page=admin_profile" class="<?= $page == 'admin_profile' ? 'active' : '' ?>"><i class="fas fa-user-circle"></i> My Profile</a></li>
        </ul>
        <div class="logout-section">
             <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h2 style="font-weight: 600; color: var(--primary-color);"><?php echo $current_title; ?></h2>
            <a href="?page=admin_profile" class="admin-profile-link">
                <i class="fas fa-user-circle fa-lg"></i> 
                <span><?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
            </a>
        </div>

        <div class="page-content">
            <?php 
                switch ($page) {
                    case 'dashboard':
                        // ... [Your existing Dashboard logic here] ...
                        // (Basic counts and recent appointments table)
                        echo "<h3>Welcome to the Dashboard</h3>";
                        // (Re-insert your dashboard statistics grid here)
                        break;

                    case 'admin_profile':
                        ?>
                        <div class="data-card">
                            <h3>Account Information</h3>
                            <div style="margin-top: 15px; color: #64748b;">
                                <p><strong>Username:</strong> <?php echo $_SESSION['username'] ?? 'Not set'; ?></p>
                                <p><strong>Admin ID:</strong> #<?php echo $_SESSION['admin_id'] ?? '0'; ?></p>
                                <p><strong>Access Level:</strong> Full Administrator</p>
                            </div>
                        </div>

                        <div class="data-card">
                            <h3>Update Password</h3>
                            <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 20px;">Ensure your account is using a long, random password to stay secure.</p>
                            
                            <?php echo $message; ?>

                            <form method="POST" action="?page=admin_profile">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" class="form-control" required placeholder="Minimum 8 characters">
                                </div>
                                <div class="form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
                                </div>
                                <button type="submit" name="update_password" class="btn-primary">
                                    <i class="fas fa-key"></i> Update Password
                                </button>
                            </form>
                        </div>
                        <?php
                        break;

                    case 'gowns': @include('gowns_create.php'); break;
                    case 'payments': @include('admin_payments.php'); break;
                    case 'makeup_management': @include('makeup_management.php'); break;
                    case 'package_management': @include('package_management.php'); break;
                    case 'admin_panel': @include('admin_panel.php'); break;
                    case 'cms': @include('cms.php'); break;
                    case 'reports': @include('reports.php'); break;
                }
            ?>
        </div>
    </div>
</body>
</html>