<?php
$con = mysqli_connect("localhost", "root", "", "fashion");

if (!$con) {
    die("Database connection failed");
}

$message = "";
if (isset($_POST['register'])) {

    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];

    $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($con, $_POST['lastname']);
    $middlename = mysqli_real_escape_string($con, $_POST['middlename']);
    $age = mysqli_real_escape_string($con, $_POST['age']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $contactnumber = mysqli_real_escape_string($con, $_POST['contactnumber']);

    // ✅ HASH PASSWORD
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email exists
    $checkEmail = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($checkEmail) > 0) {
        $message = "<div class='alert alert-error'>❌ Email already registered.</div>";
    } else {
        // 1. Insert into users table
        $sql1 = "INSERT INTO users (email, password) VALUES ('$email', '$hashedPassword')";

        if (mysqli_query($con, $sql1)) {
            $user_id = mysqli_insert_id($con);

            // 2. Insert into profile table
            $sql2 = "INSERT INTO user_profile 
            (user_id, firstname, lastname, middlename, age, address, contactnumber)
            VALUES 
            ('$user_id', '$firstname', '$lastname', '$middlename', '$age', '$address', '$contactnumber')";

            if (mysqli_query($con, $sql2)) {
                $message = "<div class='alert alert-success'>✨ Registration successful! <a href='login.php'>Login here</a></div>";
            } else {
                $message = "<div class='alert alert-error'>Profile error: " . mysqli_error($con) . "</div>";
            }
        } else {
            $message = "<div class='alert alert-error'>Account error: " . mysqli_error($con) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join The Atelier | Aura Luxury Rentals</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --gold-hover: #f1c40f;
            --black: #0a0a0a;
            --grey: #161616;
            --white: #ffffff;
            --input-bg: #222;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--black);
            background-image: radial-gradient(circle at top right, #1a1a1a, #0a0a0a);
            color: var(--white);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .reg-container {
            background: var(--grey);
            width: 100%;
            max-width: 600px;
            padding: 40px;
            border-radius: 15px;
            border: 1px solid #333;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .reg-container::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 4px;
            background: linear-gradient(to right, transparent, var(--gold), transparent);
        }

        h2 {
            font-family: 'Playfair Display', serif;
            color: var(--gold);
            text-align: center;
            font-size: 2rem;
            letter-spacing: 2px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        h3 {
            font-size: 0.9rem;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 25px 0 15px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            grid-column: span 2;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .full-width {
            grid-column: span 2;
        }

        .input-group {
            display: flex;
            flex-direction: column;
        }

        input {
            background: var(--input-bg);
            border: 1px solid #444;
            padding: 12px 15px;
            border-radius: 5px;
            color: white;
            font-family: inherit;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.2);
        }

        .btn-register {
            background: var(--gold);
            color: black;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            margin-top: 30px;
            transition: 0.3s;
        }

        .btn-register:hover {
            background: var(--gold-hover);
            transform: translateY(-2px);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #888;
        }

        .login-link a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }
        .alert-success { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; }
        .alert-error { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }

        @media (max-width: 500px) {
            .form-grid { grid-template-columns: 1fr; }
            h3, .full-width { grid-column: span 1; }
            .reg-container { padding: 25px; }
        }
    </style>
</head>
<body>

<div class="reg-container">
    <h2>The Atelier</h2>
    
    <?php echo $message; ?>

    <form method="POST">
        <div class="form-grid">
            <h3>Account Credentials</h3>
            <div class="input-group full-width">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group full-width">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <h3>Personal Profile</h3>
            <div class="input-group">
                <input type="text" name="firstname" placeholder="First Name" required>
            </div>
            <div class="input-group">
                <input type="text" name="lastname" placeholder="Last Name" required>
            </div>
            <div class="input-group">
                <input type="text" name="middlename" placeholder="Middle Name">
            </div>
            <div class="input-group">
                <input type="number" name="age" placeholder="Age" required>
            </div>
            <div class="input-group full-width">
                <input type="text" name="contactnumber" placeholder="Contact Number (e.g. 09123456789)" required>
            </div>
            <div class="input-group full-width">
                <input type="text" name="address" placeholder="Full Home Address" required>
            </div>
        </div>

        <button type="submit" name="register" class="btn-register">Create Account</button>
        
        <div class="login-link">
            Already a member? <a href="login.php">Sign In</a>
        </div>
    </form>
</div>

</body>
</html>