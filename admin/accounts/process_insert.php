<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Kiểm tra phiên đăng nhập - chỉ cho phép admin truy cập
if (
    (!isset($_SESSION['level']) || $_SESSION['level'] != 1) && 
    (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') &&
    (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin')
) {
    header('location:../index.php?error=Bạn không có quyền truy cập chức năng này');
    exit();
}

// Kết nối đến database
require '../root.php';

// Kiểm tra nếu form đã được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy và kiểm tra dữ liệu
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;
    
    // Tạo mảng lưu trữ lỗi
    $errors = [];
    
    // Validate các trường bắt buộc
    if (empty($username)) {
        $errors[] = 'Tên đăng nhập không được để trống';
    } elseif (strlen($username) < 4 || strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Tên đăng nhập phải từ 4-20 ký tự, chỉ gồm chữ cái, số và dấu gạch dưới';
    }
    
    if (empty($password)) {
        $errors[] = 'Mật khẩu không được để trống';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và số';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }
    
    if (empty($email)) {
        $errors[] = 'Email không được để trống';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Định dạng email không hợp lệ';
    }
    
    if (empty($full_name)) {
        $errors[] = 'Họ tên không được để trống';
    }
    
    if (!empty($phone) && !preg_match('/^[0-9]{9,11}$/', $phone)) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }
    
    if (empty($role_id)) {
        $errors[] = 'Vai trò không được để trống';
    }
    
    // Kiểm tra tên đăng nhập và email đã tồn tại chưa
    if (empty($errors)) {
        $username_escaped = mysqli_real_escape_string($connect, $username);
        $check_username_sql = "SELECT COUNT(*) as count FROM users WHERE username = '$username_escaped'";
        $check_username_result = mysqli_query($connect, $check_username_sql);
        
        if (!$check_username_result) {
            $errors[] = 'Lỗi kiểm tra tên đăng nhập: ' . mysqli_error($connect);
        } else {
            $row = mysqli_fetch_assoc($check_username_result);
            if ($row['count'] > 0) {
                $errors[] = 'Tên đăng nhập đã tồn tại trong hệ thống';
            }
        }
        
        $email_escaped = mysqli_real_escape_string($connect, $email);
        $check_email_sql = "SELECT COUNT(*) as count FROM users WHERE email = '$email_escaped'";
        $check_email_result = mysqli_query($connect, $check_email_sql);
        
        if (!$check_email_result) {
            $errors[] = 'Lỗi kiểm tra email: ' . mysqli_error($connect);
        } else {
            $row = mysqli_fetch_assoc($check_email_result);
            if ($row['count'] > 0) {
                $errors[] = 'Email đã tồn tại trong hệ thống';
            }
        }
    }
    
    // Nếu không có lỗi, tiến hành thêm tài khoản
    if (empty($errors)) {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Chuẩn bị giá trị an toàn để chèn vào database
        $username_escaped = mysqli_real_escape_string($connect, $username);
        $email_escaped = mysqli_real_escape_string($connect, $email);
        $full_name_escaped = mysqli_real_escape_string($connect, $full_name);
        $phone_escaped = mysqli_real_escape_string($connect, $phone);
        $address_escaped = mysqli_real_escape_string($connect, $address);
        $role_id = (int)$role_id; // Đảm bảo là số nguyên
        
        // Tạo câu lệnh SQL
        $sql = "INSERT INTO users (username, password, email, full_name, phone, address, role_id, created_at) 
                VALUES ('$username_escaped', '$hashed_password', '$email_escaped', '$full_name_escaped', 
                        '$phone_escaped', '$address_escaped', $role_id, NOW())";
        
        // Thực hiện câu lệnh
        $result = mysqli_query($connect, $sql);
        
        if ($result) {
            // Lấy ID của user vừa thêm
            $user_id = mysqli_insert_id($connect);
            
            // Ghi log hoạt động
            $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
            $log_sql = "INSERT INTO activity_logs (user_id, activity, details, created_at) 
                        VALUES ($admin_id, 'Thêm tài khoản', 'Đã thêm tài khoản mới cho $full_name_escaped (ID: $user_id)', NOW())";
            mysqli_query($connect, $log_sql);
            
            // Chuyển hướng với thông báo thành công
            header('Location: index.php?success=Đã thêm tài khoản thành công');
            exit();
        } else {
            $errors[] = 'Lỗi thêm tài khoản: ' . mysqli_error($connect);
        }
    }
    
    // Nếu có lỗi, chuyển hướng về form với thông báo lỗi
    if (!empty($errors)) {
        $error_string = implode('<br>', $errors);
        header('Location: form_insert.php?error=' . urlencode($error_string));
        exit();
    }
} else {
    // Nếu không phải là POST request, chuyển hướng về trang form
    header('Location: form_insert.php');
    exit();
}

mysqli_close($connect);
