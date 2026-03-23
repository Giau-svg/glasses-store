<?php
session_start();

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'admin/root.php';

// Xác định nguồn gọi đến process_login.php
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$isStaffLogin = strpos($referer, 'staff/login.php') !== false;
$isAdminLogin = strpos($referer, 'admin/index.php') !== false || strpos($referer, 'admin/login.php') !== false;

// Xác định trang redirect khi có lỗi
$errorRedirect = 'login.php'; // Mặc định là trang login khách hàng
if ($isStaffLogin) {
    $errorRedirect = 'staff/login.php';
} else if ($isAdminLogin) {
    $errorRedirect = 'admin/index.php';
}

// Phòng chống SQL Injection

// if (isset($_POST)) {
//     foreach ($_POST as $index => $value) {
//         if (is_string($_POST[$index]))
//             $_POST[$index] = htmlspecialchars($_POST[$index], ENT_QUOTES, "UTF-8");
//     }
// }

// if (isset($_GET)) {
//     foreach ($_GET as $index => $value) {
//         if (is_string($_GET[$index]))
//             $_GET[$index] = htmlspecialchars($_GET[$index], ENT_QUOTES, "UTF-8");
//     }
// }

if (empty($_POST['identifier']) || empty($_POST['password'])) {
    $error = 'Vui lòng nhập đầy đủ thông tin đăng nhập';
    header("Location: $errorRedirect?error=$error");
    exit;
}

// Lấy thông tin đăng nhập
$identifier = addslashes($_POST['identifier']);
$password = $_POST['password'];

// Kiểm tra xem identifier là email hay username
$sql = "SELECT * FROM users 
        WHERE (email = '$identifier' OR username = '$identifier')";

$result = mysqli_query($connect, $sql);

if (mysqli_num_rows($result) === 1) {
    $each = mysqli_fetch_array($result);
    
    // Kiểm tra mật khẩu - hỗ trợ cả hai loại mã hóa (md5 và password_hash)
    $is_valid_password = false;
    
    // Kiểm tra nếu mật khẩu được lưu dưới dạng md5
    if (md5($password) === $each['password']) {
        $is_valid_password = true;
    } 
    // Kiểm tra nếu mật khẩu được lưu dưới dạng password_hash
    else if (password_verify($password, $each['password'])) {
        $is_valid_password = true;
    }
    
    if ($is_valid_password) {
        // Xác định vai trò người dùng
        $role = $each['role_id'];
        
        // Tạo các phiên đăng nhập riêng biệt dựa trên vai trò
        if ($role == 1) { // Admin
            // Lưu thông tin vào session admin
            $_SESSION['admin_user_id'] = $each['user_id'];
            $_SESSION['admin_name'] = $each['full_name'];
            $_SESSION['admin_role'] = 'admin';
            $_SESSION['admin_level'] = 1;
            
            // Ghi nhớ đăng nhập nếu được chọn
            if (isset($_POST['remember'])) {
                $token = md5(time() . $identifier);
                $sql_update_token = "UPDATE users SET token = '$token' WHERE user_id = " . $each['user_id'];
                mysqli_query($connect, $sql_update_token);
                
                setcookie('admin_remember', $token, time() + 86400 * 30);
            }
            
            header('Location: admin/dashboard/index.php');
            exit;
        } 
        else if ($role == 2) { // Customer
            // Lưu thông tin vào session customer
            $_SESSION['customer_id'] = $each['user_id'];
            $_SESSION['customer_name'] = $each['full_name'];
            
            // Ghi nhớ đăng nhập nếu được chọn
            if (isset($_POST['remember'])) {
                $token = md5(time() . $identifier);
                $sql_update_token = "UPDATE users SET token = '$token' WHERE user_id = " . $each['user_id'];
                mysqli_query($connect, $sql_update_token);
                
                setcookie('customer_remember', $token, time() + 86400 * 30);
            }
            
            header('Location: index.php');
            exit;
        } 
        else if ($role == 3 || $role == 4) { // Sales hoặc Inventory
            // Lưu thông tin vào session staff
            $_SESSION['staff_user_id'] = $each['user_id'];
            $_SESSION['staff_name'] = $each['full_name'];
            $_SESSION['staff_role'] = ($role == 3) ? 'sales' : 'inventory';
            
            // Ghi nhớ đăng nhập nếu được chọn
            if (isset($_POST['remember'])) {
                $token = md5(time() . $identifier);
                $sql_update_token = "UPDATE users SET token = '$token' WHERE user_id = " . $each['user_id'];
                mysqli_query($connect, $sql_update_token);
                
                setcookie('staff_remember', $token, time() + 86400 * 30);
            }
            
            $redirect = ($role == 3) ? 'staff/index.php' : 'staff/inventory/index.php';
            header('Location: ' . $redirect);
            exit;
        } 
        else {
            // Vai trò khác, mặc định về trang chủ
            $_SESSION['customer_id'] = $each['user_id'];
            $_SESSION['customer_name'] = $each['full_name'];
            
            header('Location: index.php');
            exit;
        }
    } else {
        $error = 'Mật khẩu không chính xác';
        header("Location: $errorRedirect?error=$error");
        exit;
    }
} else {
    $error = 'Tài khoản không tồn tại';
    header("Location: $errorRedirect?error=$error");
    exit;
}

mysqli_close($connect);
