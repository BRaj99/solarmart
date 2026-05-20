<?php
include "db.php";

$message = "";
$resetLink = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($check) == 1) {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash("sha256", $token);

        mysqli_query($conn, "UPDATE users 
            SET reset_token='$hashedToken',
                reset_expires=DATE_ADD(NOW(), INTERVAL 1 HOUR)
            WHERE email='$email'");

        $resetLink = "http://localhost/solarmart/reset_password.php?token=$token";
        $message = "Password reset link generated successfully.";
    } else {
        $message = "Email is not registered.";
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
                <div class="<?php echo ($resetLink != "") ? 'success-alert' : 'login-alert'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php } ?>

            <?php if ($resetLink != "") { ?>
                <div class="reset-link-box">
                    <p>Click this link to reset your password:</p>
                    <a href="<?php echo $resetLink; ?>">Reset Password</a>
                </div>
            <?php } else { ?>
                <form method="POST" class="form-stack">
                    <input class="form-input" type="email" name="email" required placeholder="Enter your email">
                    <button class="primary-btn" type="submit">Send Reset Link</button>
                </form>
            <?php } ?>

            <a href="login.php" class="forgot-link">Back to Login</a>
        </div>
    </div>
</section>

</body>
</html>