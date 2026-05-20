<?php
session_start();
include "db.php";

$productRows = [];
$productResult = mysqli_query($conn, "SELECT id, name, brand, category, price, stock, image, description FROM products WHERE stock > 0 ORDER BY id DESC");
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
            "desc" => $row["description"]
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section id="header"><a href="index.php" class="logo-wrap">
            <div class="logo-mark"></div><span>SolarMart</span>
        </a>
        <ul id="navbar">
            <li><a href="index.php">Home</a></li>
            <li><a class="active" href="shop.php">Shop</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="login.php">Login</a></li>
            <li id="lg-bag"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span class="cart-count">0</span></a></li>
            <a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
        <div id="mobile"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span class="cart-count">0</span></a><i id="bar" class="fas fa-outdent"></i></div>
    </section>

    <section class="page-header"><span class="badge"><i class="fa fa-solar-panel"></i> Solar Store</span>
        <h1>Shop Products</h1>
        <p>Products are loaded from MySQL. Customers can add products to cart and buy them at checkout.</p>
    </section>

    <section id="product1" class="section-p1">
        <div class="shop-tools">
            <input id="searchProducts" type="search" placeholder="Search panels, batteries, inverter...">
            <select id="categoryFilter">
                <option>All</option><option>Panels</option><option>Inverters</option><option>Batteries</option>
                <option>Lights</option><option>Kits</option><option>Accessories</option>
            </select>
            <select id="sortProducts">
                <option value="default">Sort: Featured</option>
                <option value="low">Price: Low to High</option>
                <option value="high">Price: High to Low</option>
            </select>
        </div>
        <div class="pro-container" id="product-list"></div>
    </section>

    <section id="newsletter" class="section-p1 section-m1">
        <div><h4>Need help choosing?</h4><p>Send us your load details and roof size for a demo recommendation.</p></div>
        <a class="normal" href="contact.php">Contact Us</a>
    </section>

    <script>
        window.SOLAR_PRODUCTS = <?php echo json_encode($productRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
