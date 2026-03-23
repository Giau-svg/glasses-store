<?php
function connectDB() {
    // Thông tin kết nối database
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'eyeglasses_shop';

    // Tạo kết nối
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

    // Kiểm tra kết nối
    if (!$conn) {
        die("Kết nối thất bại: " . mysqli_connect_error());
    }

    // Đặt charset là utf8
    mysqli_set_charset($conn, "utf8");

    return $conn;
}
?> 