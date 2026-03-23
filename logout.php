<?php 
session_start();

// Chỉ xóa các session của khách hàng
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['cart']);
unset($_SESSION['carts']);

// Xóa cookie ghi nhớ đăng nhập của khách hàng
setcookie('customer_remember', null, -1, "/");

header('location:index.php');