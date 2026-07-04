<?php
require_once "site_common.php";
require_once "db.php";

$id = (int)($_GET["id"] ?? 0);
$product = null;
$reviewMessage = "";
$reviewError = "";

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_product_user_review (product_id, user_id),
    KEY product_review_product_idx (product_id),
    KEY product_review_user_idx (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_review"]) && $id > 0) {
    if (!isset($_SESSION["user_id"])) {
        $reviewError = "Please login before submitting a review.";
    } else {
        $rating = (int)($_POST["rating"] ?? 0);
        $reviewText = trim($_POST["review"] ?? "");

        if ($rating < 1 || $rating > 5) {
            $reviewError = "Please select a rating from 1 to 5.";
        } elseif ($reviewText === "") {
            $reviewError = "Please write your review.";
        } else {
            $userId = (int)$_SESSION["user_id"];
            $check = mysqli_prepare($conn, "SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
            mysqli_stmt_bind_param($check, "ii", $id, $userId);
            mysqli_stmt_execute($check);
            $existingReview = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

            if ($existingReview) {
                $stmt = mysqli_prepare($conn, "UPDATE product_reviews SET rating = ?, review = ?, updated_at = NOW() WHERE id = ?");
                $reviewId = (int)$existingReview["id"];
                mysqli_stmt_bind_param($stmt, "isi", $rating, $reviewText, $reviewId);
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO product_reviews (product_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iiis", $id, $userId, $rating, $reviewText);
            }

            if (mysqli_stmt_execute($stmt)) {
                $reviewMessage = "Thank you. Your review has been saved.";
            } else {
                $reviewError = "Unable to save review right now.";
            }
        }
    }
}

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

$reviewRows = [];
$avgRating = 0;
$reviewCount = 0;
if ($product) {
    $summaryStmt = mysqli_prepare($conn, "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM product_reviews WHERE product_id = ?");
    mysqli_stmt_bind_param($summaryStmt, "i", $id);
    mysqli_stmt_execute($summaryStmt);
    $summary = mysqli_fetch_assoc(mysqli_stmt_get_result($summaryStmt));
    $avgRating = round((float)($summary["avg_rating"] ?? 0), 1);
    $reviewCount = (int)($summary["total_reviews"] ?? 0);

    $reviewStmt = mysqli_prepare($conn, "
        SELECT pr.rating, pr.review, pr.created_at, u.fullname
        FROM product_reviews pr
        LEFT JOIN users u ON pr.user_id = u.id
        WHERE pr.product_id = ?
        ORDER BY pr.created_at DESC
    ");
    mysqli_stmt_bind_param($reviewStmt, "i", $id);
    mysqli_stmt_execute($reviewStmt);
    $reviewResult = mysqli_stmt_get_result($reviewStmt);
    while ($review = mysqli_fetch_assoc($reviewResult)) {
        $reviewRows[] = $review;
    }
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
    <link rel="stylesheet" href="style.css?v=20260704_reviews">
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
        <div class="product-rating-summary">
            <span class="stars"><?php echo str_repeat("★", (int)round($avgRating)) . str_repeat("☆", 5 - (int)round($avgRating)); ?></span>
            <strong><?php echo $avgRating > 0 ? number_format($avgRating, 1) : "No rating yet"; ?></strong>
            <small>(<?php echo $reviewCount; ?> review<?php echo $reviewCount === 1 ? "" : "s"; ?>)</small>
        </div>

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

<section class="section-p1 product-reviews-section">
    <div class="reviews-layout">
        <div class="review-form-card">
            <h3>Write a Review</h3>
            <?php if ($reviewMessage): ?><div class="success-alert"><?php echo e($reviewMessage); ?></div><?php endif; ?>
            <?php if ($reviewError): ?><div class="error-alert"><?php echo e($reviewError); ?></div><?php endif; ?>

            <?php if (isset($_SESSION["user_id"])): ?>
                <form method="POST" class="review-form">
                    <label>Rating</label>
                    <select name="rating" required>
                        <option value="">Select rating</option>
                        <option value="5">5 Stars - Excellent</option>
                        <option value="4">4 Stars - Very Good</option>
                        <option value="3">3 Stars - Good</option>
                        <option value="2">2 Stars - Fair</option>
                        <option value="1">1 Star - Poor</option>
                    </select>
                    <label>Your Review</label>
                    <textarea name="review" rows="5" placeholder="Share your experience with this product..." required></textarea>
                    <button class="primary-btn" type="submit" name="submit_review">Submit Review</button>
                </form>
            <?php else: ?>
                <p>Please login to review this product.</p>
                <a class="primary-btn" href="login.php">Login to Review</a>
            <?php endif; ?>
        </div>

        <div class="review-list-card">
            <h3>Customer Reviews</h3>
            <?php if ($reviewRows): ?>
                <?php foreach ($reviewRows as $review): ?>
                    <div class="review-item">
                        <div class="review-top">
                            <strong><?php echo e($review["fullname"] ?: "Customer"); ?></strong>
                            <span class="stars"><?php echo str_repeat("★", (int)$review["rating"]) . str_repeat("☆", 5 - (int)$review["rating"]); ?></span>
                        </div>
                        <p><?php echo nl2br(e($review["review"])); ?></p>
                        <small><?php echo date("d M Y", strtotime($review["created_at"])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet. Be the first customer to review this product.</p>
            <?php endif; ?>
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
<script src="script.js?v=20260704_reviews"></script>
</body>
</html>
