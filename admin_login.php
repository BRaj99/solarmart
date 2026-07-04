<?php
session_start();
include "db.php";

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

$error = "";
$success = $_SESSION['admin_register_success'] ?? "";
unset($_SESSION['admin_register_success']);

function postValue($key) {
    return trim($_POST[$key] ?? '');
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['admin_login'])) {
    $email = postValue('email');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? AND role='admin' LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['user_age'] = $user['age'] ?? '';
            $_SESSION['user_gender'] = $user['gender'] ?? '';
            $_SESSION['user_location'] = $user['location'] ?? '';
            $_SESSION['user_address'] = $user['address'] ?? '';
            $_SESSION['user_role'] = 'admin';
            header("Location: admin.php");
            exit();
        } else {
            $error = "Incorrect admin password.";
        }
    } else {
        $error = "Admin account not found. Please use an account with admin role.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=20260704_reviews">
    <style>
        body.admin-auth-body { min-height:100vh; background:linear-gradient(135deg,#0f172a,#14532d); display:flex; align-items:center; justify-content:center; padding:24px; }
        .admin-auth-card { width:100%; max-width:430px; background:#fff; border-radius:22px; padding:32px; box-shadow:0 20px 60px rgba(0,0,0,.25); }
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
        <h2>Admin Login</h2>
        <p>Login with an admin account to manage products, orders, stock and customers.</p>

        <?php if ($success !== "") { ?><div class="success-alert"><?php echo htmlspecialchars($success); ?></div><?php } ?>
        <?php if ($error !== "") { ?><div class="login-alert"><?php echo htmlspecialchars($error); ?></div><?php } ?>

        <input class="form-input" type="email" name="email" required placeholder="Admin email address">
        <div class="password-box">
            <input class="form-input" type="password" id="adminPassword" name="password" required placeholder="Password">
            <i class="fa fa-eye" onclick="togglePassword('adminPassword', this)"></i>
        </div>
        <button class="primary-btn" type="submit" name="admin_login">Login to Admin Panel</button>

        <div class="admin-auth-links">
            <a href="index.php">← Website</a>
            <a href="admin_register.php">Create Admin</a>
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
