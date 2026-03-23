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

// Kiểm tra ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('location:index.php?error=ID không hợp lệ');
    exit;
}

$id = (int)$_GET['id'];

try {
    // Kiểm tra xem ID có tồn tại không
    $check_sql = "SELECT * FROM manufacturers WHERE id = ?";
    $check_stmt = mysqli_prepare($connect, $check_sql);
    
    if (!$check_stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn: " . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) === 0) {
        header('location:index.php?error=Không tìm thấy nhà sản xuất');
        exit;
    }
    
    // Kiểm tra xem nhà sản xuất có đang được sử dụng trong bảng sản phẩm không
    $check_product_sql = "SELECT * FROM products WHERE manufacturer_id = ?";
    $check_product_stmt = mysqli_prepare($connect, $check_product_sql);
    
    if (!$check_product_stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn: " . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($check_product_stmt, "i", $id);
    mysqli_stmt_execute($check_product_stmt);
    $product_result = mysqli_stmt_get_result($check_product_stmt);
    
    if (mysqli_num_rows($product_result) > 0) {
        header('location:index.php?error=Không thể xóa nhà sản xuất đang được sử dụng trong sản phẩm');
        exit;
    }
    
    // Xóa nhà sản xuất
    $delete_sql = "DELETE FROM manufacturers WHERE id = ?";
    $delete_stmt = mysqli_prepare($connect, $delete_sql);
    
    if (!$delete_stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn: " . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($delete_stmt, "i", $id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        header('location:index.php?success=Xóa nhà sản xuất thành công');
    } else {
        throw new Exception("Lỗi thực thi truy vấn: " . mysqli_stmt_error($delete_stmt));
    }
} catch (Exception $e) {
    header('location:index.php?error=' . urlencode($e->getMessage()));
}

mysqli_close($connect);
