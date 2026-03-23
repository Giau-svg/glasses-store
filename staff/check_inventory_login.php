<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug thông tin session
error_log("CHECK INVENTORY LOGIN - staff_role: " . (isset($_SESSION['staff_role']) ? $_SESSION['staff_role'] : 'not set'));
error_log("CHECK INVENTORY LOGIN - staff_user_id: " . (isset($_SESSION['staff_user_id']) ? $_SESSION['staff_user_id'] : 'not set'));

// Kiểm tra xem đã đăng nhập chưa
if (!isset($_SESSION['staff_user_id']) || !isset($_SESSION['staff_role'])) {
    // Chưa đăng nhập, chuyển hướng về trang đăng nhập
    header('Location: ../index.php?error=Vui lòng đăng nhập để tiếp tục');
    exit;
}

// Kiểm tra phải nhân viên quản lý kho không
if ($_SESSION['staff_role'] != 'inventory') {
    // Debug thông tin
    error_log("REDIRECT FROM INVENTORY - Invalid role detected: " . $_SESSION['staff_role']);
    
    // Không phải nhân viên quản lý kho, chuyển hướng về trang thích hợp
    if ($_SESSION['staff_role'] == 'sales') {
        header('Location: ../sales/dashboard.php?error=Bạn không có quyền truy cập vào khu vực này');
    } else {
        header('Location: ../index.php?error=Bạn không có quyền truy cập vào khu vực này');
    }
    exit;
} 

// Debug thông tin - xác nhận là nhân viên kho hợp lệ
error_log("INVENTORY ACCESS GRANTED for user ID: " . $_SESSION['staff_user_id']); 