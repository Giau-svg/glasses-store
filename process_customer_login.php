<?php
session_start();

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'admin/root.php';

// Kiểm tra dữ liệu đầu vào
if (empty($_POST['identifier']) || empty($_POST['password'])) {
    $error = 'Vui lòng nhập đầy đủ thông tin đăng nhập';
    header("Location: login.php?error=$error");
    exit;
}

// Lấy thông tin đăng nhập
$identifier = addslashes($_POST['identifier']);
$password = $_POST['password'];

// Kiểm tra xem identifier là email hay username và chỉ lấy tài khoản khách hàng (role_id = 2)
$sql = "SELECT * FROM users 
        WHERE (email = '$identifier' OR username = '$identifier') 
        AND role_id = 2";

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
    } else {
        $error = 'Mật khẩu không chính xác';
        header("Location: login.php?error=$error");
        exit;
    }
} else {
    $error = 'Tài khoản không tồn tại hoặc không phải tài khoản khách hàng';
    header("Location: login.php?error=$error");
    exit;
}

mysqli_close($connect); 