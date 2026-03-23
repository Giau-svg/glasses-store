<?php
session_start();

if (isset($_SESSION['admin_user_id'])) {
    require_once 'root.php';
    
    // Ghi log đăng xuất
    $user_id = $_SESSION['admin_user_id'];
    $activity = 'Đăng xuất hệ thống';
    $details = 'Đăng xuất khỏi hệ thống admin';
    
    $sql_log = "INSERT INTO activity_logs (user_id, activity, details, created_at) 
               VALUES (?, ?, ?, NOW())";
    $stmt_log = mysqli_prepare($connect, $sql_log);
    mysqli_stmt_bind_param($stmt_log, "iss", $user_id, $activity, $details);
    mysqli_stmt_execute($stmt_log);
}

// Xóa cookie admin
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/');
}

// Xóa tất cả các biến session admin
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'admin_') === 0) {
        unset($_SESSION[$key]);
    }
}

// Chuyển hướng về trang đăng nhập admin
header('location:index.php?success=Đăng xuất thành công');
exit;