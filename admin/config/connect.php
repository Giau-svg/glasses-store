<?php
// Kết nối tới cơ sở dữ liệu
$connect = mysqli_connect('localhost', 'root', '', 'eyeglasses_shop');

// Kiểm tra kết nối
if (!$connect) {
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}

// Thiết lập charset
mysqli_set_charset($connect, 'utf8');
?> 