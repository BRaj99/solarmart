<?php
session_start();
include "db.php";

$message = "";
$error = "";

function clean($value) {
    return trim($value ?? "");
}

function uploadProductImage($fileInputName, $oldImage = "") {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return $oldImage ?: "images/solar-placeholder.svg";
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    $originalName = $_FILES[$fileInputName]['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed)) {
        return $oldImage ?: "images/solar-placeholder.svg";
    }

    $uploadDir = __DIR__ . "/images/products/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newName = "product_" . time() . "_" . rand(1000, 9999) . "." . $extension;
    $targetPath = $uploadDir . $newName;

    if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetPath)) {
        return "images/products/" . $newName;
    }

    return $oldImage ?: "images/solar-placeholder.svg";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? "";

    if ($action === "add") {
        $name = clean($_POST['name']);
        $brand = clean($_POST['brand']);
        $category = clean($_POST['category']);
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $sku = clean($_POST['sku']);
        $description = clean($_POST['description']);
        $image = uploadProductImage('image');

        if ($name === "" || $category === "" || $price <= 0 || $sku === "") {
            $error = "Please fill product name, category, price, and SKU.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO products (name, brand, category, price, stock, sku, image, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssdisss", $name, $brand, $category, $price, $stock, $sku, $image, $description);
            $message = mysqli_stmt_execute($stmt) ? "Product added successfully." : "Could not add product. SKU may already exist.";
        }
    }

    if ($action === "update") {
        $id = (int) ($_POST['id'] ?? 0);
        $name = clean($_POST['name']);
        $brand = clean($_POST['brand']);
        $category = clean($_POST['category']);
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $sku = clean($_POST['sku']);
        $description = clean($_POST['description']);
        $oldImage = clean($_POST['old_image']);
        $image = uploadProductImage('image', $oldImage);

        if ($id <= 0 || $name === "" || $category === "" || $price <= 0 || $sku === "") {
            $error = "Please fill product name, category, price, and SKU.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE products SET name=?, brand=?, category=?, price=?, stock=?, sku=?, image=?, description=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssdisssi", $name, $brand, $category, $price, $stock, $sku, $image, $description, $id);
            $message = mysqli_stmt_execute($stmt) ? "Product updated successfully." : "Could not update product.";
        }
    }

    if ($action === "delete") {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            $message = mysqli_stmt_execute($stmt) ? "Product deleted successfully." : "Could not delete product.";
        }
    }

    if ($action === "update_order_status") {
        $orderId = (int)($_POST["order_id"] ?? 0);
        $status = clean($_POST["status"] ?? "Pending");
        $allowedStatuses = ["Pending", "Processing", "Delivered", "Cancelled"];

        if ($orderId > 0 && in_array($status, $allowedStatuses)) {
            $stmt = mysqli_prepare($conn, "UPDATE orders SET status=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "si", $status, $orderId);
            $message = mysqli_stmt_execute($stmt) ? "Order status updated." : "Could not update order status.";
        }
    }
}

$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $editProduct = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products"))['total'] ?? 0;
$lowStock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE stock <= 5"))['total'] ?? 0;
$totalCustomers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='customer'"))['total'] ?? 0;
$totalStockValue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(price * stock),0) AS total FROM products"))['total'] ?? 0;
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders"))['total'] ?? 0;
$totalSales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(grand_total),0) AS total FROM orders WHERE status != 'Cancelled'"))['total'] ?? 0;

