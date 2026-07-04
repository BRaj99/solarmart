<?php include "admin_common.php"; ?>
<?php
$orderStatusFilter = clean($_GET['status'] ?? 'All');
$orderSearch = clean($_GET['q'] ?? '');
$allowedOrderFilters = ['All', 'Pending', 'Processing', 'Delivered', 'Cancelled'];
if (!in_array($orderStatusFilter, $allowedOrderFilters, true)) {
    $orderStatusFilter = 'All';
}

$orderWhere = "WHERE 1=1";
$orderParams = [];
$orderTypes = "";

if ($orderStatusFilter !== 'All') {
    $orderWhere .= " AND o.status = ?";
    $orderParams[] = $orderStatusFilter;
    $orderTypes .= "s";
}

if ($orderSearch !== '') {
    $orderWhere .= " AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.customer_phone LIKE ? OR o.delivery_address LIKE ?)";
    $searchTerm = "%" . $orderSearch . "%";
    for ($i = 0; $i < 5; $i++) {
        $orderParams[] = $searchTerm;
        $orderTypes .= "s";
    }
}

function orderCountByStatus($conn, $status = '') {
    if ($status === '') {
        return (int) singleValue($conn, "SELECT COUNT(*) AS total FROM orders");
    }
    $statusSafe = mysqli_real_escape_string($conn, $status);
    return (int) singleValue($conn, "SELECT COUNT(*) AS total FROM orders WHERE status='$statusSafe'");
}

$allOrdersCount = orderCountByStatus($conn);
$pendingCount = orderCountByStatus($conn, 'Pending');
$processingCount = orderCountByStatus($conn, 'Processing');
$deliveredCount = orderCountByStatus($conn, 'Delivered');
$cancelledCount = orderCountByStatus($conn, 'Cancelled');

$orderSql = "
    SELECT o.*, GROUP_CONCAT(CONCAT(oi.product_name, ' × ', oi.quantity) SEPARATOR '<br>') AS items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    $orderWhere
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

$stmt = mysqli_prepare($conn, $orderSql);
if (!empty($orderParams)) {
    mysqli_stmt_bind_param($stmt, $orderTypes, ...$orderParams);
}
mysqli_stmt_execute($stmt);
$filteredOrders = mysqli_stmt_get_result($stmt);

function statusClass($status) {
    $status = strtolower($status ?: 'pending');
    if ($status === 'delivered') return 'status-delivered';
    if ($status === 'cancelled') return 'status-cancelled';
    if ($status === 'processing') return 'status-processing';
    return 'status-pending';
}
?>
<?php adminPageStart('orders', 'Customer Orders'); ?>

