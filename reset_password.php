<?php
session_start();
include "db.php";

$message = "";

$token = $_GET['token'] ?? "";

if ($token == "") {
    die("Invalid reset link.");
}

$hashedToken = hash("sha256", $token);

$check = mysqli_query($conn, "SELECT * FROM users
    WHERE reset_token='$hashedToken'
    AND reset_expires > NOW()");

if (mysqli_num_rows($check) != 1) {
    die("Reset link is invalid or expired.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $newPassword = $_POST['new_password'];

    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {

        $message = "Passwords do not match.";

    } else {

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        mysqli_query($conn, "UPDATE users
            SET password='$hashedPassword',
                reset_token=NULL,
                reset_expires=NULL
            WHERE reset_token='$hashedToken'");

        $_SESSION['reset_success'] = "Password reset successful";

        header("Location: login.php");

        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Reset Password | SolarMart</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<section class="section-p1 login-section">

    <div class="login-box">

        <form class="card form-stack login-card" method="POST">

            <h3>Reset Password</h3>

            <?php if ($message != "") { ?>

                <div class="login-alert">

                    <?php echo $message; ?>

                </div>

            <?php } ?>

            <input
                class="form-input"
                type="password"
                name="new_password"
                required
                placeholder="New password"
            >

            <input
                class="form-input"
                type="password"
                name="confirm_password"
                required
                placeholder="Confirm password"
            >

            <button class="primary-btn" type="submit">
                Reset Password
            </button>

        </form>

    </div>

</section>

</body>
</html>