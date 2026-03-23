<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION['level']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location:/Pure/admin/index.php?error=Vui lòng đăng nhập để tiếp tục');
    exit();
}

// Kết nối đến cơ sở dữ liệu
$connect = mysqli_connect('localhost', 'root', '', 'eyeglasses_shop');
if (!$connect) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

if(empty($_GET['id'])) {
    header('location:index.php?error=Phải có mã danh mục để xóa');
    exit;
}


$id = $_GET['id'];

// Kiểm tra cột id trong bảng categories
$sql_check = "SHOW COLUMNS FROM categories LIKE 'category_id'"; 
$result_check = mysqli_query($connect, $sql_check);
if (mysqli_num_rows($result_check) > 0) {
    // Nếu tồn tại cột category_id
    $id_field = 'category_id';
} else {
    // Nếu không tồn tại cột category_id, dùng id
    $id_field = 'id';
}

// Check if category is used in products
$check_sql = "SELECT * FROM products WHERE category_id = '$id'";
$check_result = mysqli_query($connect, $check_sql);

if(mysqli_num_rows($check_result) > 0) {
    header('location:index.php?error=Không thể xóa danh mục này vì đang có sản phẩm sử dụng');
    exit;
}

// Lấy thông tin hình ảnh trước khi xóa
$image_sql = "SELECT image FROM categories WHERE " . $id_field . " = '$id'";
$image_result = mysqli_query($connect, $image_sql);
$category = mysqli_fetch_assoc($image_result);
$image = isset($category['image']) ? $category['image'] : '';

// Thực hiện xóa danh mục
$sql = "DELETE FROM categories WHERE " . $id_field . " = '$id'";
$query = mysqli_query($connect, $sql);

if ($query) {
    // Xóa file hình ảnh nếu có
    if (!empty($image) && file_exists('../' . $image) && $image != 'uploads/categories/no-image.png') {
        @unlink('../' . $image);
    }
    
    header('location:index.php?success=Xóa thành công');
} else {
    header('location:index.php?error=' . mysqli_error($connect));
}

mysqli_close($connect);