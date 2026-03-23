<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kết nối database
$connect = mysqli_connect('localhost', 'root', '', 'eyeglasses_shop');
if (!$connect) {
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}
mysqli_set_charset($connect, 'utf8');

// Include file helper.php
require_once 'admin/includes/helper.php';

// Hiển thị thông báo thành công
echo "Tất cả đã hoạt động tốt!";
?> 