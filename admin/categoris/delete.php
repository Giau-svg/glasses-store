<?php
require '../check_admin_login.php';
require '../root.php';

$id = $_GET['id'];

if(empty($id)) {
    header('location:index.php?error=Phải có mã danh mục để xóa');
    exit;
}

// Check if category is used in products
$check_sql = "SELECT * FROM products WHERE category_id = '$id'";
$check_result = mysqli_query($connect, $check_sql);

if(mysqli_num_rows($check_result) > 0) {
    header('location:index.php?error=Không thể xóa danh mục này vì đang có sản phẩm sử dụng');
    exit;
}

// Get category image to delete file
// First check which column exists in the table
$check_id_column = "SHOW COLUMNS FROM categories LIKE 'id'";
$id_column_exists = mysqli_query($connect, $check_id_column);

if(mysqli_num_rows($id_column_exists) > 0) {
    // Using id column
    $get_image_sql = "SELECT image FROM categories WHERE id = '$id'";
    $image_result = mysqli_query($connect, $get_image_sql);
    $category = mysqli_fetch_assoc($image_result);
    
    $sql = "DELETE FROM categories WHERE id = '$id'";
} else {
    // Using category_id column
    $get_image_sql = "SELECT image FROM categories WHERE category_id = '$id'";
    $image_result = mysqli_query($connect, $get_image_sql);
    $category = mysqli_fetch_assoc($image_result);
    
    $sql = "DELETE FROM categories WHERE category_id = '$id'";
}

$query = mysqli_query($connect, $sql);

if ($query) {
    // Delete image file if exists
    if (!empty($category['image'])) {
        $image_path = 'server/uploads/' . $category['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    header('location:index.php?success=Xóa thành công');
} else {
    header('location:index.php?error=' . mysqli_error($connect));
}
mysqli_close($connect);