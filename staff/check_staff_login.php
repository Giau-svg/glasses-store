<?php
session_start();

// Kiểm tra xem đã đăng nhập chưa
if (!isset($_SESSION['staff_user_id']) || !isset($_SESSION['staff_role'])) {
    // Chưa đăng nhập, chuyển hướng về trang đăng nhập
    header('Location: ../index.php?error=Vui lòng đăng nhập để tiếp tục');
    exit;
}

// Kiểm tra vai trò nhân viên
if ($_SESSION['staff_role'] != 'sales' && $_SESSION['staff_role'] != 'inventory') {
    // Không phải nhân viên bán hàng hoặc quản lý kho, chuyển hướng về trang đăng nhập
    header('Location: ../index.php?error=Bạn không có quyền truy cập vào khu vực này');
    exit;
} 