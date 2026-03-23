<?php

require 'admin/root.php';
session_start();

// Bật hiển thị lỗi để debug
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

function current_url(){
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    return $url;
}

// Kiểm tra email có được submit không
if(empty($_POST['email'])) {
    $_SESSION['error'] = 'Vui lòng nhập email';
    header('location:forgot_password.php');
    exit;
}

$email = strip_tags($_POST['email']);

$sql = "SELECT id, name from customers
where `email` = '$email'";
$result = mysqli_query($connect, $sql);

if(!$result) {
    $_SESSION['error'] = 'Lỗi truy vấn: ' . mysqli_error($connect);
    header('location:forgot_password.php');
    exit;
}

if(mysqli_num_rows($result) === 1 ){
    $each = mysqli_fetch_array($result);
    $id = $each['id'];
    $name = $each['name'];
    
    // Xóa token cũ
    $sql = "DELETE from forgot_password
    where `customer_id` = '$id'";
    if(!mysqli_query($connect, $sql)) {
        $_SESSION['error'] = 'Lỗi xóa token cũ: ' . mysqli_error($connect);
        header('location:forgot_password.php');
        exit;
    }
    
    // Tạo token mới
    $token = uniqid();
    $sql = "INSERT INTO forgot_password(customer_id, token)
    VALUES('$id', '$token')";
    if(!mysqli_query($connect, $sql)) {
        $_SESSION['error'] = 'Lỗi tạo token mới: ' . mysqli_error($connect);
        header('location:forgot_password.php');
        exit;
    }

    $link = current_url() . '/change_new_password.php?token=' . $token;
    
    // Log để debug
    error_log("Link reset: " . $link);
    error_log("Email gửi đến: " . $email);
    
    try {
        require './sendmail/server/send-mail.php';
        $title = "Đặt lại mật khẩu";
        $content = "Bấm vào đây để đặt lại mật khẩu của bạn: <a href='$link'>Đặt lại mật khẩu</a>";
        error_log("Đang cố gửi email đến: " . $email);
        
        mySendMail($email, $title, $name, $content);
        
        $_SESSION['success'] = 'Vui lòng kiểm tra email của bạn để đổi mật khẩu';
        header('location:forgot_password.php');
        exit;
    } catch (Exception $e) {
        error_log("Lỗi gửi email: " . $e->getMessage());
        $_SESSION['error'] = 'Lỗi gửi email: ' . $e->getMessage();
        header('location:forgot_password.php');
        exit;
    }
} else { 
    $_SESSION['error'] = 'Email không tồn tại hoặc đã bị vô hiệu hóa';
    header('location:forgot_password.php');
    exit;
}

mysqli_close($connect);




