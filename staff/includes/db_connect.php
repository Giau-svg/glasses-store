<?php
// File: /Pure/staff/includes/db_connect.php
// Chứa mã PHP để kết nối database cho phần Staff (hoặc chung nếu không có file khác)

$servername = "localhost";
$username = "root";
$password = ""; // Mặc định để trống trong Laragon
$dbname = "eyeglasses_shop"; // Tên database bạn đã tạo trong phpMyAdmin
$port = 3306;

// Tạo kết nối
$connect = new mysqli($servername, $username, $password, $dbname, $port);

// Kiểm tra kết nối
if ($connect->connect_error) {
    error_log("Kết nối cơ sở dữ liệu thất bại: " . $connect->connect_error, 0);
    die("Có lỗi xảy ra khi kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

// Thiết lập bộ ký tự
$connnect->set_charset("utf8mb4");

// Biến $conn chứa đối tượng kết nối sẽ có sẵn sau khi include file này.
?>