<?php
session_start();

require 'admin/root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Lấy thông tin từ form
    $full_name = trim(addslashes($_POST['name'] ?? ''));
    $email = trim(addslashes($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $phone = addslashes($_POST['phone'] ?? '');
    $address = trim(addslashes($_POST['address'] ?? ''));
    
    // Kiểm tra dữ liệu đầu vào
    if (empty($full_name) || empty($email) || empty($password)) {
        throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc!");
    }

    // Tạo username từ email
    $username = trim(explode('@', $email)[0]);
    
    // Mã hóa mật khẩu
    $hashed_password = md5($password);

    // Kiểm tra email đã tồn tại chưa
    $sql = "SELECT count(*) as count FROM users WHERE email = '$email'";
    $result = mysqli_query($connect, $sql);
    
    if (!$result) {
        throw new Exception("Lỗi truy vấn: " . mysqli_error($connect));
    }
    
    $number_rows = mysqli_fetch_assoc($result)['count'];
    
    if ($number_rows > 0) {
        throw new Exception('Email này đã tồn tại!');
    }

    // Lấy role_id của customer
    $sql = "SELECT role_id FROM roles WHERE role_name = 'customer'";
    $result = mysqli_query($connect, $sql);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        throw new Exception("Không tìm thấy vai trò 'customer' trong hệ thống!");
    }
    
    $role_id = mysqli_fetch_assoc($result)['role_id'];

    // Thêm người dùng vào bảng users - loại bỏ trường gender
    $sql = "INSERT INTO users(username, password, email, full_name, phone, address, role_id, created_at) 
            VALUES ('$username', '$hashed_password', '$email', '$full_name', '$phone', '$address', $role_id, NOW())";
    
    if (!mysqli_query($connect, $sql)) {
        throw new Exception("Lỗi khi thêm tài khoản: " . mysqli_error($connect));
    }

    // Lấy user_id vừa tạo
    $user_id = mysqli_insert_id($connect);
    
    // Lưu thông tin đăng nhập vào session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['name'] = $full_name;
    $_SESSION['role'] = $role_id;
    
    // Thông báo thành công
    $_SESSION['success'] = "Đăng ký tài khoản thành công!";
    
    // Chuyển hướng về trang đăng nhập
    header("Location: login.php");
    exit;
    
} catch (Exception $e) {
    // Ghi lại lỗi vào session
    $_SESSION['error'] = $e->getMessage();
    
    // Debug mode - hiển thị chi tiết lỗi
    error_log("Lỗi đăng ký: " . $e->getMessage());
    
    // Chuyển hướng về trang đăng ký
    header("Location: signup.php");
    exit;
}