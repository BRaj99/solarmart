<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT o.*, GROUP_CONCAT(CONCAT(oi.product_name, ' x ', oi.quantity) SEPARATOR '<br>') AS items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);

function statusClass($status) {
    $status = strtolower($status ?? 'pending');
    if ($status === 'delivered') return 'delivered';
    if ($status === 'processing') return 'processing';
    if ($status === 'cancelled') return 'cancelled';
    return 'pending';
}

function safeSession($key) {
    return htmlspecialchars($_SESSION[$key] ?? '');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<section id="header">
    <a href="index.php" class="logo-wrap">
        <div class="logo-mark"></div>
        <span>SolarMart</span>
    </a>

    <ul id="navbar">
        <li><a href="index.php">Home</a></li>
        <li><a href="shop.php">Shop</a></li>
        <li><a href="cart.php">Cart <span class="cart-count">0</span></a></li>
        <li><a class="active" href="profile.php"><i class="fa-solid fa-user"></i> My Account</a></li>
        <a href="#" id="close"><i class="fa fa-times"></i></a>
    </ul>

    <div id="mobile">
        <a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span class="cart-count">0</span></a>
        <i id="bar" class="fas fa-outdent"></i>
    </div>
</section>

<section class="page-header">
    <h1>My Account</h1>
    <p>View your profile, order status, and previous order history.</p>
</section>

<section class="section-p1 customer-account-grid">
    <div class="card profile-card-box">
        <h3>Customer Profile</h3>

        <p><strong>Name:</strong> <?php echo safeSession('user_name'); ?></p>
        <p><strong>Email:</strong> <?php echo safeSession('user_email'); ?></p>
        <p><strong>Phone:</strong> <?php echo safeSession('user_phone'); ?></p>
        <p><strong>Age:</strong> <?php echo safeSession('user_age'); ?></p>
        <p><strong>Gender:</strong> <?php echo safeSession('user_gender'); ?></p>
        <p><strong>Location:</strong> <?php echo safeSession('user_location'); ?></p>
        <p><strong>Delivery Address:</strong> <?php echo safeSession('user_address'); ?></p>

        <br>
        <a href="login.php?logout=true" class="primary-btn">Logout</a>
    </div>

    <div class="card order-history-card">
        <div class="section-title-row">
            <div>
                <h3>My Orders</h3>
                <p>Track your current order status and view previous orders.</p>
            </div>
            <a href="shop.php" class="outline-btn">Continue Shopping</a>
        </div>

        <div class="table-wrap">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Order No.</th>
                        <th>Products</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                            <td><?php echo $order['items'] ?: 'No items'; ?></td>
                            <td>Rs <?php echo number_format((float)$order['grand_total']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td>
                                <span class="order-status <?php echo statusClass($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date("M d, Y h:i A", strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            You have not placed any orders yet. <a href="shop.php">Shop products now</a>.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script src="script.js"></script>
</body>
</html>
