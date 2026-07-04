<?php include "admin_common.php"; ?>
<?php adminPageStart('stock', 'Stock Management'); ?>

<style>
.stock-page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 22px;
}
.stock-page-head h1 {
    margin-bottom: 6px;
}
.stock-page-head p {
    color: #64748b;
    max-width: 720px;
}
.stock-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.stock-ui-grid {
    display: grid;
    grid-template-columns: minmax(310px, 420px) 1fr;
    gap: 22px;
    align-items: start;
}
.stock-card {
    border: 1px solid #e5e7eb;
    border-radius: 22px;
    background: #fff;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}
.stock-card-header {
    padding: 20px 22px;
    background: linear-gradient(135deg, #eefcf6, #ffffff);
    border-bottom: 1px solid #e5e7eb;
}
.stock-card-header .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #088178;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 8px;
}
.stock-card-header h3 {
    margin: 0 0 6px;
}
.stock-card-header p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}
.stock-form-body {
    padding: 22px;
}
.stock-form-body .field {
    margin-bottom: 16px;
}
.stock-form-body label,
.filter-label {
    display: block;
    margin-bottom: 7px;
    color: #334155;
    font-weight: 700;
    font-size: 13px;
}
.stock-form-body input,
.stock-form-body select,
.report-filter-bar input,
.report-filter-bar select {
    width: 100%;
    min-height: 46px;
    border: 1px solid #dbe3ef;
    border-radius: 12px;
    padding: 10px 13px;
    background: #fff;
    outline: none;
}
.stock-form-body input:focus,
.stock-form-body select:focus,
.report-filter-bar input:focus,
.report-filter-bar select:focus {
    border-color: #088178;
    box-shadow: 0 0 0 4px rgba(8, 129, 120, 0.12);
}
.nepali-date-fields {
    display: grid;
    grid-template-columns: 1fr 1.15fr .8fr;
    gap: 10px;
}
.stock-submit-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 18px;
}
.stock-submit-row .normal,
.stock-submit-row .outline-btn,
.stock-actions .normal,
.stock-actions .outline-btn {
    border-radius: 12px;
    text-decoration: none;
}
.stock-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(130px, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}
.stock-summary-card {
    border-radius: 18px;
    padding: 16px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
}
.stock-summary-card span {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    font-size: 13px;
    font-weight: 700;
}
.stock-summary-card h2 {
    margin: 8px 0 0;
    color: #0f172a;
}
.report-card {
    padding: 0;
}
.report-filter-bar {
    padding: 20px 22px;
    border-bottom: 1px solid #e5e7eb;
    background: #fbfdff;
}
.filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    align-items: end;
}
.report-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 14px;
}
.report-range-box {
    margin: 18px 22px 0;
    padding: 14px 16px;
    border-radius: 16px;
    background: #ecfeff;
    border: 1px solid #bae6fd;
}
.report-range-box p {
    margin: 4px 0;
}
.report-tools {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
    padding: 18px 22px 0;
}
.report-tools h3 {
    margin: 0;
}
.report-search {
    max-width: 320px;
    width: 100%;
    min-height: 42px;
    border: 1px solid #dbe3ef;
    border-radius: 12px;
    padding: 9px 12px;
}
.stock-table-wrap {
    padding: 18px 22px 22px;
}
.stock-report-table th {
    white-space: nowrap;
}
.stock-report-table td {
    vertical-align: middle;
}
.stock-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 6px 10px;
    font-weight: 800;
    font-size: 12px;
    background: #ecfdf5;
    color: #047857;
}
.stock-date-main {
    font-weight: 800;
    color: #0f172a;
}
.stock-date-sub {
    display: block;
    color: #64748b;
    font-size: 12px;
    margin-top: 3px;
}
.bill-pill {
    display: inline-flex;
    padding: 6px 10px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
}
.empty-stock-state {
    text-align: center;
    padding: 35px 15px;
    color: #64748b;
}
.empty-stock-state i {
    display: block;
    font-size: 38px;
    color: #94a3b8;
    margin-bottom: 10px;
}
@media (max-width: 1100px) {
    .stock-ui-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 760px) {
    .stock-page-head,
    .report-tools {
        flex-direction: column;
    }
    .stock-summary-grid,
    .filter-grid,
    .nepali-date-fields {
        grid-template-columns: 1fr;
    }
}

