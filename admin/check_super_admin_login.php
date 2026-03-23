<?php
session_start();

// Kiểm tra quyền admin với biến session riêng biệt
if (
    // Kiểm tra các biến session admin riêng biệt
    (!isset($_SESSION['admin_level']) || $_SESSION['admin_level'] != 1) && 
    (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin')
) {
    // Add debugging information
    $debug_info = "SESSION data: " . json_encode($_SESSION);
    error_log($debug_info);
    
    header('location:../index.php?error=Bạn không có quyền truy cập trang này');
    exit;
}