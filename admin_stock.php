<?php include "admin_common.php"; ?>
<?php adminPageStart('stock', 'Stock Management'); ?>
<h1>Stock Management</h1>
<p>Add stock, update stock entries, and view Nepali fiscal-year stock reports.</p>
<?php adminAlerts($message, $error); ?>

<div class="card">
    <h3><?php echo $editStock ? "Edit Stock Entry" : "Add Stock"; ?></h3>
    <p>Add product stock using Nepali date. If there is a mistake, click Edit in the report below.</p>
    <form class="admin-form" method="POST" action="admin_stock.php">
        <input type="hidden" name="action" value="<?php echo $editStock ? 'update_stock_log' : 'add_stock'; ?>">
        <?php if ($editStock): ?>
            <input type="hidden" name="log_id" value="<?php echo (int)$editStock['id']; ?>">
        <?php endif; ?>

        <select name="product_id" required>
            <option value="">Select Product</option>
            <?php
            $stockProductsForForm = mysqli_query($conn, "SELECT id, name, stock FROM products ORDER BY name ASC");
            if ($stockProductsForForm && mysqli_num_rows($stockProductsForForm) > 0):
                while ($sp = mysqli_fetch_assoc($stockProductsForForm)):
                    $selectedStockProduct = ($editStock && (int)$editStock['product_id'] === (int)$sp['id']) ? 'selected' : '';
            ?>
                    <option value="<?php echo (int)$sp['id']; ?>" <?php echo $selectedStockProduct; ?>>
                        <?php echo htmlspecialchars($sp['name']); ?> — Current Stock: <?php echo (int)$sp['stock']; ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>

        <input type="number" name="added_stock" min="1" placeholder="Stock Quantity" required value="<?php echo htmlspecialchars($editStock['added_stock'] ?? ''); ?>">

        <label>Nepali Stock Date</label>
        <?php nepaliDateDropdowns('stock', $editStock['stock_date'] ?? date('Y-m-d')); ?>

        <input type="text" name="note" placeholder="Bill Number *" required value="<?php echo htmlspecialchars($editStock['note'] ?? ''); ?>">

        <button type="submit" class="normal"><?php echo $editStock ? "Update Stock Entry" : "Add Stock"; ?></button>
        <?php if ($editStock): ?>
            <a class="outline-btn" href="admin_stock.php">Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>

<br>
<div class="card">
    <h3>Stock Report</h3>
    <form class="admin-form" method="GET" action="admin_stock.php">
        <select name="fiscal_year">
            <option value="">All Nepali Fiscal Years</option>
            <?php foreach ($availableFiscalYears as $fy): ?>
                <option value="<?php echo htmlspecialchars($fy); ?>" <?php echo $fiscalYear === $fy ? 'selected' : ''; ?>>
                    FY <?php echo htmlspecialchars($fy); ?> BS
                </option>
            <?php endforeach; ?>
        </select>

        <label>From Nepali Date</label>
        <?php nepaliDateDropdowns('from', $filterFrom ?: $reportStartDate); ?>

        <label>To Nepali Date</label>
        <?php nepaliDateDropdowns('to', $filterTo ?: $reportEndDate); ?>

        <button type="submit" class="normal">Report</button>
        <a class="outline-btn" href="admin_stock.php">Reset</a>
    </form>

    <?php if ($fiscalYear || $filterFrom || $filterTo): ?>
        <div class="report-range-box">
            <?php if ($fiscalYear): ?>
                <p><strong>Selected Nepali FY:</strong> FY <?php echo htmlspecialchars($fiscalYear); ?> BS</p>
            <?php endif; ?>
            <p><strong>Selected Date Range:</strong> <?php echo htmlspecialchars($selectedDateRangeDisplay); ?></p>
        </div>
    <?php endif; ?>

    <h2>Total Stock Added: <?php echo (int)$totalStockAdded; ?></h2>
    <div class="table-wrap">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Date</th><th>Nepali FY</th><th>Product</th><th>Added Stock</th>
                    <th>Current Stock</th><th>Bill No.</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($stockReport && mysqli_num_rows($stockReport) > 0): ?>
                <?php while ($log = mysqli_fetch_assoc($stockReport)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(nepaliDateDisplay($log['stock_date'])); ?></td>
                        <td>FY <?php echo htmlspecialchars(nepaliFiscalYearLabel($log['stock_date'])); ?> BS</td>
                        <td><?php echo htmlspecialchars($log['product_name']); ?></td>
                        <td><?php echo (int)$log['added_stock']; ?></td>
                        <td><?php echo (int)$log['current_stock']; ?></td>
                        <td><?php echo htmlspecialchars($log['note']); ?></td>
                        <td><a class="small-btn edit-btn" href="admin_stock.php?edit_stock=<?php echo (int)$log['id']; ?>">Edit</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No stock records found for this filter.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminPageEnd(); ?>
