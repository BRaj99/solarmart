<?php
session_start();
include "db.php";

$message = clean($_GET["msg"] ?? "");
$error = clean($_GET["err"] ?? "");

function clean($value) {
    return trim($value ?? "");
}


// Creates the stock log table automatically if it is missing.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS stock_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    added_stock INT NOT NULL,
    stock_date DATE NOT NULL,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (product_id),
    INDEX (stock_date),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function nepaliFiscalYearLabel($adDate) {
    $time = strtotime($adDate);
    if (!$time) return "";

    $adYear = (int)date("Y", $time);
    $monthDay = date("m-d", $time);
    $startBsYear = ($monthDay >= "07-16") ? $adYear + 57 : $adYear + 56;
    return $startBsYear . "/" . substr((string)($startBsYear + 1), -2);
}

function nepaliDateDisplay($adDate) {
    $time = strtotime($adDate);
    if (!$time) return "";

    $adYear = (int)date("Y", $time);
    $dateOnly = date("Y-m-d", $time);

    $monthStarts = [
        ["name" => "Baishakh", "date" => $adYear . "-04-14"],
        ["name" => "Jestha", "date" => $adYear . "-05-15"],
        ["name" => "Ashadh", "date" => $adYear . "-06-15"],
        ["name" => "Shrawan", "date" => $adYear . "-07-17"],
        ["name" => "Bhadra", "date" => $adYear . "-08-17"],
        ["name" => "Ashwin", "date" => $adYear . "-09-17"],
        ["name" => "Kartik", "date" => $adYear . "-10-18"],
        ["name" => "Mangsir", "date" => $adYear . "-11-17"],
        ["name" => "Poush", "date" => $adYear . "-12-16"],
        ["name" => "Magh", "date" => ($adYear + 1) . "-01-15"],
        ["name" => "Falgun", "date" => ($adYear + 1) . "-02-13"],
        ["name" => "Chaitra", "date" => ($adYear + 1) . "-03-14"]
    ];

    if ($dateOnly < $adYear . "-04-14") {
        $adYear--;
        $monthStarts = [
            ["name" => "Baishakh", "date" => $adYear . "-04-14"],
            ["name" => "Jestha", "date" => $adYear . "-05-15"],
            ["name" => "Ashadh", "date" => $adYear . "-06-15"],
            ["name" => "Shrawan", "date" => $adYear . "-07-17"],
            ["name" => "Bhadra", "date" => $adYear . "-08-17"],
            ["name" => "Ashwin", "date" => $adYear . "-09-17"],
            ["name" => "Kartik", "date" => $adYear . "-10-18"],
            ["name" => "Mangsir", "date" => $adYear . "-11-17"],
            ["name" => "Poush", "date" => $adYear . "-12-16"],
            ["name" => "Magh", "date" => ($adYear + 1) . "-01-15"],
            ["name" => "Falgun", "date" => ($adYear + 1) . "-02-13"],
            ["name" => "Chaitra", "date" => ($adYear + 1) . "-03-14"]
        ];
    }

    $selectedMonth = $monthStarts[0];
    foreach ($monthStarts as $monthStart) {
        if ($dateOnly >= $monthStart["date"]) {
            $selectedMonth = $monthStart;
        }
    }

    $start = new DateTime($selectedMonth["date"]);
    $current = new DateTime($dateOnly);
    $day = $start->diff($current)->days + 1;
    $bsYear = $adYear + 57;

    return $selectedMonth["name"] . " " . $day . ", " . $bsYear . " BS";
}

function nepaliDateRangeDisplay($fromDate, $toDate) {
    if ($fromDate === "" && $toDate === "") return "All dates";
    if ($fromDate !== "" && $toDate !== "") return nepaliDateDisplay($fromDate) . " to " . nepaliDateDisplay($toDate);
    if ($fromDate !== "") return "From " . nepaliDateDisplay($fromDate);
    return "Up to " . nepaliDateDisplay($toDate);
}

