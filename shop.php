<?php
require_once "site_common.php";
include "db.php";

$productRows = [];

$productResult = mysqli_query($conn, "SELECT id, name, brand, category, price, stock, image, description FROM products ORDER BY id DESC");

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
    <link rel="stylesheet" href="style.css?v=20260704_reviews">
</head>
<body>

    <?php renderHeader('shop'); ?>

    <section class="page-header">
        <span class="badge">
            <i class="fa fa-solar-panel"></i> Solar Store
        </span>
        <h1>Shop Products</h1>
        <p>Browse solar panels, batteries, inverters, lights, kits and accessories.</p>
    </section>

    <section id="product1" class="section-p1">
        <div class="shop-tools">
            <input id="searchProducts" type="search" placeholder="Search panels, batteries, inverter...">

            <select id="categoryFilter">
                <option>All</option>
                <option>Panels</option>
                <option>Inverters</option>
                <option>Batteries</option>
                <option>Lights</option>
                <option>Kits</option>
                <option>Accessories</option>
            </select>

            <select id="stockFilter">
                <option value="in">In stock only</option>
                <option value="all">Show all</option>
            </select>

            <select id="sortProducts">
                <option value="default">Sort: Newest</option>
                <option value="low">Price: Low to High</option>
                <option value="high">Price: High to Low</option>
                <option value="name">Name: A to Z</option>
            </select>
        </div>

        <div class="pro-container" id="product-list"></div>
        <div class="product-pagination" id="productPagination"></div>
    </section>

    <section id="newsletter" class="section-p1 section-m1">
        <div>
            <h4>Need help choosing?</h4>
            <p>Send us your load details and roof size for a product recommendation.</p>
        </div>
        <a class="normal" href="contact.php">Contact Us</a>
    </section>

    <?php renderFooter(); ?>

    <script>
        window.SOLAR_PRODUCTS = <?php echo json_encode($productRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    </script>
    <script src="script.js?v=20260704_reviews"></script>
</body>
</html>