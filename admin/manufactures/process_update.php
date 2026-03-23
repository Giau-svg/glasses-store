<?php
// Bật hiển thị lỗi để dễ dàng debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../root.php';
require '../check_admin_login.php';

// Kiểm tra kết nối database
if (!$connect) {
    die("Lỗi kết nối database: " . mysqli_connect_error());
}

// Kiểm tra form đã được submit chưa
if (!isset($_POST['btn-submit'])) {
    header('location:index.php?error=Vui lòng gửi form đúng cách');
    exit;
}

// Lấy dữ liệu từ form
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Validate dữ liệu
if (empty($id)) {
    header('location:index.php?error=ID không hợp lệ');
    exit;
}

if (empty($name)) {
    header('location:form_update.php?id=' . $id . '&error=Vui lòng nhập tên nhà sản xuất');
    exit;
}

try {
    // Kiểm tra xem nhà sản xuất có tồn tại không
    $check_exists_sql = "SELECT * FROM manufacturers WHERE id = ?";
    $check_exists_stmt = mysqli_prepare($connect, $check_exists_sql);
    
    if (!$check_exists_stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn: " . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($check_exists_stmt, "i", $id);
    mysqli_stmt_execute($check_exists_stmt);
    $result = mysqli_stmt_get_result($check_exists_stmt);
    
    if (mysqli_num_rows($result) == 0) {
        header('location:index.php?error=Không tìm thấy nhà sản xuất');
        exit;
    }
    
    // Kiểm tra xem tên nhà sản xuất đã tồn tại chưa (không tính nhà sản xuất hiện tại)
    $check_name_sql = "SELECT * FROM manufacturers WHERE name = ? AND id != ?";
    $check_name_stmt = mysqli_prepare($connect, $check_name_sql);
    
    if (!$check_name_stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn: " . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($check_name_stmt, "si", $name, $id);
    mysqli_stmt_execute($check_name_stmt);
    $name_result = mysqli_stmt_get_result($check_name_stmt);
    
    if (mysqli_num_rows($name_result) > 0) {
        header('location:form_update.php?id=' . $id . '&error=Tên nhà sản xuất đã tồn tại');
        exit;
    }
    
    // Cập nhật thông tin nhà sản xuất
    $update_sql = "UPDATE manufacturers SET name = ?, description = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($connect, $update_sql);
    
    if (!$update_stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn: " . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($update_stmt, "ssi", $name, $description, $id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        header('location:index.php?success=Cập nhật nhà sản xuất thành công');
    } else {
        throw new Exception("Lỗi thực thi truy vấn: " . mysqli_stmt_error($update_stmt));
    }
} catch (Exception $e) {
    header('location:form_update.php?id=' . $id . '&error=' . urlencode($e->getMessage()));
}

mysqli_close($connect);