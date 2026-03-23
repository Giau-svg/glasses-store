<?php

require '../root.php';
require '../check_admin_login.php'; 

// Kiểm tra nếu có ID sản phẩm
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    // Kiểm tra xem sản phẩm có tồn tại không
    $sql_check = "SELECT * FROM products WHERE product_id = ?";
    $stmt_check = mysqli_prepare($connect, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $product_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        $product = mysqli_fetch_assoc($result_check);
        
        // Xóa ảnh nếu có
        if (!empty($product['image']) && file_exists(__DIR__ . "/uploads/" . $product['image'])) {
            unlink(__DIR__ . "/uploads/" . $product['image']);
        }
        
        // Xóa sản phẩm khỏi cơ sở dữ liệu
        $sql_delete = "DELETE FROM products WHERE product_id = ?";
        $stmt_delete = mysqli_prepare($connect, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $product_id);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            header('Location: index.php?success=Đã xóa sản phẩm thành công');
            exit;
        } else {
            header('Location: index.php?error=Lỗi khi xóa sản phẩm: ' . mysqli_error($connect));
            exit;
        }
    } else {
        header('Location: index.php?error=Không tìm thấy sản phẩm');
        exit;
    }
} else {
    header('Location: index.php?error=Không có ID sản phẩm');
    exit;
}