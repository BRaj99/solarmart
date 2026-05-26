<?php
include "db.php";
include "mail_helper.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
            $token = bin2hex(random_bytes(32));
            $hashedToken = hash("sha256", $token);

            $update = mysqli_prepare($conn, "UPDATE users SET reset_token=?, reset_expires=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email=?");
            mysqli_stmt_bind_param($update, "ss", $hashedToken, $email);
            mysqli_stmt_execute($update);

            $resetLink = BASE_URL . "/reset_password.php?token=" . urlencode($token);

            try {
                sendResetPasswordEmail($user['email'], $user['fullname'], $resetLink);
                $message = "Password reset link has been sent to your registered email.";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Reset link was created, but email could not be sent. " . $e->getMessage();
                $messageType = "error";
            }
        } else {
            $message = "Email is not registered.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | SolarMart</title>
    <link rel="stylesheet" href="style.css">
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

            <form method="POST" class="form-stack">
                <input class="form-input" type="email" name="email" required placeholder="Enter your registered email">
                <button class="primary-btn" type="submit">Send Reset Link</button>
            </form>

            <a href="login.php" class="forgot-link">Back to Login</a>
        </div>
    </div>
</section>

</body>
</html>
