<?php
session_start();
include "db.php";
$isLoggedIn = isset($_SESSION["user_id"]);

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
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section id="header"><a href="index.php" class="logo-wrap"><div class="logo-mark"></div><span>SolarMart</span></a>
        <ul id="navbar">
            <li><a href="index.php">Home</a></li><li><a href="shop.php">Shop</a></li><li><a href="blog.php">Blog</a></li>
            <li><a href="about.php">About</a></li><li><a href="contact.php">Contact</a></li><li><a href="login.php">Login</a></li>
            <li id="lg-bag"><a class="active" href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span class="cart-count">0</span></a></li>
            <a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
        <div id="mobile"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span class="cart-count">0</span></a><i id="bar" class="fas fa-outdent"></i></div>
    </section>
    <section class="page-header"><h1>Your Cart</h1><p>Review quantities and totals before checkout.</p></section>
    <section class="section-p1">
        <div class="cart-page-shell">
            <div class="cart-items-panel">
                <table class="cart-table">
                    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th>Remove</th></tr></thead>
                    <tbody id="cartBody"></tbody>
                </table>
            </div>
            <div id="cartSummary" class="card cart-summary"></div>
        </div>
    </section>
    <script>
        window.SOLAR_PRODUCTS = <?php echo json_encode($productRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        window.SOLAR_IS_LOGGED_IN = <?php echo $isLoggedIn ? "true" : "false"; ?>;
    </script>
    <script src="script.js?v=checkout-cart-fix-2"></script>
</body>
</html>
