<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | SolarMart</title>
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
            <li><a class="active" href="blog.php">Blog</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li id="lg-bag"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span
                        class="cart-count">0</span></a></li><a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
        <div id="mobile"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span
                    class="cart-count">0</span></a><i id="bar" class="fas fa-outdent"></i></div>
    </section>
    <section class="page-header">
        <h1>Solar Blog</h1>
        <p>Helpful buying guides and renewable energy tips for customers.</p>
    </section>
    <section class="section-p1 blog-grid">
        <article class="card blog-card">
            <div class="blog-img"><i class="fa fa-solar-panel"></i></div>
            <h3>How to choose a solar panel</h3>
            <p>Compare wattage, efficiency, warranty and roof space before buying.</p><a class="outline-btn"
                href="#">Read More</a>
        </article>
        <article class="card blog-card">
            <div class="blog-img"><i class="fa fa-car-battery"></i></div>
            <h3>Battery backup basics</h3>
            <p>Understand capacity, voltage and daily energy needs for backup systems.</p><a class="outline-btn"
                href="#">Read More</a>
        </article>
        <article class="card blog-card">
            <div class="blog-img"><i class="fa fa-bolt"></i></div>
            <h3>Inverter buying guide</h3>
            <p>Pick the right inverter size for appliances, surge load and battery support.</p><a class="outline-btn"
                href="#">Read More</a>
        </article>
    </section>
    <script src="script.js"></script>
</body>

</html>