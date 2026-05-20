<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | SolarMart</title>
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
            <li><a class="active" href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li id="lg-bag"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span
                        class="cart-count">0</span></a></li><a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
        <div id="mobile"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span
                    class="cart-count">0</span></a><i id="bar" class="fas fa-outdent"></i></div>
    </section>
    <section class="page-header">
        <h1>About SolarMart</h1>
        <p>A clean, modern ecommerce front-end for solar products and renewable-energy services.</p>
    </section>
    <section class="section-p1 grid-2">
        <div class="hero-visual">
            <div class="sun-orb"></div>
            <div class="panel-grid">
                <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
        </div>
        <div><span class="badge">Why SolarMart?</span>
            <h2>Designed for sustainable shopping</h2>
            <p>SolarMart helps customers discover panels, batteries, inverters and solar kits with a simple cart and
                checkout experience.</p>
            <p>The project uses static HTML, CSS and JavaScript, so it can run directly in the browser without a backend
                database.</p><a class="primary-btn" href="shop.php">Browse Products</a>
        </div>
    </section>
    <section class="section-p1 info-grid">
        <div class="card">
            <h3><i class="fa fa-leaf"></i> Eco First</h3>
            <p>Green and sunlight inspired colors fit the solar-energy theme.</p>
        </div>
        <div class="card">
            <h3><i class="fa fa-cart-shopping"></i> Demo Store</h3>
            <p>Cart and checkout are powered by localStorage for front-end testing.</p>
        </div>
        <div class="card">
            <h3><i class="fa fa-user-shield"></i> Admin Ready</h3>
            <p>Admin dashboard includes demo product, order and customer sections.</p>
        </div>
    </section>
    <script src="script.js"></script>
</body>

</html>