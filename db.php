<?php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'solarmart';
$DB_PORT = 3306; // Change to 3306 if your XAMPP MySQL uses 3306.

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if (!$conn && $DB_PORT !== 3306) {
    $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, 3306);
}
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
?>
