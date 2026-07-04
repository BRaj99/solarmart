<?php
session_start();
include "db.php";
include "otp_helper.php";

ensureOtpTable($conn);

$message = "";
$messageType = "";
$showOtpForm = isset($_SESSION['reset_email']);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_reset_otp'])) {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, fullname, email FROM users WHERE email=? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $check = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($check) == 1) {
            $user = mysqli_fetch_assoc($check);
            $_SESSION['reset_email'] = $user['email'];
            $_SESSION['reset_name'] = $user['fullname'];
            try {
                createAndSendOtp($conn, $user['email'], $user['fullname'], 'password_reset');
                $message = "Password reset OTP has been sent. It expires in 1 minute.";
                $messageType = "success";
                $showOtpForm = true;
            } catch (Exception $e) {
                $message = $e->getMessage();
                $messageType = "error";
            }
        } else {
            $message = "Email is not registered.";
            $messageType = "error";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resend_reset_otp'])) {
    $email = $_SESSION['reset_email'] ?? '';
    $name = $_SESSION['reset_name'] ?? '';
    $showOtpForm = true;

    if (!$email) {
        $message = "Please enter your email again.";
        $messageType = "error";
        $showOtpForm = false;
    } else {
        try {
            createAndSendOtp($conn, $email, $name, 'password_reset');
            $message = "A new OTP has been sent. It expires in 1 minute.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = "error";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password_with_otp'])) {
    $email = $_SESSION['reset_email'] ?? '';
    $otp = trim($_POST['otp'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $showOtpForm = true;

    if (!$email) {
        $message = "Password reset session expired. Please try again.";
        $messageType = "error";
        $showOtpForm = false;
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (strlen($newPassword) < 6) {
        $message = "Password must be at least 6 characters.";
        $messageType = "error";
    } else {
        $verify = verifyOtpCode($conn, $email, 'password_reset', $otp);
        if (!$verify['ok']) {
            $message = $verify['message'];
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE email=?");
            mysqli_stmt_bind_param($stmt, "ss", $hashedPassword, $email);
            mysqli_stmt_execute($stmt);

            unset($_SESSION['reset_email'], $_SESSION['reset_name']);
            $_SESSION['reset_success'] = "Password reset successful. Please login with your new password.";
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | SolarMart</title>
    <link rel="stylesheet" href="style.css?v=20260704_reviews">
</head>
<body>
<section class="section-p1 login-section">
    <div class="login-box">
        <div class="card form-stack login-card">
            <h3>Forgot Password</h3>

            <?php if ($message != "") { ?>
                <div class="<?php echo ($messageType == 'success') ? 'success-alert' : 'login-alert'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php } ?>

            <?php if (!$showOtpForm) { ?>
                <form method="POST" class="form-stack">
                    <input class="form-input" type="email" name="email" required placeholder="Enter your registered email">
                    <button class="primary-btn" type="submit" name="send_reset_otp">Send OTP</button>
                </form>
            <?php } else { ?>
                <p>Enter the OTP sent to <strong><?php echo htmlspecialchars($_SESSION['reset_email']); ?></strong>.</p>
                <div class="otp-timer">OTP expires in <strong id="otpCountdown">01:00</strong></div>

                <form method="POST" class="form-stack">
                    <input class="form-input" type="text" name="otp" required maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit OTP">
                    <input class="form-input" type="password" name="new_password" required placeholder="New password">
                    <input class="form-input" type="password" name="confirm_password" required placeholder="Confirm password">
                    <button class="primary-btn" type="submit" name="reset_password_with_otp">Verify OTP & Reset Password</button>
                </form>

                <form method="POST">
                    <button id="resendOtpBtn" class="outline-btn" type="submit" name="resend_reset_otp" disabled>Resend OTP</button>
                </form>
            <?php } ?>

            <a href="login.php" class="forgot-link">Back to Login</a>
        </div>
    </div>
</section>
<script>
function startOtpCountdown() {
    const timer = document.getElementById('otpCountdown');
    const resend = document.getElementById('resendOtpBtn');
    if (!timer || !resend) return;
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
