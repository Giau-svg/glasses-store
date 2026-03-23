<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

require 'root.php';
session_start();

// Reset số lần redirect ngay khi bắt đầu xử lý đăng nhập
$_SESSION['admin_redirect_count'] = 0;

// Kiểm tra dữ liệu từ form
if (empty($_POST['email']) || empty($_POST['password'])) {
    header('location:index.php?error=Vui lòng điền đầy đủ thông tin đăng nhập');
    exit;
}

$email = mysqli_real_escape_string($connect, $_POST['email']);
$password = $_POST['password'];

// Đăng nhập nhanh với mật khẩu và email cố định
if ($email == 'admin@opticvision.com' && $password == 'admin123') {
    $role = 'admin';
    
    // Kiểm tra xem tài khoản đã tồn tại chưa
    $sql_check = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt_check = mysqli_prepare($connect, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        // Tạo tài khoản mới nếu chưa tồn tại
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $full_name = 'Admin';
        $username = 'admin';
        
        $sql_insert = "INSERT INTO users (username, email, password, full_name, role_id) VALUES (?, ?, ?, ?, 1)";
        $stmt_insert = mysqli_prepare($connect, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "ssss", $username, $email, $hashed_password, $full_name);
        mysqli_stmt_execute($stmt_insert);
        
        $user_id = mysqli_insert_id($connect);
    } else {
        $user = mysqli_fetch_assoc($result_check);
        $user_id = $user['user_id'];
        $full_name = $user['full_name'];
    }
    
    // Xóa session admin cũ để tránh xung đột, nhưng giữ nguyên các session khác
    // Lưu các biến session không phải admin
    $temp_staff = isset($_SESSION['staff_user_id']) ? $_SESSION['staff_user_id'] : null;
    $temp_staff_name = isset($_SESSION['staff_name']) ? $_SESSION['staff_name'] : null;
    $temp_staff_role = isset($_SESSION['staff_role']) ? $_SESSION['staff_role'] : null;
    
    $temp_customer = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;
    $temp_customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : null;
    
    // Xóa các session admin
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'admin_') === 0) {
            unset($_SESSION[$key]);
        }
    }
    
    // Khôi phục các session khác
    if ($temp_staff) $_SESSION['staff_user_id'] = $temp_staff;
    if ($temp_staff_name) $_SESSION['staff_name'] = $temp_staff_name;
    if ($temp_staff_role) $_SESSION['staff_role'] = $temp_staff_role;
    
    if ($temp_customer) $_SESSION['customer_id'] = $temp_customer;
    if ($temp_customer_name) $_SESSION['customer_name'] = $temp_customer_name;
    
    // Thiết lập các biến session cho admin
    $_SESSION['admin_user_id'] = $user_id;
    $_SESSION['admin_name'] = $full_name;
    $_SESSION['admin_full_name'] = $full_name;
    $_SESSION['admin_role'] = 'admin';
    $_SESSION['admin_level'] = 1;
    $_SESSION['admin_redirect_count'] = 0;
    
    // Ghi log đăng nhập
    $activity = 'Đăng nhập hệ thống';
    $details = 'Đăng nhập với email ' . $email;
    $sql_log = "INSERT INTO activity_logs (user_id, activity, details, created_at) 
               VALUES (?, ?, ?, NOW())";
    $stmt_log = mysqli_prepare($connect, $sql_log);
    mysqli_stmt_bind_param($stmt_log, "iss", $user_id, $activity, $details);
    mysqli_stmt_execute($stmt_log);
    
    // Chuyển hướng đến dashboard admin
    header('location:dashboard/index.php');
    exit;
} else if ($email == 'sales@opticvision.com' && $password == 'sales123' || 
           $email == 'inventory@opticvision.com' && $password == 'inventory123') {
    // Nếu đây là nhân viên bán hàng hoặc quản lý kho, chuyển hướng đến trang staff
    header('location:../staff/login.php');
    exit;
}

