<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['staff_user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Kiểm tra quyền truy cập
$allowed_roles = ['admin', 'inventory_manager', 'sales_staff'];
if (!in_array($_SESSION['staff_role'], $allowed_roles)) {
    header('Location: ../unauthorized.php');
    exit();
}

// Kết nối cơ sở dữ liệu
require_once '../config/database.php';
$conn = connectDB();
?> 