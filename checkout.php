<?php
session_start();
include "db.php";

$message = "";
$error = "";
$orderNumber = "";

function cleanInput($value) {
    return trim($value ?? "");
}

function getProductsForJs($conn) {
    $rows = [];
    $result = mysqli_query($conn, "SELECT id, name, brand, category, price, stock, image, description FROM products WHERE stock > 0 ORDER BY id DESC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
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
    return $rows;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerName = cleanInput($_POST["customer_name"] ?? "");
    $customerEmail = cleanInput($_POST["customer_email"] ?? "");
    $customerPhone = cleanInput($_POST["customer_phone"] ?? "");
    $deliveryAddress = cleanInput($_POST["delivery_address"] ?? "");
    $paymentMethod = cleanInput($_POST["payment_method"] ?? "Cash on Delivery");
    $cartData = $_POST["cart_data"] ?? "[]";
    $cartItems = json_decode($cartData, true);

    if ($customerName === "" || $customerEmail === "" || $customerPhone === "" || $deliveryAddress === "") {
        $error = "Please fill all billing and delivery fields.";
    } elseif (!is_array($cartItems) || count($cartItems) === 0) {
        $error = "Your cart is empty. Please add products before checkout.";
    } else {
        mysqli_begin_transaction($conn);

        try {
            $subtotal = 0;
            $validItems = [];

            foreach ($cartItems as $item) {
                $productId = (int)($item["id"] ?? 0);
                $qty = (int)($item["qty"] ?? 0);

                if ($productId <= 0 || $qty <= 0) {
                    continue;
                }

                $stmt = mysqli_prepare($conn, "SELECT id, name, price, stock FROM products WHERE id=? FOR UPDATE");
                mysqli_stmt_bind_param($stmt, "i", $productId);
                mysqli_stmt_execute($stmt);
                $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                if (!$product) {
                    throw new Exception("One product in your cart no longer exists.");
                }

                if ((int)$product["stock"] < $qty) {
                    throw new Exception($product["name"] . " has only " . (int)$product["stock"] . " item(s) available.");
                }

                $price = (float)$product["price"];
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                $validItems[] = [
                    "id" => (int)$product["id"],
                    "name" => $product["name"],
                    "price" => $price,
                    "qty" => $qty,
                    "line_total" => $lineTotal
                ];
            }

            if (count($validItems) === 0) {
                throw new Exception("Your cart is empty.");
            }

            $shipping = $subtotal > 200000 ? 0 : 1500;
            $tax = round($subtotal * 0.13);
            $grandTotal = $subtotal + $shipping + $tax;
            $userId = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : null;
            $orderNumber = "ORD-" . date("YmdHis") . "-" . rand(100, 999);
            $status = "Pending";

            $stmt = mysqli_prepare($conn, "INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, delivery_address, payment_method, subtotal, shipping, tax, grand_total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sisssssdddds", $orderNumber, $userId, $customerName, $customerEmail, $customerPhone, $deliveryAddress, $paymentMethod, $subtotal, $shipping, $tax, $grandTotal, $status);
            mysqli_stmt_execute($stmt);
            $orderId = mysqli_insert_id($conn);

            foreach ($validItems as $item) {
                $stmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iisddd", $orderId, $item["id"], $item["name"], $item["price"], $item["qty"], $item["line_total"]);
                mysqli_stmt_execute($stmt);

                $stmt = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "ii", $item["qty"], $item["id"]);
                mysqli_stmt_execute($stmt);
            }

            mysqli_commit($conn);
            $message = "Order placed successfully. Your order number is " . $orderNumber . ".";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

$productRows = getProductsForJs($conn);
$defaultName = $_SESSION["user_name"] ?? "";
$defaultEmail = $_SESSION["user_email"] ?? "";
$defaultPhone = $_SESSION["user_phone"] ?? "";
$defaultAddress = $_SESSION["user_address"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section id="header"><a href="index.php" class="logo-wrap"><div class="logo-mark"></div><span>SolarMart</span></a>
        <ul id="navbar">
            <li><a href="index.php">Home</a></li><li><a href="shop.php">Shop</a></li><li><a href="cart.php">Cart <span class="cart-count">0</span></a></li>
            <a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
        <div id="mobile"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span class="cart-count">0</span></a><i id="bar" class="fas fa-outdent"></i></div>
    </section>

    <section class="page-header">
        <h1>Checkout</h1>
        <p>Place your order. The order will be saved in MySQL and visible to admin.</p>
    </section>

    <section class="section-p1 grid-2">
        <form id="checkoutForm" class="card form-stack" method="POST">
            <h3>Billing & Delivery</h3>

            <?php if ($message): ?>
                <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
                <script>localStorage.removeItem('solarCart');</script>
            <?php endif; ?>

            <?php if ($error): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <input class="form-input" name="customer_name" required placeholder="Full name" value="<?php echo htmlspecialchars($defaultName); ?>">
            <input class="form-input" type="email" name="customer_email" required placeholder="Email" value="<?php echo htmlspecialchars($defaultEmail); ?>">
            <input class="form-input" name="customer_phone" required placeholder="Phone" value="<?php echo htmlspecialchars($defaultPhone); ?>">
            <input class="form-input" name="delivery_address" required placeholder="Delivery address" value="<?php echo htmlspecialchars($defaultAddress); ?>">
            <select class="form-input" name="payment_method">
                <option>Cash on Delivery</option>
                <option>Bank Transfer</option>
                <option>Digital Wallet</option>
            </select>

            <input type="hidden" name="cart_data" id="cartDataInput">
            <button class="primary-btn" type="submit">Place Order</button>
        </form>

        <div class="card">
            <h3>Order Summary</h3>
            <div id="checkoutSummary"><p>Your checkout summary will appear here.</p></div>
            <p>After placing an order, admin can see customer name, phone, products, quantity, total and status in admin dashboard.</p>
        </div>
    </section>

    <script>
        window.SOLAR_PRODUCTS = <?php echo json_encode($productRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
