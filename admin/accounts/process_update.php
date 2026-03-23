<?php

session_start();
if(empty($_SESSION['level'] > 0)){
    header('location:../index.php');
}
require '../root.php';

$id = $_POST['id'];

$username = addslashes($_POST['username']);
$username = strip_tags($username);

$email = trim(addslashes($_POST['email']));
$email = strip_tags($email);

$full_name = addslashes($_POST['full_name']);
$full_name = strip_tags($full_name);

$phone = addslashes($_POST['phone']);
$role_id = $_POST['role_id'];
$address = addslashes($_POST['address']);

if(empty($_POST['id'])){
    header('location:form_update.php?error=Phải điền mã để sửa');
    exit;
}

if(!empty($_POST['username']) || !empty($_POST['email']) || !empty($_POST['full_name']) || !empty($_POST['phone'])){
    $sql = "UPDATE `users`
    SET
    username = '$username',
    email = '$email',
    full_name = '$full_name',
    phone = '$phone',
    role_id = '$role_id',
    address = '$address',
    updated_at = NOW()
    WHERE user_id = '$id'";
    
    $query = mysqli_query($connect, $sql);
    
    if ($query) {
        header('location:index.php?success=Sửa thành công');
    } else{
        $error = mysqli_error($connect);
        header("location:form_update.php?id=$id&error=Lỗi: $error");
    }
} else {
    header("location:form_update.php?id=$id&error=Phải điền đủ thông tin");
}

mysqli_close($connect);


