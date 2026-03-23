<?php

require 'admin/root.php';
session_start();

// Hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Phòng chống SQL Injection
if(isset($_POST)){
    foreach($_POST as $index=> $value){
        if(is_string($_POST[$index]))
        $_POST[$index] = htmlspecialchars($_POST[$index],ENT_QUOTES, "UTF-8");
    }   
}

if(isset($_GET)){
    foreach($_GET as $index=> $value){
        if(is_string($_GET[$index]))
        $_GET[$index] = htmlspecialchars($_GET[$index],ENT_QUOTES, "UTF-8");
    }   
}

$token = isset($_POST['token']) ? $_POST['token'] : '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Kiểm tra mật khẩu
if(empty($password)) {
    $_SESSION['error'] = "Vui lòng nhập mật khẩu";
    header('location:change_new_password.php?token=' . $token);
    exit;
}

if(strlen($password) < 6) {
    $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự";
    header('location:change_new_password.php?token=' . $token);
    exit;
}

if($password !== $confirm_password) {
    $_SESSION['error'] = "Mật khẩu xác nhận không khớp";
    header('location:change_new_password.php?token=' . $token);
    exit;
}

// Chuyển mật khẩu sang dạng md5
$password = md5(addslashes($password));

// Kiểm tra token có hợp lệ không
$sql = "SELECT customer_id from forgot_password
    where `token` = '$token'";

$result = mysqli_query($connect, $sql);
if(!$result) {
    $_SESSION['error'] = "Lỗi truy vấn: " . mysqli_error($connect);
    header('location:change_new_password.php?token=' . $token);
    exit;
}

if(mysqli_num_rows($result) !== 1){
    $_SESSION['error'] = "Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn";
    header('location:login.php');
    exit;
}

$customer_id = mysqli_fetch_array($result)['customer_id'];

// Cập nhật mật khẩu mới
$sql = "UPDATE customers
SET customers.password = '$password', customers.token = null, customers.updated_at = now()
WHERE id = '$customer_id'";
$result = mysqli_query($connect, $sql);

if(!$result) {
    $_SESSION['error'] = "Lỗi cập nhật mật khẩu: " . mysqli_error($connect);
    header('location:change_new_password.php?token=' . $token);
    exit;
}

// Xóa token đã sử dụng
$sql = "DELETE from forgot_password
where `token` = '$token'";
$result = mysqli_query($connect, $sql);

if(!$result) {
    // Không trả về lỗi cho người dùng vì mật khẩu đã được cập nhật
    error_log("Lỗi xóa token: " . mysqli_error($connect));
}

$_SESSION['success'] = "Đổi mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.";
header('location:login.php');
exit;

mysqli_close($connect);
