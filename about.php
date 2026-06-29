<?php require_once 'site_common.php'; ?>
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
<?php renderHeader('about'); ?>
<section class="page-header">
    <h1>About SolarMart</h1>
    <p>A clean ecommerce website for solar products and renewable-energy services.</p>
</section>
<section class="section-p1 grid-2">
    <div class="hero-visual"><div class="sun-orb"></div><div class="panel-grid"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div></div>
    <div>
        <span class="badge">Why SolarMart?</span>
        <h2>Designed for sustainable shopping</h2>
        <p>SolarMart helps customers discover panels, batteries, inverters, lights, cables and solar kits with a working cart and checkout experience.</p>
        <p>The shop, checkout, admin dashboard, orders, products, stock and users are connected with MySQL.</p>
        <a class="primary-btn" href="shop.php">Browse Products</a>
        <a class="outline-btn" href="contact.php">Contact Us</a>
    </div>
</section>
<section class="section-p1 info-grid">
    <div class="card"><h3><i class="fa fa-leaf"></i> Eco First</h3><p>Green and sunlight inspired design for solar-energy products.</p></div>
    <div class="card"><h3><i class="fa fa-cart-shopping"></i> Working Store</h3><p>Customers can add items to cart and place orders through checkout.</p></div>
    <div class="card"><h3><i class="fa fa-user-shield"></i> Admin Ready</h3><p>Admin dashboard manages products, orders, customers and stock logs.</p></div>
</section>
<?php renderFooter(); ?>
<script src="script.js"></script>
</body>
</html>
