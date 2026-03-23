<?php
// busmanage/root.php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'eyeglasses_shop';

$connect = new mysqli($host, $username, $password, $dbname);

if ($connect->connect_error) {
    die("Kết nối thất bại: " . $connect->connect_error);
}

$connect->set_charset("utf8");

// Thiết lập quyền truy cập cho business manager (ví dụ: chỉ được truy cập các bảng orders, stock_receipts, products)
$allowed_tables = ['orders', 'stock_receipts', 'products', 'order_details', 'stock_receipt_details', 'suppliers'];
?>