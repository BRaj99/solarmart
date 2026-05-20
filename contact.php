<?php
 session_start(); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <section id="header"><a href="index.php" class="logo-wrap">
            <div class="logo-mark"></div><span>SolarMart</span>
        </a>
        <ul id="navbar">
            <li><a href="index.php">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="about.php">About</a></li>
            <li><a class="active" href="contact.php">Contact</a></li>
            <li id="lg-bag"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span
                        class="cart-count">0</span></a></li><a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
        <div id="mobile"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span
                    class="cart-count">0</span></a><i id="bar" class="fas fa-outdent"></i></div>
    </section>
    <section class="page-header">
        <h1>Contact Us</h1>
        <p>Ask about products, installation packages or delivery. This form is a front-end demo.</p>
    </section>
    <section class="section-p1 contact-grid">
        <div class="card">
            <h3>Store Details</h3>
            <p><strong>Address:</strong> Kathmandu, Nepal</p>
            <p><strong>Email:</strong> support@solarmart.demo</p>
            <p><strong>Phone:</strong> +977-9800000000</p>
            <p><strong>Hours:</strong> 9:00 - 18:00, Sun-Fri</p>
        </div>
        <form class="card form-stack" data-demo data-message="Message sent successfully!">
            <h3>Send Message</h3><input class="form-input" required placeholder="Your name"><input class="form-input"
                type="email" required placeholder="Email address"><input class="form-input"
                placeholder="Subject"><textarea required placeholder="How can we help?"></textarea><button
                class="primary-btn">Send Message</button>
        </form>
    </section>
    <script src="script.js"></script>
</body>

</html>