$orders = mysqli_query($conn, "
    SELECT o.*, GROUP_CONCAT(CONCAT(oi.product_name, ' x ', oi.quantity) SEPARATOR '<br>') AS items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<section id="header">
    <a href="index.php" class="logo-wrap"><div class="logo-mark"></div><span>SolarMart Admin</span></a>
    <ul id="navbar">
        <li><a href="index.php">Storefront</a></li>
        <li><a class="active" href="admin.php">Dashboard</a></li>
        <li><a href="shop.php">Shop</a></li>
        <a href="#" id="close"><i class="fa fa-times"></i></a>
    </ul>
    <div id="mobile"><i id="bar" class="fas fa-outdent"></i></div>
</section>

<main class="admin-layout">
    <aside class="admin-sidebar">
        <h3 style="color:white">Admin Panel</h3>
        <a class="active" href="#dashboard"><i class="fa fa-chart-line"></i> Dashboard</a>
        <a href="#orders"><i class="fa fa-receipt"></i> Customer Orders</a>
        <a href="#products"><i class="fa fa-box"></i> Products</a>
        <a href="#customers"><i class="fa fa-users"></i> Customers</a>
    </aside>

    <section class="admin-main">
        <div id="dashboard">
            <h1>Dashboard</h1>
            <p>Products and customer purchases are loaded from your MySQL database.</p>
            <?php if ($message): ?><p class="success-message"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
            <?php if ($error): ?><p class="error-message"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
            <div class="admin-cards">
                <div class="card metric-card"><p>Total Products</p><h2><?php echo $totalProducts; ?></h2></div>
                <div class="card metric-card"><p>Customer Orders</p><h2><?php echo $totalOrders; ?></h2></div>
                <div class="card metric-card"><p>Customers</p><h2><?php echo $totalCustomers; ?></h2></div>
                <div class="card metric-card"><p>Total Sales</p><h2>Rs <?php echo number_format($totalSales); ?></h2></div>
            </div>
        </div><br>

        <div id="orders" class="card">
            <h3>Products Bought By Customers</h3>
            <p>Every successful checkout appears here.</p>
            <div class="table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Order No.</th><th>Customer</th><th>Contact</th><th>Products Bought</th>
                            <th>Total</th><th>Payment</th><th>Status</th><th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order["order_number"]); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order["customer_name"]); ?></strong><br>
                                    <?php echo htmlspecialchars($order["customer_email"]); ?><br>
                                    <?php echo htmlspecialchars($order["delivery_address"]); ?>
                                </td>
                                <td><?php echo htmlspecialchars($order["customer_phone"]); ?></td>
                                <td><?php echo $order["items"] ?: "No items"; ?></td>
                                <td>Rs <?php echo number_format($order["grand_total"]); ?></td>
                                <td><?php echo htmlspecialchars($order["payment_method"]); ?></td>
                                <td>
                                    <form method="POST" class="inline-status-form">
                                        <input type="hidden" name="action" value="update_order_status">
                                        <input type="hidden" name="order_id" value="<?php echo (int)$order["id"]; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <?php foreach (["Pending", "Processing", "Delivered", "Cancelled"] as $status): ?>
                                                <option value="<?php echo $status; ?>" <?php echo $order["status"] === $status ? "selected" : ""; ?>><?php echo $status; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo date("M d, Y h:i A", strtotime($order["created_at"])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">No customer orders yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div><br>

        <div id="products" class="card">
            <h3><?php echo $editProduct ? "Edit Product" : "Add New Product"; ?></h3>
            <form class="admin-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editProduct ? 'update' : 'add'; ?>">
                <?php if ($editProduct): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$editProduct['id']; ?>">
                    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editProduct['image']); ?>">
                <?php endif; ?>
                <input type="text" name="name" placeholder="Product Name" required value="<?php echo htmlspecialchars($editProduct['name'] ?? ''); ?>">
                <input type="text" name="brand" placeholder="Brand" value="<?php echo htmlspecialchars($editProduct['brand'] ?? ''); ?>">
                <select name="category" required>
                    <?php
                    $categories = ['Panels', 'Inverters', 'Batteries', 'Lights', 'Kits', 'Accessories'];
                    $selectedCategory = $editProduct['category'] ?? '';
                    echo '<option value="">Select Category</option>';
                    foreach ($categories as $cat) {
                        $selected = ($selectedCategory === $cat) ? 'selected' : '';
                        echo '<option '.$selected.' value="'.htmlspecialchars($cat).'">'.htmlspecialchars($cat).'</option>';
                    }
                    ?>
                </select>
                <input type="number" step="0.01" name="price" placeholder="Price" required value="<?php echo htmlspecialchars($editProduct['price'] ?? ''); ?>">
                <input type="number" name="stock" placeholder="Stock Quantity" required value="<?php echo htmlspecialchars($editProduct['stock'] ?? ''); ?>">
                <input type="text" name="sku" placeholder="SKU Code" required value="<?php echo htmlspecialchars($editProduct['sku'] ?? ''); ?>">
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.svg">
                <textarea name="description" placeholder="Product Description"><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>
                <button type="submit" class="normal"><?php echo $editProduct ? "Update Product" : "Add Product"; ?></button>
                <?php if ($editProduct): ?><a class="outline-btn" href="admin.php#products">Cancel Edit</a><?php endif; ?>
            </form>
            <br>
            <h3>Manage Products</h3>
            <div class="table-wrap">
                <table class="cart-table">
                    <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>SKU</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (mysqli_num_rows($products) > 0): ?>
                        <?php while ($product = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="" style="width:55px;height:45px;object-fit:contain"></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td>Rs <?php echo number_format($product['price']); ?></td>
                                <td><?php echo (int)$product['stock']; ?></td>
                                <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                <td>
                                    <a class="small-btn edit-btn" href="admin.php?edit=<?php echo (int)$product['id']; ?>#products">Edit</a>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                                        <button type="submit" class="small-btn delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No products found. Add your first product above.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div><br>

        <div id="customers" class="card">
            <h3>Customers</h3>
            <p>Registered customer count is loaded from the users table.</p>
            <h2><?php echo $totalCustomers; ?> customers</h2>
            <p>Low stock items: <?php echo $lowStock; ?> | Stock value: Rs <?php echo number_format($totalStockValue); ?></p>
        </div>
    </section>
</main>
<script src="script.js"></script>
</body>
</html>
