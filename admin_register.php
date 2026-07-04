<?php
session_start();
include "db.php";

// Change this key before uploading your project online.
$ADMIN_SETUP_KEY = "1111";

$error = "";

function postValue($key) {
    return trim($_POST[$key] ?? '');
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['admin_register'])) {
    $setupKey = postValue('setup_key');
    $fullname = postValue('fullname');
    $email = postValue('email');
    $phone = postValue('phone');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($setupKey !== $ADMIN_SETUP_KEY) {
        $error = "Invalid admin setup key.";
    } elseif ($fullname === "") {
        $error = "Full name is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email=? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $check = mysqli_stmt_get_result($stmt);

        if ($check && mysqli_num_rows($check) > 0) {
            $error = "This email is already registered. Use SQL to change its role to admin, or use another email.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = "admin";
            $stmt = mysqli_prepare($conn, "INSERT INTO users(fullname,email,phone,password,role) VALUES(?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "sssss", $fullname, $email, $phone, $hashedPassword, $role);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['admin_register_success'] = "Admin account created successfully. Please login.";
                header("Location: admin_login.php");
                exit();
            } else {
                $error = "Something went wrong while creating the admin account.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=20260704_reviews">
    <style>
        body.admin-auth-body { min-height:100vh; background:linear-gradient(135deg,#0f172a,#14532d); display:flex; align-items:center; justify-content:center; padding:24px; }
        .admin-auth-card { width:100%; max-width:480px; background:#fff; border-radius:22px; padding:32px; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .admin-auth-logo { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
        .admin-auth-logo .logo-mark { width:42px; height:42px; }
        .admin-auth-card h2 { margin:0 0 8px; color:#102a43; }
        .admin-auth-card p { color:#64748b; margin-bottom:22px; }
        .admin-auth-links { margin-top:18px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .admin-auth-links a { color:#0f766e; font-weight:700; text-decoration:none; }
    </style>
</head>
<body class="admin-auth-body">
    <form class="admin-auth-card form-stack" method="POST">
        <div class="admin-auth-logo">
            <div class="logo-mark"></div>
            <strong>SolarMart Admin</strong>
        </div>
        <h2>Create Admin Account</h2>
        <p>This page creates users with <strong>role = admin</strong>. Keep the setup key private.</p>

        <?php if ($error !== "") { ?><div class="login-alert"><?php echo htmlspecialchars($error); ?></div><?php } ?>

        <input class="form-input" type="password" name="setup_key" required placeholder="Admin setup key">
        <input class="form-input" type="text" name="fullname" required placeholder="Full name">
        <input class="form-input" type="email" name="email" required placeholder="Admin email address">
        <input class="form-input" type="text" name="phone" placeholder="Phone number">
        <div class="password-box">
            <input class="form-input" type="password" id="password" name="password" required placeholder="Password">
            <i class="fa fa-eye" onclick="togglePassword('password', this)"></i>
        </div>
        <div class="password-box">
            <input class="form-input" type="password" id="confirmPassword" name="confirm_password" required placeholder="Confirm password">
            <i class="fa fa-eye" onclick="togglePassword('confirmPassword', this)"></i>
        </div>
        <button class="primary-btn" type="submit" name="admin_register">Create Admin</button>

        <div class="admin-auth-links">
            <a href="admin_login.php">← Admin Login</a>
            <a href="index.php">Website</a>
        </div>
    </form>

<script>
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>
