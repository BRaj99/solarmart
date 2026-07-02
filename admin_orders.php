<?php include "admin_common.php"; ?>
<?php adminPageStart('orders', 'Customer Orders'); ?>
<h1>Customer Orders</h1>
<p>Every successful checkout appears here.</p>
<?php adminAlerts($message, $error); ?>
<div class="card">
    <h3>Products Bought By Customers</h3>
    <div class="table-wrap">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Order No.</th><th>Customer</th><th>Contact</th><th>Products Bought</th>
                    <th>Total</th><th>Payment</th><th>Status</th><th>Invoice</th><th>Date</th>
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
                            <?php if ($order["status"] === "Delivered"): ?>
                                <strong>Delivered</strong><br>
                                <small>Status locked</small>
                            <?php else: ?>
                                <form method="POST" class="inline-status-form">
                                    <input type="hidden" name="action" value="update_order_status">
                                    <input type="hidden" name="order_id" value="<?php echo (int)$order["id"]; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <?php foreach (["Pending", "Processing", "Delivered", "Cancelled"] as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $order["status"] === $status ? "selected" : ""; ?>><?php echo $status; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($order["status"] === "Delivered" && !empty($order["invoice_sent_at"])): ?>
                                Sent<br><small><?php echo date("M d, Y h:i A", strtotime($order["invoice_sent_at"])); ?></small>
                            <?php elseif ($order["status"] === "Delivered"): ?>
                                Delivery confirmed
                            <?php else: ?>
                                After delivery
                            <?php endif; ?>
                        </td>
                        <td><?php echo date("M d, Y h:i A", strtotime($order["created_at"])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">No customer orders yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminPageEnd(); ?>
