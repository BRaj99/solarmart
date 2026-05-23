<?php include "admin_common.php"; ?>
<?php adminPageStart('customers', 'Customers'); ?>

<h1>Customers</h1>
<p>Registered customer details are loaded from the users table.</p>
<?php adminAlerts($message, $error); ?>

<div class="admin-cards">
    <div class="card metric-card">
        <p>Total Registered Customers</p>
        <h2><?php echo $totalCustomers; ?></h2>
    </div>
</div>

<div class="card">
    <h3>Registered Customer Details</h3>
    <p>View customer profile details, order count, and total spending.</p>

    <div class="table-wrap">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Location</th>
                    <th>Address</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Registered Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($customers && mysqli_num_rows($customers) > 0): ?>
                    <?php while ($customer = mysqli_fetch_assoc($customers)): ?>
                        <tr>
                            <td><?php echo (int)$customer['id']; ?></td>
                            <td><?php echo htmlspecialchars($customer['fullname'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['age'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['gender'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['location'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['address'] ?? ''); ?></td>
                            <td><?php echo (int)($customer['order_count'] ?? 0); ?></td>
                            <td>Rs <?php echo number_format((float)($customer['total_spent'] ?? 0)); ?></td>
                            <td>
                                <?php
                                    if (!empty($customer['created_at'])) echo date('M d, Y', strtotime($customer['created_at']));
                                    else echo 'Not available';
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">No registered customers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php adminPageEnd(); ?>
