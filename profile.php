<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<section id="header">
    <a href="index.php" class="logo-wrap">
        <div class="logo-mark"></div>
        <span>SolarMart</span>
    </a>

    <ul id="navbar">
        <li><a href="index.php">Home</a></li>
        <li><a href="shop.php">Shop</a></li>
        <li><a href="cart.php">Cart <span class="cart-count">0</span></a></li>
        <li>
            <a class="active profile-icon" href="profile.php">
                <i class="fa-solid fa-user"></i>
            </a>
        </li>
    </ul>
</section>

<section class="page-header">
    <h1>My Profile</h1>
    <p>View your SolarMart customer account details.</p>
</section>

<section class="section-p1 login-section">
    <div class="login-box">
        <div class="card login-card">
            <h3>Customer Profile</h3>

            <p><strong>Name:</strong> <?php echo $_SESSION['user_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $_SESSION['user_email']; ?></p>
            <p><strong>Phone:</strong> <?php echo $_SESSION['user_phone']; ?></p>
            <p><strong>Age:</strong> <?php echo $_SESSION['user_age']; ?></p>
            <p><strong>Gender:</strong> <?php echo $_SESSION['user_gender']; ?></p>
            <p><strong>Location:</strong> <?php echo $_SESSION['user_location']; ?></p>
            <p><strong>Delivery Address:</strong> <?php echo $_SESSION['user_address']; ?></p>

            <br>

            <a href="login.php?logout=true" class="primary-btn">Logout</a>
        </div>
    </div>
</section>

<script src="script.js"></script>
</body>
</html>