function nepaliFiscalYearRangeAd($fyLabel) {
    $parts = explode("/", $fyLabel);
    if (count($parts) < 1 || !is_numeric($parts[0])) return ["", ""];

    $startBsYear = (int)$parts[0];
    $startAdYear = $startBsYear - 57;
    return [$startAdYear . "-07-16", ($startAdYear + 1) . "-07-15"];
}

function buildNepaliFiscalYears($startDate, $endDate = null) {
    if (!$startDate) return [];

    $endDate = $endDate ?: date("Y-m-d");
    $startLabel = nepaliFiscalYearLabel($startDate);
    $endLabel = nepaliFiscalYearLabel($endDate);

    $startYear = (int)explode("/", $startLabel)[0];
    $endYear = (int)explode("/", $endLabel)[0];

    $years = [];
    for ($year = $endYear; $year >= $startYear; $year--) {
        $years[] = $year . "/" . substr((string)($year + 1), -2);
    }
    return $years;
}

function nepaliBsToAd($bsYear, $bsMonth, $bsDay) {
    $bsYear = (int)$bsYear;
    $bsMonth = (int)$bsMonth;
    $bsDay = (int)$bsDay;

    if ($bsYear <= 0 || $bsMonth < 1 || $bsMonth > 12 || $bsDay < 1 || $bsDay > 32) {
        return "";
    }

    $adYear = $bsYear - 57;
    $monthStarts = [
        1 => $adYear . "-04-14",
        2 => $adYear . "-05-15",
        3 => $adYear . "-06-15",
        4 => $adYear . "-07-17",
        5 => $adYear . "-08-17",
        6 => $adYear . "-09-17",
        7 => $adYear . "-10-18",
        8 => $adYear . "-11-17",
        9 => $adYear . "-12-16",
        10 => ($adYear + 1) . "-01-15",
        11 => ($adYear + 1) . "-02-13",
        12 => ($adYear + 1) . "-03-14"
    ];

    $date = new DateTime($monthStarts[$bsMonth]);
    $date->modify("+" . ($bsDay - 1) . " days");
    return $date->format("Y-m-d");
}

function nepaliDatePartsFromAd($adDate) {
    $display = nepaliDateDisplay($adDate ?: date("Y-m-d"));

    if (!preg_match('/^([A-Za-z]+)\s+(\d+),\s+(\d+)\s+BS$/', $display, $matches)) {
        return ["year" => (int)date("Y") + 57, "month" => 1, "day" => 1];
    }

    $monthMap = [
        "Baishakh" => 1, "Jestha" => 2, "Ashadh" => 3, "Shrawan" => 4,
        "Bhadra" => 5, "Ashwin" => 6, "Kartik" => 7, "Mangsir" => 8,
        "Poush" => 9, "Magh" => 10, "Falgun" => 11, "Chaitra" => 12
    ];

    return [
        "year" => (int)$matches[3],
        "month" => $monthMap[$matches[1]] ?? 1,
        "day" => (int)$matches[2]
    ];
}

