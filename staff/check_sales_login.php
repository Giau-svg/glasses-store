<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug thông tin session
error_log("CHECK SALES LOGIN - staff_role: " . (isset($_SESSION['staff_role']) ? $_SESSION['staff_role'] : 'not set'));
error_log("CHECK SALES LOGIN - staff_user_id: " . (isset($_SESSION['staff_user_id']) ? $_SESSION['staff_user_id'] : 'not set'));

// Kiểm tra xem đã đăng nhập chưa
if (!isset($_SESSION['staff_user_id']) || !isset($_SESSION['staff_role'])) {
    // Chưa đăng nhập, chuyển hướng về trang đăng nhập
    header('Location: ../index.php?error=Vui lòng đăng nhập để tiếp tục');
    exit;
}

// Kiểm tra phải nhân viên bán hàng không
if ($_SESSION['staff_role'] != 'sales') {
    // Debug thông tin
    error_log("REDIRECT FROM SALES - Invalid role detected: " . $_SESSION['staff_role']);
    
    // Không phải nhân viên bán hàng, chuyển hướng về trang thích hợp
    if ($_SESSION['staff_role'] == 'inventory') {
        header('Location: ../inventory/dashboard.php?error=Bạn không có quyền truy cập vào khu vực này');
    } else {
        header('Location: ../index.php?error=Bạn không có quyền truy cập vào khu vực này');
    }
    exit;
} 

// Debug thông tin - xác nhận là nhân viên bán hàng hợp lệ
error_log("SALES ACCESS GRANTED for user ID: " . $_SESSION['staff_user_id']); 