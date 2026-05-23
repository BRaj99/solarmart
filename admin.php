<?php include "admin_common.php"; ?>
<?php adminPageStart('dashboard', 'Admin Dashboard'); ?>

<h1>Dashboard</h1>
<p>Products, customers, orders, stock alerts, and sales graphs are loaded from your MySQL database.</p>
<?php adminAlerts($message, $error); ?>

<div class="admin-cards">
    <div class="card metric-card"><p>Total Products</p><h2><?php echo $totalProducts; ?></h2></div>
    <div class="card metric-card"><p>Customer Orders</p><h2><?php echo $totalOrders; ?></h2></div>
    <div class="card metric-card"><p>Registered Customers</p><h2><?php echo $totalCustomers; ?></h2></div>
    <div class="card metric-card"><p>Total Sales</p><h2>Rs <?php echo number_format($totalSales); ?></h2></div>
</div>

<div class="admin-cards">
    <div class="card metric-card">
        <p>Low Stock Items</p>
        <h2><?php echo $lowStock; ?></h2>
        <a class="outline-btn" href="admin_stock.php">View Stock</a>
    </div>
    <div class="card metric-card">
        <p>Total Stock Value</p>
        <h2>Rs <?php echo number_format($totalStockValue); ?></h2>
    </div>
</div>

<div class="grid-2 admin-chart-grid">
    <div class="card">
        <h3>Monthly Sales</h3>
        <p>Sales amount from recent orders.</p>
        <canvas id="salesChart" height="130"></canvas>
    </div>
    <div class="card">
        <h3>Order Status</h3>
        <p>Current order status summary.</p>
        <canvas id="orderStatusChart" height="130"></canvas>
    </div>
</div>

<div class="card" style="margin-top:22px;">
    <h3>Lowest Stock Products</h3>
    <p>Products with the lowest stock are shown first.</p>
    <canvas id="stockChart" height="90"></canvas>
</div>

<div class="card" style="margin-top:22px;">
    <h3>Admin Sections</h3>
    <p>Use these quick links to manage each function on its own page.</p>
    <div class="admin-cards">
        <a class="card metric-card" href="admin_orders.php"><p>Open</p><h2>Orders</h2></a>
        <a class="card metric-card" href="admin_stock.php"><p>Open</p><h2>Stock</h2></a>
        <a class="card metric-card" href="admin_products.php"><p>Open</p><h2>Products</h2></a>
        <a class="card metric-card" href="admin_customers.php"><p>Open</p><h2>Customers</h2></a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthlySalesLabels = <?php echo json_encode($monthlySalesLabels); ?>;
const monthlySalesData = <?php echo json_encode($monthlySalesData); ?>;
const orderStatusLabels = <?php echo json_encode($orderStatusLabels); ?>;
const orderStatusData = <?php echo json_encode($orderStatusData); ?>;
const stockLabels = <?php echo json_encode($stockLabels); ?>;
const stockData = <?php echo json_encode($stockData); ?>;

function makeChart(id, config) {
    const canvas = document.getElementById(id);
    if (canvas) new Chart(canvas, config);
}

makeChart('salesChart', {
    type: 'line',
    data: {
        labels: monthlySalesLabels.length ? monthlySalesLabels : ['No data'],
        datasets: [{
            label: 'Sales Rs',
            data: monthlySalesData.length ? monthlySalesData : [0],
            tension: 0.35,
            fill: true
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

makeChart('orderStatusChart', {
    type: 'doughnut',
    data: {
        labels: orderStatusLabels.length ? orderStatusLabels : ['No orders'],
        datasets: [{ data: orderStatusData.length ? orderStatusData : [1] }]
    },
    options: { responsive: true }
});

makeChart('stockChart', {
    type: 'bar',
    data: {
        labels: stockLabels.length ? stockLabels : ['No products'],
        datasets: [{ label: 'Current Stock', data: stockData.length ? stockData : [0] }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php adminPageEnd(); ?>