// Kiểm tra tài khoản trong bảng users (chỉ cho phép vai trò admin)
$sql = "SELECT * FROM users WHERE email = ? AND role_id = 1 LIMIT 1";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);
    
    // Kiểm tra mật khẩu
    if (password_verify($password, $user['password'])) {
        // Xóa session admin cũ để tránh xung đột, nhưng giữ nguyên các session khác
        // Lưu các biến session không phải admin
        $temp_staff = isset($_SESSION['staff_user_id']) ? $_SESSION['staff_user_id'] : null;
        $temp_staff_name = isset($_SESSION['staff_name']) ? $_SESSION['staff_name'] : null;
        $temp_staff_role = isset($_SESSION['staff_role']) ? $_SESSION['staff_role'] : null;
        
        $temp_customer = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;
        $temp_customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : null;
        
        // Xóa các session admin
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'admin_') === 0) {
                unset($_SESSION[$key]);
            }
        }
        
        // Khôi phục các session khác
        if ($temp_staff) $_SESSION['staff_user_id'] = $temp_staff;
        if ($temp_staff_name) $_SESSION['staff_name'] = $temp_staff_name;
        if ($temp_staff_role) $_SESSION['staff_role'] = $temp_staff_role;
        
        if ($temp_customer) $_SESSION['customer_id'] = $temp_customer;
        if ($temp_customer_name) $_SESSION['customer_name'] = $temp_customer_name;
        
        // Thiết lập các biến session cho admin
        $_SESSION['admin_user_id'] = $user['user_id'];
        $_SESSION['admin_name'] = $user['full_name'];
        $_SESSION['admin_role'] = ($user['role_id'] == 1) ? 'admin' : '';
        $_SESSION['admin_level'] = 1;
        $_SESSION['admin_redirect_count'] = 0;
        
        // Ghi nhớ đăng nhập
        if (isset($_POST['remember'])) {
            $token = md5(time() . $email);
            $sql_update_token = "UPDATE users SET token = ? WHERE user_id = ?";
            $stmt_token = mysqli_prepare($connect, $sql_update_token);
            mysqli_stmt_bind_param($stmt_token, "si", $token, $user['user_id']);
            mysqli_stmt_execute($stmt_token);
            
            setcookie('admin_remember', $token, time() + 86400 * 30);
        }
        
        // Ghi log đăng nhập
        $activity = 'Đăng nhập hệ thống';
        $details = 'Đăng nhập thành công vào hệ thống';
        
        $sql_log = "INSERT INTO activity_logs (user_id, activity, details, created_at) 
                   VALUES (?, ?, ?, NOW())";
        $stmt_log = mysqli_prepare($connect, $sql_log);
        mysqli_stmt_bind_param($stmt_log, "iss", $user['user_id'], $activity, $details);
        mysqli_stmt_execute($stmt_log);
        
        // Chuyển hướng đến trang dashboard
        header('location:dashboard/index.php');
        exit;
    } else {
        header('location:index.php?error=Mật khẩu không chính xác');
        exit;
    }
} else {
    // Kiểm tra nếu đây là tài khoản nhân viên, chuyển hướng đến trang đăng nhập của nhân viên
    $sql_staff = "SELECT * FROM users WHERE email = ? AND role_id IN (3, 4) LIMIT 1";
    $stmt_staff = mysqli_prepare($connect, $sql_staff);
    mysqli_stmt_bind_param($stmt_staff, "s", $email);
    mysqli_stmt_execute($stmt_staff);
    $result_staff = mysqli_stmt_get_result($stmt_staff);
    
    if (mysqli_num_rows($result_staff) === 1) {
        header('location:../staff/login.php?message=Vui lòng đăng nhập tại khu vực nhân viên');
        exit;
    }
    
    // Nếu không phải admin hoặc nhân viên
    header('location:index.php?error=Email không tồn tại hoặc bạn không có quyền truy cập vào khu vực này');
    exit;
}

mysqli_close($connect);
