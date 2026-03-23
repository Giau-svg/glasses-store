<?php
require '../check_admin_login.php';
require '../root.php';

// Kiểm tra nếu form đã được gửi đi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $product_id = (int)$_POST['product_id'];
    $product_name = trim(mysqli_real_escape_string($connect, $_POST['product_name']));
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $description = isset($_POST['description']) ? trim(mysqli_real_escape_string($connect, $_POST['description'])) : '';
    $stock_quantity = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0;
    $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $manufacturer_id = isset($_POST['manufacturer_id']) && !empty($_POST['manufacturer_id']) ? (int)$_POST['manufacturer_id'] : null;
    $brand_id = isset($_POST['brand_id']) && !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    
    // Kiểm tra xem sản phẩm có tồn tại không
    $check_product = "SELECT * FROM products WHERE product_id = ?";
    $stmt_check = mysqli_prepare($connect, $check_product);
    mysqli_stmt_bind_param($stmt_check, "i", $product_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        header('Location: index.php?error=Không tìm thấy sản phẩm');
        exit;
    }
    
    $product = mysqli_fetch_assoc($result_check);
    $current_image = $product['image_path'];
    
    // Xử lý ảnh
    $image_path = $current_image; // Giữ nguyên ảnh cũ nếu không có thay đổi
    
    // Kiểm tra nếu người dùng muốn xóa ảnh
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == 1) {
        $image_path = null;
        // Xóa file ảnh cũ nếu tồn tại
        if (!empty($current_image) && file_exists("uploads/" . $current_image)) {
            unlink("uploads/" . $current_image);
        }
    }
    
    // Kiểm tra nếu có upload ảnh mới
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = __DIR__ . "/uploads/";
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                header('Location: form_update.php?id=' . $product_id . '&error=Không thể tạo thư mục uploads. Vui lòng kiểm tra quyền của thư mục.');
                exit;
            }
        }
        
        // Debug thông tin file
        $upload_error = "File Type: " . $_FILES['image']['type'] . ", Size: " . $_FILES['image']['size'];
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Kiểm tra loại file
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            header('Location: form_update.php?id=' . $product_id . '&error=Chỉ chấp nhận file ảnh có định dạng JPEG, PNG, GIF. ' . $upload_error);
            exit;
        }
        
        // Kiểm tra kích thước file
        if ($_FILES['image']['size'] > $max_size) {
            header('Location: form_update.php?id=' . $product_id . '&error=Kích thước file không được vượt quá 2MB');
            exit;
        }
        
        // Tạo tên file duy nhất
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        
        // Upload file mới
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Xóa file ảnh cũ nếu tồn tại
            if (!empty($current_image) && file_exists($target_dir . $current_image) && $current_image != $image) {
                unlink($target_dir . $current_image);
            }
            $image_path = $image;
        } else {
            $php_error = error_get_last();
            header('Location: form_update.php?id=' . $product_id . '&error=Có lỗi xảy ra khi upload file: ' . ($php_error ? $php_error['message'] : 'Unknown error'));
            exit;
        }
    }
    
    // Cập nhật sản phẩm trong CSDL
    $sql = "UPDATE products SET 
            product_name = ?, 
            price = ?, 
            description = ?, 
            stock_quantity = ?, 
            category_id = ?, 
            manufacturer_id = ?, 
            brand_id = ?,
            image_path = ?
            WHERE product_id = ?";
    
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "sdsiiiisi", $product_name, $price, $description, $stock_quantity, $category_id, $manufacturer_id, $brand_id, $image_path, $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Thành công
        header('Location: index.php?success=Đã cập nhật sản phẩm thành công');
        exit;
    } else {
        // Lỗi
        header('Location: form_update.php?id=' . $product_id . '&error=Lỗi: ' . mysqli_error($connect));
        exit;
    }
} else {
    // Nếu không phải là POST request, chuyển hướng về trang danh sách
    header('Location: index.php');
    exit;
}
?>
