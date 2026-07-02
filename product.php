<?php
require_once "site_common.php";
require_once "db.php";

$id = (int)($_GET["id"] ?? 0);
$product = null;

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, brand, category, price, stock, sku, image, description FROM products WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

if (!$product) {
    http_response_code(404);
}

function productImagePath($image) {
    $image = trim((string)$image);
    if ($image === "") return "images/solar-placeholder.svg";
    if (preg_match('/^https?:\/\//i', $image)) return $image;
    if (strpos($image, "images/") === 0) return $image;
    return "images/" . $image;
}

$description = trim($product["description"] ?? "");
if ($description === "" && $product) {
    $description = "Product description is not available yet. Please contact SolarMart for technical details, warranty information, and installation support.";
}

$productRows = [];
$result = mysqli_query($conn, "SELECT id, name, brand, category, price, stock, image, description FROM products WHERE stock > 0 ORDER BY id DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $productRows[] = [
            "id" => (int)$row["id"],
            "name" => $row["name"],
            "brand" => $row["brand"],
            "category" => $row["category"],
            "price" => (float)$row["price"],
            "stock" => (int)$row["stock"],
            "image" => productImagePath($row["image"]),
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
    <title><?php echo $product ? e($product["name"]) : "Product Not Found"; ?> | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php renderHeader("shop"); ?>

<?php if ($product): ?>
<section class="section-p1 product-detail">
    <div class="product-detail-image">
        <img src="<?php echo e(productImagePath($product["image"])); ?>" alt="<?php echo e($product["name"]); ?>">
    </div>

    <div class="product-detail-info">
        <span class="badge"><?php echo e($product["brand"]); ?> • <?php echo e($product["category"]); ?></span>
        <h1><?php echo e($product["name"]); ?></h1>
        <h2>Rs <?php echo number_format((float)$product["price"]); ?></h2>

        <div class="product-description-box">
            <h3>Product Description</h3>
            <p><?php echo nl2br(e($description)); ?></p>
        </div>

        <div class="product-meta">
            <p><strong>SKU:</strong> <?php echo e($product["sku"]); ?></p>
            <p><strong>Stock:</strong> <?php echo (int)$product["stock"]; ?> available</p>
        </div>

        <div class="product-actions">
            <button class="primary-btn add-cart detail-cart-btn" data-id="<?php echo (int)$product["id"]; ?>">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
            </button>
            <a class="outline-btn" href="shop.php">Back to Shop</a>
        </div>
    </div>
</section>
<?php else: ?>
<section class="section-p1">
    <div class="card" style="max-width:720px;margin:auto;text-align:center;">
        <h1>Product Not Found</h1>
        <p>The product you are looking for is unavailable.</p>
        <a class="primary-btn" href="shop.php">Back to Shop</a>
    </div>
</section>
<?php endif; ?>

<?php renderFooter(); ?>

<script>
window.SOLAR_PRODUCTS = <?php echo json_encode($productRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>
<script src="script.js"></script>
</body>
</html>
