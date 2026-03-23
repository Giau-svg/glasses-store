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
    header('location:form_insert.php?error=Vui lòng gửi form đúng cách');
    exit;
}

// Lấy dữ liệu từ form
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Validate dữ liệu
if (empty($name)) {
    header('location:form_insert.php?error=Vui lòng nhập tên nhà sản xuất');
    exit;
}

try {
    // Kiểm tra xem tên nhà sản xuất đã tồn tại chưa
    $check_sql = "SELECT * FROM manufacturers WHERE name = ?";
    $check_stmt = mysqli_prepare($connect, $check_sql);
    
    if (!$check_stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn: " . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($check_stmt, "s", $name);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) > 0) {
        header('location:form_insert.php?error=Tên nhà sản xuất đã tồn tại');
        exit;
    }
    
    // Thêm nhà sản xuất vào database
    $insert_sql = "INSERT INTO manufacturers (name, description) VALUES (?, ?)";
    $insert_stmt = mysqli_prepare($connect, $insert_sql);
    
    if (!$insert_stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn: " . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($insert_stmt, "ss", $name, $description);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        header('location:index.php?success=Thêm nhà sản xuất thành công');
    } else {
        throw new Exception("Lỗi thực thi truy vấn: " . mysqli_stmt_error($insert_stmt));
    }
} catch (Exception $e) {
    header('location:form_insert.php?error=' . urlencode($e->getMessage()));
}

mysqli_close($connect);
