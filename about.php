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
    <p>Solar products and renewable-energy services for homes and businesses.</p>
</section>
<section class="section-p1 grid-2">
    <div class="hero-visual"><div class="sun-orb"></div><div class="panel-grid"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div></div>
    <div>
        <span class="badge">Why SolarMart?</span>
        <h2>Built for reliable solar shopping</h2>
        <p>SolarMart helps customers find panels, batteries, inverters, lights, cables and complete solar kits.</p>
        <p>Products, orders, stock and customer details are managed through the system.</p>
        <a class="primary-btn" href="shop.php">Browse Products</a>
        <a class="outline-btn" href="contact.php">Contact Us</a>
    </div>
</section>
<section class="section-p1 info-grid">
    <div class="card"><h3><i class="fa fa-leaf"></i> Eco First</h3><p>Solar products selected for practical renewable-energy use.</p></div>
    <div class="card"><h3><i class="fa fa-cart-shopping"></i> Working Store</h3><p>Customers can add products to cart and place orders online.</p></div>
    <div class="card"><h3><i class="fa fa-user-shield"></i> Admin Ready</h3><p>Admin can manage products, orders, customers and stock.</p></div>
</section>
<?php renderFooter(); ?>
<script src="script.js"></script>
</body>
</html>
