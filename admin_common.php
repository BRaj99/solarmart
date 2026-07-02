<?php
session_start();
include "db.php";
require_once "mail_helper.php";

$message = clean($_GET["msg"] ?? "");
$error = clean($_GET["err"] ?? "");

function clean($value) {
    return trim($value ?? "");
}


// Creates the stock log table automatically if it is missing.

$columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'invoice_sent_at'");
if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE orders ADD COLUMN invoice_sent_at TIMESTAMP NULL DEFAULT NULL");
}

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


function sendDeliveredInvoiceForOrder($conn, $orderId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$order) {
        throw new Exception("Order not found.");
    }

    $stmt = mysqli_prepare($conn, "SELECT product_name, price, quantity, line_total FROM order_items WHERE order_id=? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $itemsResult = mysqli_stmt_get_result($stmt);
    $items = [];
    while ($item = mysqli_fetch_assoc($itemsResult)) {
        $items[] = $item;
    }

    if (count($items) === 0) {
        throw new Exception("No order items found for invoice.");
    }

    $invoiceHtml = buildDeliveredInvoiceHtml($order, $items);
    sendInvoicePdfEmail($order['customer_email'], $order['customer_name'], $order['order_number'], $invoiceHtml);

    $stmt = mysqli_prepare($conn, "UPDATE orders SET invoice_sent_at = NOW() WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
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
            $stmt = mysqli_prepare($conn, "SELECT status, invoice_sent_at FROM orders WHERE id=? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "i", $orderId);
            mysqli_stmt_execute($stmt);
            $currentOrder = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if (!$currentOrder) {
                $error = "Order not found.";
            } elseif ($currentOrder["status"] === "Delivered") {
                $error = "Delivered orders cannot be changed.";
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE orders SET status=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "si", $status, $orderId);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "Order status updated.";

                    if ($status === "Delivered" && empty($currentOrder["invoice_sent_at"])) {
                        try {
                            sendDeliveredInvoiceForOrder($conn, $orderId);
                            $message = "Order marked as Delivered and invoice sent to customer.";
                        } catch (Exception $mailError) {
                            $message = "Order marked as Delivered, but invoice email was not sent. Check mail_config.php. " . $mailError->getMessage();
                        }
                    }
                } else {
                    $error = "Could not update order status.";
                }
            }
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


function tableExists($conn, $tableName) {
    $tableName = mysqli_real_escape_string($conn, $tableName);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
    return $result && mysqli_num_rows($result) > 0;
}

function columnExists($conn, $tableName, $columnName) {
    $tableName = mysqli_real_escape_string($conn, $tableName);
    $columnName = mysqli_real_escape_string($conn, $columnName);
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
    return $result && mysqli_num_rows($result) > 0;
}

function singleValue($conn, $sql, $default = 0) {
    $result = mysqli_query($conn, $sql);
    if (!$result) return $default;
    $row = mysqli_fetch_assoc($result);
    if (!$row) return $default;
    $values = array_values($row);
    return $values[0] ?? $default;
}

$products = tableExists($conn, "products") ? mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC") : false;
$totalProducts = tableExists($conn, "products") ? singleValue($conn, "SELECT COUNT(*) AS total FROM products") : 0;
$lowStock = tableExists($conn, "products") ? singleValue($conn, "SELECT COUNT(*) AS total FROM products WHERE stock <= 5") : 0;
$totalStockValue = tableExists($conn, "products") ? singleValue($conn, "SELECT COALESCE(SUM(price * stock),0) AS total FROM products") : 0;

$customerWhere = "1=1";
if (tableExists($conn, "users") && columnExists($conn, "users", "role")) {
    $customerWhere = "role='customer'";
}
$totalCustomers = tableExists($conn, "users") ? singleValue($conn, "SELECT COUNT(*) AS total FROM users WHERE $customerWhere") : 0;

$totalOrders = tableExists($conn, "orders") ? singleValue($conn, "SELECT COUNT(*) AS total FROM orders") : 0;
$totalSales = tableExists($conn, "orders") ? singleValue($conn, "SELECT COALESCE(SUM(grand_total),0) AS total FROM orders WHERE status != 'Cancelled'") : 0;

$customers = false;
if (tableExists($conn, "users")) {
    $customerOrderJoin = tableExists($conn, "orders") ? "LEFT JOIN orders o ON u.id = o.user_id" : "";
    $customerOrderFields = tableExists($conn, "orders") ? "COUNT(o.id) AS order_count, COALESCE(SUM(o.grand_total),0) AS total_spent" : "0 AS order_count, 0 AS total_spent";
    $customerCreatedOrder = columnExists($conn, "users", "created_at") ? "u.created_at DESC," : "";
    $customers = mysqli_query($conn, "
        SELECT u.id, u.fullname, u.email, u.phone, u.age, u.gender, u.location, u.address,
               " . (columnExists($conn, "users", "created_at") ? "u.created_at" : "NULL AS created_at") . ",
               $customerOrderFields
        FROM users u
        $customerOrderJoin
        WHERE $customerWhere
        GROUP BY u.id
        ORDER BY $customerCreatedOrder u.id DESC
    ");
}

$monthlySalesLabels = [];
$monthlySalesData = [];
if (tableExists($conn, "orders") && columnExists($conn, "orders", "created_at")) {
    $monthlyResult = mysqli_query($conn, "
        SELECT DATE_FORMAT(created_at, '%b %Y') AS month_label, COALESCE(SUM(grand_total),0) AS sales
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
          AND status != 'Cancelled'
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY YEAR(created_at), MONTH(created_at)
    ");
    if ($monthlyResult) {
        while ($row = mysqli_fetch_assoc($monthlyResult)) {
            $monthlySalesLabels[] = $row['month_label'];
            $monthlySalesData[] = (float)$row['sales'];
        }
    }
}

$orderStatusLabels = [];
$orderStatusData = [];
if (tableExists($conn, "orders") && columnExists($conn, "orders", "status")) {
    $statusResult = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM orders GROUP BY status ORDER BY total DESC");
    if ($statusResult) {
        while ($row = mysqli_fetch_assoc($statusResult)) {
            $orderStatusLabels[] = $row['status'] ?: 'Pending';
            $orderStatusData[] = (int)$row['total'];
        }
    }
}

$stockLabels = [];
$stockData = [];
if (tableExists($conn, "products")) {
    $stockResult = mysqli_query($conn, "SELECT name, stock FROM products ORDER BY stock ASC LIMIT 8");
    if ($stockResult) {
        while ($row = mysqli_fetch_assoc($stockResult)) {
            $stockLabels[] = $row['name'];
            $stockData[] = (int)$row['stock'];
        }
    }
}


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

function adminPageStart($activePage, $pageTitle = "Admin Panel") { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><i class="fa fa-bars"></i></button>
        </div>
        <nav class="admin-nav">
            <a class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>" href="admin.php"><i class="fa fa-chart-line"></i> <span>Dashboard</span></a>
            <a class="<?php echo $activePage === 'orders' ? 'active' : ''; ?>" href="admin_orders.php"><i class="fa fa-receipt"></i> <span>Customer Orders</span></a>
            <a class="<?php echo $activePage === 'stock' ? 'active' : ''; ?>" href="admin_stock.php"><i class="fa fa-warehouse"></i> <span>Stock</span></a>
            <a class="<?php echo $activePage === 'products' ? 'active' : ''; ?>" href="admin_products.php"><i class="fa fa-box"></i> <span>Products</span></a>
            <a class="<?php echo $activePage === 'customers' ? 'active' : ''; ?>" href="admin_customers.php"><i class="fa fa-users"></i> <span>Customers</span></a>
        </nav>
    </aside>
    <section class="admin-main">
<?php }

function adminAlerts($message, $error) { ?>
    <?php if ($message): ?><p class="success-message"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error-message"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
<?php }

function adminPageEnd() { ?>
    </section>
</main>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const layout = document.getElementById("adminLayout");
    const sidebar = document.getElementById("adminSidebar");
    const toggle = document.getElementById("sidebarToggle");
    if (toggle && layout && sidebar) {
        toggle.addEventListener("click", function () {
            if (window.innerWidth <= 1000) sidebar.classList.toggle("mobile-open");
            else layout.classList.toggle("sidebar-collapsed");
        });
    }
});
</script>
<script src="script.js"></script>
</body>
</html>
<?php }
