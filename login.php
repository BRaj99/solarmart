<?php
session_start();
include "db.php";
include "otp_helper.php";

ensureOtpTable($conn);

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$error = "";
$registerError = "";
$registerSuccess = "";
$otpMessage = "";
$showSignup = false;
$showOtp = false;

$next = $_GET["next"] ?? ($_POST["next"] ?? "index.php");
$allowedNext = ["checkout.php", "cart.php", "index.php", "shop.php", "admin.php", "admin_products.php", "admin_orders.php", "admin_stock.php", "admin_customers.php"];
if (!in_array($next, $allowedNext)) { $next = "index.php"; }

$loginNotice = $_SESSION["login_notice"] ?? "";
unset($_SESSION["login_notice"]);

function postValue($key) {
    return trim($_POST[$key] ?? '');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = postValue('email');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            if (($user['role'] ?? 'customer') === 'admin') {
                $error = "Please use the separate Admin Login page.";
            } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['user_age'] = $user['age'];
            $_SESSION['user_gender'] = $user['gender'];
            $_SESSION['user_location'] = $user['location'];
            $_SESSION['user_address'] = $user['address'];
            $_SESSION['user_role'] = $user['role'] ?? 'customer';
            header("Location: " . $next);
            exit();
            }
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "Email not found";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $showSignup = true;

    $fullname = postValue('fullname');
    $email = postValue('email');
    $phone = postValue('phone');
    $age = postValue('age');
    $gender = postValue('gender');
    $location = postValue('location');
    $address = postValue('address');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $registerError = "Password must be at least 6 characters.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email=? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $check = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($check) > 0) {
            $registerError = "Email already registered";
        } else {
            $_SESSION['pending_register'] = [
                'fullname' => $fullname,
                'email' => $email,
                'phone' => $phone,
                'age' => $age,
                'gender' => $gender,
                'location' => $location,
                'address' => $address,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];

            try {
                createAndSendOtp($conn, $email, $fullname, 'register');
                $showSignup = false;
                $showOtp = true;
                $otpMessage = "OTP sent to {$email}. It expires in 1 minute.";
            } catch (Exception $e) {
                $registerError = $e->getMessage();
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_register_otp'])) {
    $pending = $_SESSION['pending_register'] ?? null;
    $otp = postValue('otp');
    $showOtp = true;

    if (!$pending) {
        $registerError = "Registration session expired. Please sign up again.";
        $showOtp = false;
        $showSignup = true;
    } else {
        $verify = verifyOtpCode($conn, $pending['email'], 'register', $otp);
        if (!$verify['ok']) {
            $registerError = $verify['message'];
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO users(fullname,email,phone,age,gender,location,address,password) VALUES(?,?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "ssssssss", $pending['fullname'], $pending['email'], $pending['phone'], $pending['age'], $pending['gender'], $pending['location'], $pending['address'], $pending['password']);

            if (mysqli_stmt_execute($stmt)) {
                unset($_SESSION['pending_register']);
                $registerSuccess = "Account verified and created successfully. You can login now.";
                $showOtp = false;
            } else {
                $registerError = "Something went wrong. Please try again.";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resend_register_otp'])) {
    $pending = $_SESSION['pending_register'] ?? null;
    $showOtp = true;

    if (!$pending) {
        $registerError = "Registration session expired. Please sign up again.";
        $showOtp = false;
        $showSignup = true;
    } else {
        try {
            createAndSendOtp($conn, $pending['email'], $pending['fullname'], 'register');
            $otpMessage = "A new OTP has been sent. It expires in 1 minute.";
        } catch (Exception $e) {
            $registerError = $e->getMessage();
        }
    }
}

if (isset($_SESSION['pending_register']) && !$registerSuccess && !$showSignup) {
    $showOtp = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<section id="header">
    <a href="index.php" class="logo-wrap"><div class="logo-mark"></div><span>SolarMart</span></a>
    <ul id="navbar">
        <li><a href="index.php">Home</a></li>
        <li><a href="shop.php">Shop</a></li>
        <li><a href="cart.php">Cart <span class="cart-count">0</span></a></li>
        <a href="#" id="close"><i class="fa fa-times"></i></a>
    </ul>
    <div id="mobile">
        <a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span class="cart-count">0</span></a>
        <i id="bar" class="fas fa-outdent"></i>
    </div>
</section>

<section class="page-header">
    <h1>Customer Login</h1>
    <p>Login or create a secure SolarMart account.</p>
</section>

<section class="section-p1 login-section">
    <div class="login-box">
        <?php if (!empty($loginNotice)): ?><p class="success-alert"><?php echo htmlspecialchars($loginNotice); ?></p><?php endif; ?>

        <form id="loginForm" class="card form-stack login-card <?php echo ($showSignup || $showOtp) ? 'hidden' : ''; ?>" method="POST">
            <h3>Customer Login</h3>
            <?php if ($error != "") { ?><div class="login-alert"><?php echo htmlspecialchars($error); ?></div><?php } ?>
            <?php if (isset($_SESSION['reset_success'])) { ?><div class="success-alert"><?php echo htmlspecialchars($_SESSION['reset_success']); unset($_SESSION['reset_success']); ?></div><?php } ?>
            <?php if ($registerSuccess != "") { ?><div class="success-alert"><?php echo htmlspecialchars($registerSuccess); ?></div><?php } ?>

            <input class="form-input" type="email" name="email" required placeholder="Email address">
            <div class="password-box">
                <input class="form-input" type="password" id="loginPassword" name="password" required placeholder="Password">
                <i class="fa fa-eye" onclick="togglePassword('loginPassword', this)"></i>
            </div>
            <button class="primary-btn" type="submit" name="login">Login</button>
            <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
            <p>Don't have an account?</p>
            <button type="button" class="outline-btn" onclick="showSignup()">Sign Up</button>
            <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">
        </form>

        <form id="signupForm" class="card form-stack login-card <?php echo $showSignup ? '' : 'hidden'; ?>" method="POST">
            <h3>Create Account</h3>
            <?php if ($registerError != "" && !$showOtp) { ?><div class="login-alert"><?php echo htmlspecialchars($registerError); ?></div><?php } ?>
            <input class="form-input" type="text" name="fullname" required placeholder="Full name">
            <input class="form-input" type="email" name="email" required placeholder="Email address">
            <input class="form-input" type="text" name="phone" required placeholder="Phone number">
            <input class="form-input" type="number" name="age" required placeholder="Age">
            <select class="form-input" name="gender" required>
                <option value="">Select gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <input class="form-input" type="text" name="location" required placeholder="City / Location">
            <textarea class="form-input" name="address" required placeholder="Full delivery address"></textarea>
            <div class="password-box">
                <input class="form-input" type="password" id="signupPassword" name="password" required placeholder="Password">
                <i class="fa fa-eye" onclick="togglePassword('signupPassword', this)"></i>
            </div>
            <button class="primary-btn" type="submit" name="register">Send OTP & Create Account</button>
            <p>Already have an account?</p>
            <button type="button" class="outline-btn" onclick="showLogin()">Back to Login</button>
        </form>

        <div id="registerOtpBox" class="card form-stack login-card <?php echo $showOtp ? '' : 'hidden'; ?>">
            <h3>Verify Email OTP</h3>
            <?php if ($otpMessage != "") { ?><div class="success-alert"><?php echo htmlspecialchars($otpMessage); ?></div><?php } ?>
            <?php if ($registerError != "") { ?><div class="login-alert"><?php echo htmlspecialchars($registerError); ?></div><?php } ?>
            <p>Enter the 6-digit OTP sent to your email.</p>
            <div class="otp-timer">OTP expires in <strong id="otpCountdown">01:00</strong></div>
            <form method="POST" class="form-stack">
                <input class="form-input" type="text" name="otp" required maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit OTP">
                <button class="primary-btn" type="submit" name="verify_register_otp">Verify & Finish Registration</button>
            </form>
            <form method="POST">
                <button id="resendOtpBtn" class="outline-btn" type="submit" name="resend_register_otp" disabled>Resend OTP</button>
            </form>
            <button type="button" class="forgot-link" onclick="showSignup()">Change registration details</button>
        </div>
    </div>
</section>

<script src="script.js?v=otp-security-1"></script>
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
function showSignup() {
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("signupForm").classList.remove("hidden");
    document.getElementById("registerOtpBox").classList.add("hidden");
}
function showLogin() {
    document.getElementById("signupForm").classList.add("hidden");
    document.getElementById("registerOtpBox").classList.add("hidden");
    document.getElementById("loginForm").classList.remove("hidden");
}
function startOtpCountdown() {
    const box = document.getElementById('registerOtpBox');
    const timer = document.getElementById('otpCountdown');
    const resend = document.getElementById('resendOtpBtn');
    if (!box || box.classList.contains('hidden') || !timer || !resend) return;
    let seconds = 60;
    resend.disabled = true;
    const interval = setInterval(() => {
        seconds--;
        const mm = String(Math.floor(seconds / 60)).padStart(2, '0');
        const ss = String(seconds % 60).padStart(2, '0');
        timer.textContent = `${mm}:${ss}`;
        if (seconds <= 0) {
            clearInterval(interval);
            timer.textContent = '00:00';
            resend.disabled = false;
        }
    }, 1000);
}
document.addEventListener('DOMContentLoaded', startOtpCountdown);
</script>
</body>
</html>