/* Match stock add/update and filter controls with the product add/update UI */
.stock-card .admin-form {
    margin: 0;
    background: #fbfffc;
    border: 1px solid var(--line, #d8e8dc);
    border-radius: 0 0 22px 22px;
    box-shadow: none;
}
.stock-form-body.admin-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 15px;
    padding: 18px;
}
.stock-form-body.admin-form .field {
    margin-bottom: 0;
}
.stock-form-body.admin-form .field label,
.report-filter-bar.admin-form .filter-label {
    color: var(--deep, #0f3324);
    font-weight: 900;
    font-size: 12px;
}
.stock-submit-row {
    grid-column: 1 / -1;
}
.report-filter-bar.admin-form {
    display: block;
    padding: 18px;
    border-left: 0;
    border-right: 0;
    border-radius: 0;
}
.report-filter-bar.admin-form .filter-grid {
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
}

</style>

<div class="stock-page-head">
    <div>
        <h1>Stock Management</h1>
        <p>Add new stock, manage bill-wise stock entries, and view stock reports by Nepali fiscal year or Nepali date range.</p>
    </div>
    <div class="stock-actions">
        <a class="outline-btn" href="admin_products.php"><i class="fa fa-box"></i> Products</a>
        <a class="normal" href="#stockForm"><i class="fa fa-plus"></i> Add Stock</a>
    </div>
</div>

<?php adminAlerts($message, $error); ?>

<div class="stock-summary-grid">
    <div class="stock-summary-card">
        <span><i class="fa fa-cubes"></i> Total Added</span>
        <h2><?php echo (int)$totalStockAdded; ?></h2>
    </div>
    <div class="stock-summary-card">
        <span><i class="fa fa-calendar-days"></i> Report Range</span>
        <h2 style="font-size:18px;"><?php echo htmlspecialchars($selectedDateRangeDisplay); ?></h2>
    </div>
    <div class="stock-summary-card">
        <span><i class="fa fa-filter"></i> Fiscal Year</span>
        <h2 style="font-size:18px;"><?php echo $fiscalYear ? 'FY ' . htmlspecialchars($fiscalYear) . ' BS' : 'All'; ?></h2>
    </div>
</div>

<div class="stock-ui-grid">
    <div class="stock-card" id="stockForm">
        <div class="stock-card-header">
            <div class="eyebrow">
                <i class="fa fa-warehouse"></i>
                <?php echo $editStock ? "Edit Entry" : "New Stock Entry"; ?>
            </div>
            <h3><?php echo $editStock ? "Update Stock Entry" : "Add Stock"; ?></h3>
            <p><?php echo $editStock ? "Update the selected bill-wise stock record." : "Add stock with product, quantity, Nepali date, and bill number."; ?></p>
        </div>

        <form class="admin-form stock-form-body" method="POST" action="admin_stock.php">
            <input type="hidden" name="action" value="<?php echo $editStock ? 'update_stock_log' : 'add_stock'; ?>">
            <?php csrfField(); ?>
            <?php if ($editStock): ?>
                <input type="hidden" name="log_id" value="<?php echo (int)$editStock['id']; ?>">
            <?php endif; ?>

            <div class="field">
                <label>Product</label>
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
            </div>

            <div class="field">
                <label>Stock Quantity</label>
                <input type="number" name="added_stock" min="1" placeholder="Enter quantity" required value="<?php echo htmlspecialchars($editStock['added_stock'] ?? ''); ?>">
            </div>

            <div class="field">
                <label>Nepali Stock Date</label>
                <?php nepaliDateDropdowns('stock', $editStock['stock_date'] ?? date('Y-m-d')); ?>
            </div>

            <div class="field">
                <label>Bill Number</label>
                <input type="text" name="note" placeholder="Enter bill number" required value="<?php echo htmlspecialchars($editStock['note'] ?? ''); ?>">
            </div>

            <div class="stock-submit-row">
                <button type="submit" class="normal">
                    <i class="fa <?php echo $editStock ? 'fa-save' : 'fa-plus'; ?>"></i>
                    <?php echo $editStock ? "Update Entry" : "Add Stock"; ?>
                </button>
                <?php if ($editStock): ?>
                    <a class="outline-btn" href="admin_stock.php"><i class="fa fa-xmark"></i> Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="stock-card report-card">
        <div class="stock-card-header">
            <div class="eyebrow"><i class="fa fa-chart-column"></i> Stock Report</div>
            <h3>Filter Stock Records</h3>
            <p>Use Nepali fiscal year or custom Nepali date range to view stock history.</p>
        </div>

        <form class="admin-form report-filter-bar" method="GET" action="admin_stock.php">
            <div class="filter-grid">
                <div>
                    <label class="filter-label">Nepali Fiscal Year</label>
                    <select name="fiscal_year">
                        <option value="">All Fiscal Years</option>
                        <?php foreach ($availableFiscalYears as $fy): ?>
                            <option value="<?php echo htmlspecialchars($fy); ?>" <?php echo $fiscalYear === $fy ? 'selected' : ''; ?>>
                                FY <?php echo htmlspecialchars($fy); ?> BS
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="filter-label">From Nepali Date</label>
                    <?php nepaliDateDropdowns('from', $filterFrom ?: $reportStartDate); ?>
                </div>

                <div>
                    <label class="filter-label">To Nepali Date</label>
                    <?php nepaliDateDropdowns('to', $filterTo ?: $reportEndDate); ?>
                </div>
            </div>

            <div class="report-buttons">
                <button type="submit" class="normal"><i class="fa fa-magnifying-glass-chart"></i> Show Report</button>
                <a class="outline-btn" href="admin_stock.php"><i class="fa fa-rotate-left"></i> Reset</a>
            </div>
        </form>

        <?php if ($fiscalYear || $filterFrom || $filterTo): ?>
            <div class="report-range-box">
                <?php if ($fiscalYear): ?>
                    <p><strong>Selected Nepali FY:</strong> FY <?php echo htmlspecialchars($fiscalYear); ?> BS</p>
                <?php endif; ?>
                <p><strong>Selected Date Range:</strong> <?php echo htmlspecialchars($selectedDateRangeDisplay); ?></p>
            </div>
        <?php endif; ?>

        <div class="report-tools">
            <h3>Stock Entries</h3>
            <input type="search" class="report-search" id="stockReportSearch" placeholder="Search product, bill no., FY...">
        </div>

        <div class="stock-table-wrap">
            <div class="table-wrap">
                <table class="cart-table stock-report-table" id="stockReportTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Nepali FY</th>
                            <th>Product</th>
                            <th>Added Stock</th>
                            <th>Current Stock</th>
                            <th>Bill No.</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($stockReport && mysqli_num_rows($stockReport) > 0): ?>
                        <?php while ($log = mysqli_fetch_assoc($stockReport)): ?>
                            <tr>
                                <td>
                                    <span class="stock-date-main"><?php echo htmlspecialchars(nepaliDateDisplay($log['stock_date'])); ?></span>
                                    <span class="stock-date-sub"><?php echo htmlspecialchars($log['stock_date']); ?></span>
                                </td>
                                <td>FY <?php echo htmlspecialchars(nepaliFiscalYearLabel($log['stock_date'])); ?> BS</td>
                                <td><strong><?php echo htmlspecialchars($log['product_name']); ?></strong></td>
                                <td><span class="stock-badge">+<?php echo (int)$log['added_stock']; ?></span></td>
                                <td><?php echo (int)$log['current_stock']; ?></td>
                                <td><span class="bill-pill"><?php echo htmlspecialchars($log['note']); ?></span></td>
                                <td>
                                    <a class="small-btn edit-btn" href="admin_stock.php?edit_stock=<?php echo (int)$log['id']; ?>#stockForm">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-stock-state">
                                    <i class="fa fa-box-open"></i>
                                    <strong>No stock records found.</strong>
                                    <p>Try changing the fiscal year/date filter or add a new stock entry.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const search = document.getElementById("stockReportSearch");
    const table = document.getElementById("stockReportTable");

    if (search && table) {
        search.addEventListener("input", function () {
            const term = search.value.toLowerCase();
            table.querySelectorAll("tbody tr").forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(term) ? "" : "none";
            });
        });
    }
});
</script>

<?php adminPageEnd(); ?>