<style>
.order-page-head {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: flex-start;
    margin-bottom: 22px;
}
.order-page-head h1 {
    margin-bottom: 8px;
}
.order-page-head p {
    color: #637381;
    max-width: 720px;
}
.order-summary-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(140px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.order-summary-card {
    background: #fff;
    border: 1px solid #e7edf3;
    border-radius: 18px;
    padding: 16px;
    box-shadow: 0 8px 24px rgba(20, 35, 50, 0.06);
}
.order-summary-card span {
    color: #637381;
    font-size: 13px;
    display: block;
    margin-bottom: 8px;
}
.order-summary-card strong {
    font-size: 24px;
    color: #1f2d3d;
}
.order-filter-card {
    background: #fff;
    border: 1px solid #e7edf3;
    border-radius: 20px;
    padding: 18px;
    margin-bottom: 18px;
    box-shadow: 0 8px 24px rgba(20, 35, 50, 0.06);
}
.order-filter-form {
    display: grid;
    grid-template-columns: 1fr 220px auto auto;
    gap: 12px;
    align-items: center;
}
.order-filter-form input,
.order-filter-form select {
    width: 100%;
    border: 1px solid #d9e2ec;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 14px;
    background: #f8fafc;
}
.order-filter-form button,
.order-filter-form a {
    border: 0;
    border-radius: 12px;
    padding: 12px 16px;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    white-space: nowrap;
}
.order-filter-form button {
    background: #088178;
    color: #fff;
}
.order-filter-form a {
    background: #eef3f8;
    color: #1f2d3d;
}
.order-card-list {
    display: grid;
    gap: 14px;
}
.order-card {
    background: #fff;
    border: 1px solid #e7edf3;
    border-radius: 20px;
    padding: 18px;
    box-shadow: 0 8px 24px rgba(20, 35, 50, 0.06);
}
.order-card-top {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    border-bottom: 1px solid #eef2f6;
    padding-bottom: 14px;
    margin-bottom: 14px;
}
.order-number {
    font-size: 17px;
    color: #1f2d3d;
    font-weight: 800;
}
.order-date {
    color: #637381;
    font-size: 13px;
    margin-top: 5px;
}
.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 800;
}
.status-pending { background: #fff7e6; color: #b26a00; }
.status-processing { background: #eaf4ff; color: #0969b2; }
.status-delivered { background: #e9f9ef; color: #087443; }
.status-cancelled { background: #ffecec; color: #b42318; }
.order-card-body {
    display: grid;
    grid-template-columns: 1.15fr 1fr 0.9fr 1fr;
    gap: 16px;
}
.order-info-block h4 {
    margin: 0 0 8px;
    color: #1f2d3d;
    font-size: 14px;
}
.order-info-block p,
.order-info-block div {
    margin: 0;
    color: #637381;
    font-size: 14px;
    line-height: 1.7;
}
.order-total {
    font-size: 18px;
    color: #088178;
    font-weight: 900;
}
.inline-status-form select {
    width: 100%;
    max-width: 170px;
    border: 1px solid #d9e2ec;
    border-radius: 12px;
    padding: 10px 12px;
    background: #f8fafc;
}
.locked-note {
    color: #637381;
    font-size: 12px;
    margin-top: 6px;
}
.empty-orders {
    text-align: center;
    padding: 40px 20px;
    color: #637381;
}
.empty-orders i {
    font-size: 42px;
    color: #c8d4df;
    margin-bottom: 12px;
}
@media (max-width: 1100px) {
    .order-summary-grid { grid-template-columns: repeat(2, 1fr); }
    .order-card-body { grid-template-columns: repeat(2, 1fr); }
    .order-filter-form { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 680px) {
    .order-page-head,
    .order-card-top { flex-direction: column; }
    .order-summary-grid,
    .order-card-body,
    .order-filter-form { grid-template-columns: 1fr; }
}
</style>

<div class="order-page-head">
    <div>
        <h1>Customer Orders</h1>
        <p>View customer purchases, filter by order status, and update active orders. Delivered and Cancelled orders are locked.</p>
    </div>
</div>

<?php adminAlerts($message, $error); ?>

<div class="order-summary-grid">
    <div class="order-summary-card"><span>All Orders</span><strong><?php echo number_format($allOrdersCount); ?></strong></div>
    <div class="order-summary-card"><span>Pending</span><strong><?php echo number_format($pendingCount); ?></strong></div>
    <div class="order-summary-card"><span>Processing</span><strong><?php echo number_format($processingCount); ?></strong></div>
    <div class="order-summary-card"><span>Delivered</span><strong><?php echo number_format($deliveredCount); ?></strong></div>
    <div class="order-summary-card"><span>Cancelled</span><strong><?php echo number_format($cancelledCount); ?></strong></div>
</div>

<div class="order-filter-card">
    <form method="GET" class="order-filter-form">
        <input type="search" name="q" value="<?php echo htmlspecialchars($orderSearch); ?>" placeholder="Search order no, customer, email, phone or address">
        <select name="status">
            <?php foreach ($allowedOrderFilters as $filter): ?>
                <option value="<?php echo htmlspecialchars($filter); ?>" <?php echo $orderStatusFilter === $filter ? 'selected' : ''; ?>>
                    <?php echo $filter === 'All' ? 'All Status' : htmlspecialchars($filter); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><i class="fa fa-filter"></i> Filter</button>
        <a href="admin_orders.php">Reset</a>
    </form>
</div>

<div class="order-card-list">
    <?php if ($filteredOrders && mysqli_num_rows($filteredOrders) > 0): ?>
        <?php while ($order = mysqli_fetch_assoc($filteredOrders)): ?>
            <div class="order-card">
                <div class="order-card-top">
                    <div>
                        <div class="order-number"><?php echo htmlspecialchars($order["order_number"]); ?></div>
                        <div class="order-date"><i class="fa fa-calendar"></i> <?php echo date("M d, Y h:i A", strtotime($order["created_at"])); ?></div>
                    </div>
                    <span class="status-badge <?php echo statusClass($order["status"]); ?>">
                        <?php echo htmlspecialchars($order["status"] ?: "Pending"); ?>
                    </span>
                </div>

                <div class="order-card-body">
                    <div class="order-info-block">
                        <h4>Customer</h4>
                        <p>
                            <strong><?php echo htmlspecialchars($order["customer_name"]); ?></strong><br>
                            <?php echo htmlspecialchars($order["customer_email"]); ?><br>
                            <?php echo htmlspecialchars($order["customer_phone"]); ?>
                        </p>
                    </div>

                    <div class="order-info-block">
                        <h4>Delivery Address</h4>
                        <p><?php echo htmlspecialchars($order["delivery_address"]); ?></p>
                    </div>

                    <div class="order-info-block">
                        <h4>Products Bought</h4>
                        <div><?php echo $order["items"] ?: "No items"; ?></div>
                    </div>

                    <div class="order-info-block">
                        <h4>Payment & Status</h4>
                        <p><?php echo htmlspecialchars($order["payment_method"]); ?></p>
                        <div class="order-total">Rs <?php echo number_format($order["grand_total"]); ?></div>

                        <?php if (in_array($order["status"], ["Delivered", "Cancelled"], true)): ?>
                            <div style="margin-top:10px;">
                                <span class="status-badge <?php echo statusClass($order["status"]); ?>">
                                    <?php echo htmlspecialchars($order["status"]); ?>
                                </span>
                                <div class="locked-note">Status locked</div>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="inline-status-form" style="margin-top:10px;">
                                <input type="hidden" name="action" value="update_order_status">
                                <?php csrfField(); ?>
                                <input type="hidden" name="order_id" value="<?php echo (int)$order["id"]; ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <?php foreach (["Pending", "Processing", "Delivered", "Cancelled"] as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo $order["status"] === $status ? "selected" : ""; ?>><?php echo $status; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php endif; ?>

                        <div class="locked-note">
                            Invoice:
                            <?php if ($order["status"] === "Delivered" && !empty($order["invoice_sent_at"])): ?>
                                Sent on <?php echo date("M d, Y h:i A", strtotime($order["invoice_sent_at"])); ?>
                            <?php elseif ($order["status"] === "Delivered"): ?>
                                Delivery confirmed
                            <?php else: ?>
                                Sent after delivery
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="order-card empty-orders">
            <i class="fa fa-receipt"></i>
            <h3>No orders found</h3>
            <p>Try changing the status filter or search keyword.</p>
        </div>
    <?php endif; ?>
</div>

<?php adminPageEnd(); ?>
