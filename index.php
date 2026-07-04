<?php
require_once "site_common.php";
require_once "db.php";

$productRows = [];
$productResult = mysqli_query($conn, "
    SELECT p.id, p.name, p.brand, p.category, p.price, p.stock, p.image, p.description,
           COALESCE(SUM(oi.quantity), 0) AS sold_qty
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.stock > 0
    GROUP BY p.id, p.name, p.brand, p.category, p.price, p.stock, p.image, p.description
    ORDER BY sold_qty DESC, p.id DESC
    LIMIT 4
");
if ($productResult) {
    while ($row = mysqli_fetch_assoc($productResult)) {
        $productRows[] = [
            "id" => (int)$row["id"],
            "name" => $row["name"],
            "brand" => $row["brand"],
            "category" => $row["category"],
            "price" => (float)$row["price"],
            "stock" => (int)$row["stock"],
            "image" => $row["image"],
            "desc" => $row["description"],
            "sold" => (int)$row["sold_qty"]
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SolarMart | Renewable Energy Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=20260704_reviews">
</head>

<body>

<?php renderHeader("home"); ?>

<section id="picture1">
    <div class="hero-copy">
        <span class="badge">
            <i class="fa-solid fa-sun"></i> Clean power for every home
        </span>

        <h4>Solar products and installation essentials</h4>
        <h1>Power your future with <span>sunshine</span></h1>

        <p>
            Explore panels, inverters, batteries, lights and complete solar kits.
        </p>

        <div class="hero-actions">
            <a class="primary-btn" href="shop.php">Shop Solar Products</a>
            <a class="outline-btn" href="about.php">Learn More</a>
        </div>
    </div>

    <div class="hero-visual">
        <div class="sun-orb"></div>
        <div class="panel-grid">
            <span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span>
        </div>
    </div>
</section>

<section class="section-p1 stats-strip">
    <div class="stat-card">
        <strong>25+</strong>
        <p>Solar product types</p>
    </div>

    <div class="stat-card">
        <strong>13%</strong>
        <p>VAT calculated at checkout</p>
    </div>

    <div class="stat-card">
        <strong>24/7</strong>
        <p>Customer support</p>
    </div>
</section>

<section id="feature" class="section-p1">
    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-truck-fast"></i></div>
        <h6>Fast Delivery</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-mobile-screen"></i></div>
        <h6>Online Order</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-piggy-bank"></i></div>
        <h6>Save Money</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-screwdriver-wrench"></i></div>
        <h6>Install Support</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-leaf"></i></div>
        <h6>Eco Friendly</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-headset"></i></div>
        <h6>24/7 Support</h6>
    </div>
</section>

<section id="product1" class="section-p1">
    <h2>Featured Solar Products</h2>
    <p>Top 4 highest sold renewable energy products for homes, farms and businesses.</p>
    <div class="pro-container" id="product-list"></div>
</section>

<section id="banner" class="section-m1">
    <h4>Solar Services</h4>
    <h2>Get clean energy with <span>smart products</span></h2>
    <a class="normal" href="shop.php">Shop Now</a>
</section>

<section id="newsletter" class="section-p1 section-m1">
    <div class="newstext">
        <h4>Sign Up For Solar Updates</h4>
        <p>Get email updates about new solar products and <span>special offers</span></p>
    </div>

    <form class="form" data-demo data-message="Thanks for subscribing!">
        <input type="email" placeholder="Your email address" required>
        <button class="normal">Sign Up</button>
    </form>
</section>

<?php renderFooter(); ?>

<script>
window.SOLAR_PRODUCTS = <?php echo json_encode($productRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>
<script src="script.js?v=20260704_reviews"></script>
</body>
</html>