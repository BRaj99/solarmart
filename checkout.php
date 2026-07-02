<?php
session_start();
include "db.php";
include "mail_helper.php";

if (!isset($_SESSION["user_id"])) {
    $_SESSION["login_notice"] = "Please login before purchasing items.";
    header("Location: login.php?next=checkout.php");
    exit();
}

$message = "";
$error = "";
$orderNumber = "";
$invoiceHtml = "";
$emailStatus = "";

function cleanInput($value) {
    return trim($value ?? "");
}

function formatRsPhp($value) {
    return "Rs " . number_format((float)$value, 2);
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

function buildInvoiceHtml($orderNumber, $customerName, $customerEmail, $customerPhone, $deliveryAddress, $paymentMethod, $items, $subtotal, $shipping, $tax, $grandTotal) {
    $rows = "";
    foreach ($items as $item) {
        $rows .= "<tr>
            <td style='padding:10px;border-bottom:1px solid #ddd;'>" . htmlspecialchars($item["name"]) . "</td>
            <td style='padding:10px;border-bottom:1px solid #ddd;text-align:center;'>" . (int)$item["qty"] . "</td>
            <td style='padding:10px;border-bottom:1px solid #ddd;text-align:right;'>" . formatRsPhp($item["price"]) . "</td>
            <td style='padding:10px;border-bottom:1px solid #ddd;text-align:right;'>" . formatRsPhp($item["line_total"]) . "</td>
        </tr>";
    }

    return "
    <div style='font-family:Arial,sans-serif;max-width:760px;margin:auto;color:#18221D;'>
        <div style='background:#0F3324;color:white;padding:22px;border-radius:16px 16px 0 0;'>
            <h2 style='margin:0;color:white;'>SolarMart Invoice</h2>
            <p style='margin:8px 0 0;color:#d7f2e3;'>Invoice No: <strong>" . htmlspecialchars($orderNumber) . "</strong></p>
        </div>
        <div style='border:1px solid #DCEBDD;border-top:0;padding:22px;border-radius:0 0 16px 16px;'>
            <h3>Customer Details</h3>
            <p><strong>Name:</strong> " . htmlspecialchars($customerName) . "<br>
            <strong>Email:</strong> " . htmlspecialchars($customerEmail) . "<br>
            <strong>Phone:</strong> " . htmlspecialchars($customerPhone) . "<br>
            <strong>Address:</strong> " . htmlspecialchars($deliveryAddress) . "<br>
            <strong>Payment:</strong> " . htmlspecialchars($paymentMethod) . "</p>

            <table style='width:100%;border-collapse:collapse;margin-top:16px;'>
                <thead>
                    <tr style='background:#E8F8EF;'>
                        <th style='padding:10px;text-align:left;'>Product</th>
                        <th style='padding:10px;text-align:center;'>Qty</th>
                        <th style='padding:10px;text-align:right;'>Price</th>
                        <th style='padding:10px;text-align:right;'>Total</th>
                    </tr>
                </thead>
                <tbody>$rows</tbody>
            </table>

            <div style='margin-top:18px;text-align:right;'>
                <p>Subtotal: <strong>" . formatRsPhp($subtotal) . "</strong></p>
                <p>Shipping: <strong>" . ($shipping > 0 ? formatRsPhp($shipping) : "Free") . "</strong></p>
                <p>VAT: <strong>" . formatRsPhp($tax) . "</strong></p>
                <h2 style='color:#1B8A5A;'>Grand Total: " . formatRsPhp($grandTotal) . "</h2>
            </div>
            <p style='margin-top:20px;color:#66756D;'>Thank you for shopping with SolarMart.</p>
        </div>
    </div>";
}

function sendInvoiceEmail($to, $customerName, $orderNumber, $invoiceHtml) {
    return sendInvoicePdfEmail($to, $customerName, $orderNumber, $invoiceHtml);
}

$userId = (int)$_SESSION["user_id"];
$defaultName = $_SESSION["user_name"] ?? "";
$defaultEmail = $_SESSION["user_email"] ?? "";
$defaultPhone = $_SESSION["user_phone"] ?? "";
$defaultAddress = $_SESSION["user_address"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerName = $defaultName;
    $customerEmail = $defaultEmail;
    $customerPhone = cleanInput($_POST["customer_phone"] ?? $defaultPhone);
    $deliveryAddress = cleanInput($_POST["delivery_address"] ?? $defaultAddress);
    $paymentMethod = cleanInput($_POST["payment_method"] ?? "Cash on Delivery");
    $cartData = $_POST["cart_data"] ?? "";
    if (trim($cartData) === "" || trim($cartData) === "[]") {
        $cartData = isset($_COOKIE["solarCart"]) ? urldecode($_COOKIE["solarCart"]) : "[]";
    }
    $cartItems = json_decode($cartData, true);

    if ($customerName === "" || $customerEmail === "" || $customerPhone === "" || $deliveryAddress === "") {
        $error = "Please make sure your phone and delivery address are filled.";
    } elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Your registered email is not valid.";
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
                if ($productId <= 0 || $qty <= 0) continue;

                $stmt = mysqli_prepare($conn, "SELECT id, name, price, stock FROM products WHERE id=? FOR UPDATE");
                mysqli_stmt_bind_param($stmt, "i", $productId);
                mysqli_stmt_execute($stmt);
                $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                if (!$product) throw new Exception("One product in your cart no longer exists.");
                if ((int)$product["stock"] < $qty) throw new Exception($product["name"] . " has only " . (int)$product["stock"] . " item(s) available.");

                $price = (float)$product["price"];
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;
                $validItems[] = ["id" => (int)$product["id"], "name" => $product["name"], "price" => $price, "qty" => $qty, "line_total" => $lineTotal];
            }

            if (count($validItems) === 0) throw new Exception("Your cart is empty.");

            $shipping = $subtotal > 200000 ? 0 : 1500;
            $tax = round($subtotal * 0.13);
            $grandTotal = $subtotal + $shipping + $tax;
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
            $emailStatus = "Invoice will be sent to your email after the order status is marked as Delivered.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

$productRows = getProductsForJs($conn);
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

    <section class="page-header"><h1>Checkout</h1><p>Only logged-in customers can place orders. Invoice will be emailed after your order is delivered.</p></section>

    <section class="section-p1 checkout-layout">
        <form id="checkoutForm" class="card form-stack checkout-card" method="POST">
            <h3>Billing & Delivery</h3>
            <?php if ($message): ?><p class="success-message"><?php echo htmlspecialchars($message); ?></p><p class="success-message"><?php echo htmlspecialchars($emailStatus); ?></p><script>localStorage.removeItem('solarCart'); document.cookie='solarCart=; path=/; max-age=0';</script><?php endif; ?>
            <?php if ($error): ?><p class="error-message"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

            <label>Registered Customer</label>
            <input class="form-input" readonly value="<?php echo htmlspecialchars($defaultName); ?>">
            <label>Registered Email</label>
            <input class="form-input" readonly value="<?php echo htmlspecialchars($defaultEmail); ?>">
            <label>Phone</label>
            <input class="form-input" name="customer_phone" required placeholder="Phone" value="<?php echo htmlspecialchars($defaultPhone); ?>">
            <label>Delivery Address</label>
            <input class="form-input" name="delivery_address" required placeholder="Delivery address" value="<?php echo htmlspecialchars($defaultAddress); ?>">
            <label>Payment Method</label>
            <select class="form-input" name="payment_method">
                <option>Cash on Delivery</option>
                <option>Bank Transfer</option>
                <option>Digital Wallet</option>
            </select>
            <input type="hidden" name="cart_data" id="cartDataInput">
            <button class="primary-btn" type="submit">Place Order</button>
        </form>

        <div class="card checkout-card">
            <h3>Order Summary</h3>
            <div id="checkoutSummary"><p>Your checkout summary will appear here.</p></div>

        </div>
    </section>

    <script>
        window.SOLAR_PRODUCTS = <?php echo json_encode($productRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        window.SOLAR_IS_LOGGED_IN = true;
    </script>
    <script src="script.js?v=checkout-cart-fix-2"></script>
</body>
</html>