function nepaliDateDropdowns($prefix, $selectedAdDate = "") {
    $selected = nepaliDatePartsFromAd($selectedAdDate ?: date("Y-m-d"));
    $months = [
        1 => "Baishakh", 2 => "Jestha", 3 => "Ashadh", 4 => "Shrawan",
        5 => "Bhadra", 6 => "Ashwin", 7 => "Kartik", 8 => "Mangsir",
        9 => "Poush", 10 => "Magh", 11 => "Falgun", 12 => "Chaitra"
    ];

    echo '<div class="nepali-date-fields">';
    echo '<select name="'.$prefix.'_bs_year" required>';
    for ($year = $selected["year"] + 2; $year >= $selected["year"] - 10; $year--) {
        $sel = ($year == $selected["year"]) ? "selected" : "";
        echo '<option value="'.$year.'" '.$sel.'>'.$year.' BS</option>';
    }
    echo '</select>';

    echo '<select name="'.$prefix.'_bs_month" required>';
    foreach ($months as $num => $name) {
        $sel = ($num == $selected["month"]) ? "selected" : "";
        echo '<option value="'.$num.'" '.$sel.'>'.$name.'</option>';
    }
    echo '</select>';

    echo '<select name="'.$prefix.'_bs_day" required>';
    for ($day = 1; $day <= 32; $day++) {
        $sel = ($day == $selected["day"]) ? "selected" : "";
        echo '<option value="'.$day.'" '.$sel.'>'.$day.'</option>';
    }
    echo '</select>';
    echo '</div>';
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


    if ($action === "add_stock") {
        $productId = (int)($_POST['product_id'] ?? 0);
        $addedStock = (int)($_POST['added_stock'] ?? 0);
        $stockDate = nepaliBsToAd($_POST['stock_bs_year'] ?? 0, $_POST['stock_bs_month'] ?? 0, $_POST['stock_bs_day'] ?? 0);
        $note = clean($_POST['note'] ?? '');

        if ($productId <= 0 || $addedStock <= 0 || $stockDate === '') {
            $error = "Please select product, stock quantity, and Nepali stock date.";
        } elseif ($note === '') {
            $error = "Bill number is required.";
        } elseif ($stockDate > date("Y-m-d")) {
            $error = "Future date is not allowed. Please select today or a past Nepali date.";
        } else {
            mysqli_begin_transaction($conn);
            try {
                $stmt = mysqli_prepare($conn, "UPDATE products SET stock = stock + ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "ii", $addedStock, $productId);
                mysqli_stmt_execute($stmt);

                $stmt = mysqli_prepare($conn, "INSERT INTO stock_logs (product_id, added_stock, stock_date, note) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iiss", $productId, $addedStock, $stockDate, $note);
                mysqli_stmt_execute($stmt);

                mysqli_commit($conn);
                $message = "Stock is added successfully.";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Could not add stock.";
            }
        }
    }

    if ($action === "update_stock_log") {
        $logId = (int)($_POST['log_id'] ?? 0);
        $newProductId = (int)($_POST['product_id'] ?? 0);
        $newAddedStock = (int)($_POST['added_stock'] ?? 0);
        $newStockDate = nepaliBsToAd($_POST['stock_bs_year'] ?? 0, $_POST['stock_bs_month'] ?? 0, $_POST['stock_bs_day'] ?? 0);
        $newNote = clean($_POST['note'] ?? '');

        if ($logId <= 0 || $newProductId <= 0 || $newAddedStock <= 0 || $newStockDate === '') {
            $error = "Please select product, stock quantity, and Nepali stock date.";
        } elseif ($newNote === '') {
            $error = "Bill number is required.";
        } elseif ($newStockDate > date("Y-m-d")) {
            $error = "Future date is not allowed. Please select today or a past Nepali date.";
        } else {
            mysqli_begin_transaction($conn);
            try {
                $stmt = mysqli_prepare($conn, "SELECT product_id, added_stock FROM stock_logs WHERE id=? FOR UPDATE");
                mysqli_stmt_bind_param($stmt, "i", $logId);
                mysqli_stmt_execute($stmt);
                $oldLog = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                if (!$oldLog) {
                    throw new Exception("Stock record not found.");
                }

                $oldProductId = (int)$oldLog['product_id'];
                $oldAddedStock = (int)$oldLog['added_stock'];

                if ($oldProductId === $newProductId) {
                    $difference = $newAddedStock - $oldAddedStock;
                    $stmt = mysqli_prepare($conn, "UPDATE products SET stock = stock + ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "ii", $difference, $newProductId);
                    mysqli_stmt_execute($stmt);
                } else {
                    $removeOld = -$oldAddedStock;
                    $stmt = mysqli_prepare($conn, "UPDATE products SET stock = stock + ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "ii", $removeOld, $oldProductId);
                    mysqli_stmt_execute($stmt);

                    $stmt = mysqli_prepare($conn, "UPDATE products SET stock = stock + ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "ii", $newAddedStock, $newProductId);
                    mysqli_stmt_execute($stmt);
                }

                $stmt = mysqli_prepare($conn, "UPDATE stock_logs SET product_id=?, added_stock=?, stock_date=?, note=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "iissi", $newProductId, $newAddedStock, $newStockDate, $newNote, $logId);
                mysqli_stmt_execute($stmt);

                mysqli_commit($conn);
                $message = "Stock is updated successfully.";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Could not update stock entry.";
            }
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $actionForRedirect = clean($_POST['action'] ?? '');
    $section = 'dashboard';

    if (in_array($actionForRedirect, ['add_stock', 'update_stock_log'])) {
        $section = 'stock';
    } elseif (in_array($actionForRedirect, ['add', 'update', 'delete'])) {
        $section = 'products';
    } elseif ($actionForRedirect === 'update_order_status') {
        $section = 'orders';
    }

    if ($message !== "") {
        header("Location: admin.php?msg=" . urlencode($message) . "#" . $section);
        exit;
    }

    if ($error !== "") {
        header("Location: admin.php?err=" . urlencode($error) . "#" . $section);
        exit;
    }

    header("Location: admin.php#" . $section);
    exit;
}

$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $editProduct = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}


$editStock = null;
if (isset($_GET['edit_stock'])) {
    $editStockId = (int) $_GET['edit_stock'];
    $stmt = mysqli_prepare($conn, "
        SELECT sl.*, p.name AS product_name
        FROM stock_logs sl
        JOIN products p ON sl.product_id = p.id
        WHERE sl.id=?
    ");
    mysqli_stmt_bind_param($stmt, "i", $editStockId);
    mysqli_stmt_execute($stmt);
    $editStock = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products"))['total'] ?? 0;
$lowStock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE stock <= 5"))['total'] ?? 0;
$totalCustomers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='customer'"))['total'] ?? 0;
$totalStockValue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(price * stock),0) AS total FROM products"))['total'] ?? 0;
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders"))['total'] ?? 0;
$totalSales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(grand_total),0) AS total FROM orders WHERE status != 'Cancelled'"))['total'] ?? 0;


$filterFrom = clean($_GET['from'] ?? '');
$filterTo = clean($_GET['to'] ?? '');

if (isset($_GET['from_bs_year'], $_GET['from_bs_month'], $_GET['from_bs_day'])) {
    $filterFrom = nepaliBsToAd($_GET['from_bs_year'], $_GET['from_bs_month'], $_GET['from_bs_day']);
}
if (isset($_GET['to_bs_year'], $_GET['to_bs_month'], $_GET['to_bs_day'])) {
    $filterTo = nepaliBsToAd($_GET['to_bs_year'], $_GET['to_bs_month'], $_GET['to_bs_day']);
}

$fiscalYear = clean($_GET['fiscal_year'] ?? '');
if ($fiscalYear !== '') {
    [$filterFrom, $filterTo] = nepaliFiscalYearRangeAd($fiscalYear);
}

$minProductDateRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT DATE(MIN(created_at)) AS min_date, DATE(MAX(created_at)) AS max_date FROM products"));
$minStockDateRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MIN(stock_date) AS min_date, MAX(stock_date) AS max_date FROM stock_logs"));
$possibleStartDates = array_filter([$minProductDateRow['min_date'] ?? '', $minStockDateRow['min_date'] ?? '']);
$possibleEndDates = array_filter([$minProductDateRow['max_date'] ?? '', $minStockDateRow['max_date'] ?? '', date('Y-m-d')]);
$reportStartDate = !empty($possibleStartDates) ? min($possibleStartDates) : date('Y-m-d');
$reportEndDate = !empty($possibleEndDates) ? max($possibleEndDates) : date('Y-m-d');
$availableFiscalYears = buildNepaliFiscalYears($reportStartDate, $reportEndDate);

$where = "WHERE 1=1";
$params = [];
$types = "";

if ($filterFrom !== '') {
    $where .= " AND sl.stock_date >= ?";
    $params[] = $filterFrom;
    $types .= "s";
}
if ($filterTo !== '') {
    $where .= " AND sl.stock_date <= ?";
    $params[] = $filterTo;
    $types .= "s";
}

$stockReportSql = "
    SELECT sl.*, p.name AS product_name, p.stock AS current_stock
    FROM stock_logs sl
    JOIN products p ON sl.product_id = p.id
    $where
    ORDER BY sl.stock_date DESC, sl.id DESC
";
$stmt = mysqli_prepare($conn, $stockReportSql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$stockReport = mysqli_stmt_get_result($stmt);

$stockSummarySql = "SELECT COALESCE(SUM(sl.added_stock),0) AS total_added FROM stock_logs sl $where";
$stmt = mysqli_prepare($conn, $stockSummarySql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$totalStockAdded = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total_added'] ?? 0;
$selectedDateRangeDisplay = nepaliDateRangeDisplay($filterFrom, $filterTo);

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

<style>
.success-message,
.error-message {
    margin: 18px 0;
    padding: 14px 18px;
    border-radius: 14px;
    font-weight: 800;
    box-shadow: 0 10px 25px rgba(15, 51, 36, .08);
}
.success-message {
    background: #e8fff1;
    color: #0f7a42;
    border: 1px solid #8ee0b1;
}
.error-message {
    background: #ffe5e5;
    color: #d8000c;
    border: 1px solid #ffb3b3;
}
</style>
</head>
<body class="admin-page">
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

<main class="admin-layout" id="adminLayout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-head">
            <div>
                <span class="admin-kicker">SolarMart</span>
                <h3>Admin Panel</h3>
            </div>
            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="fa fa-bars"></i>
            </button>
        </div>

        <nav class="admin-nav">
            <a class="active" href="#dashboard"><i class="fa fa-chart-line"></i> <span>Dashboard</span></a>
            <a href="#orders"><i class="fa fa-receipt"></i> <span>Customer Orders</span></a>
            <a href="#stock"><i class="fa fa-warehouse"></i> <span>Stock</span></a>
            <a href="#products"><i class="fa fa-box"></i> <span>Products</span></a>
            <a href="#customers"><i class="fa fa-users"></i> <span>Customers</span></a>
        </nav>
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


        <div id="stock" class="card">
            <h3><?php echo $editStock ? "Edit Stock Entry" : "Add Stock"; ?></h3>
            <p>Add product stock using Nepali date. If there is a mistake, click Edit in the report below.</p>
            <form class="admin-form" method="POST" action="admin.php#stock">
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
                    <a class="outline-btn" href="admin.php#stock">Cancel Edit</a>
                <?php endif; ?>
            </form>

            <br>
            <h3>Stock Report</h3>
            <form class="admin-form" method="GET" action="admin.php#stock">
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
                <a class="outline-btn" href="admin.php#stock">Reset</a>
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
                                <td><a class="small-btn edit-btn" href="admin.php?edit_stock=<?php echo (int)$log['id']; ?>#stock">Edit</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No stock records found for this filter.</td></tr>
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const layout = document.getElementById("adminLayout");
    const sidebar = document.getElementById("adminSidebar");
    const toggle = document.getElementById("sidebarToggle");
    const links = document.querySelectorAll(".admin-nav a");

    if (toggle && layout && sidebar) {
        toggle.addEventListener("click", function () {
            if (window.innerWidth <= 1000) {
                sidebar.classList.toggle("mobile-open");
            } else {
                layout.classList.toggle("sidebar-collapsed");
            }
        });
    }

    links.forEach(function (link) {
        link.addEventListener("click", function () {
            links.forEach(function (item) { item.classList.remove("active"); });
            link.classList.add("active");
            if (window.innerWidth <= 1000 && sidebar) {
                sidebar.classList.remove("mobile-open");
            }
        });
    });
});
</script>

<script src="script.js"></script>
</body>
</html>
