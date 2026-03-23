<?php
require '../check_admin_login.php';
require '../root.php';

// Lấy dữ liệu từ form
$category_id = $_POST['category_id'];
$category_name = addslashes($_POST['category_name']);
$type = addslashes($_POST['type'] ?? '');
$description = addslashes($_POST['description'] ?? '');
$image_old = $_POST['image_old'] ?? '';

// Validation cơ bản
if(empty($category_id)) {
    header('location:index.php?error=Phải có mã danh mục để sửa');
    exit;
}

if(empty($category_name)) {
    header("location:form_update.php?id=$category_id&error=Phải điền tên danh mục");
    exit;
}

// Kiểm tra tên trùng lặp
$check_sql = "SELECT * FROM categories WHERE category_name = '$category_name' AND category_id != '$category_id'";
$check_result = mysqli_query($connect, $check_sql);
if(mysqli_num_rows($check_result) > 0) {
    header("location:form_update.php?id=$category_id&error=Tên danh mục đã tồn tại");
    exit;
}

// Kiểm tra xem cột image có tồn tại trong bảng hay không
$check_image_column = mysqli_query($connect, "SHOW COLUMNS FROM categories LIKE 'image'");
$has_image_column = mysqli_num_rows($check_image_column) > 0;

// Kiểm tra xem cột type có tồn tại trong bảng hay không
$check_type_column = mysqli_query($connect, "SHOW COLUMNS FROM categories LIKE 'type'");
$has_type_column = mysqli_num_rows($check_type_column) > 0;

// Xử lý upload hình ảnh
$image = $image_old; // Mặc định giữ ảnh cũ

// Nếu có file được tải lên và cột image tồn tại
if($has_image_column && isset($_FILES['image_new']) && $_FILES['image_new']['size'] > 0) {
    $image_name = time() . '_' . $_FILES['image_new']['name'];
    $target_dir = "../../public/uploads/categories/";
    
    // Tạo thư mục nếu chưa tồn tại
    if(!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . $image_name;
    
    // Kiểm tra và upload hình ảnh
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['image_new']['type'];
    
    if(!in_array($file_type, $allowed_types)) {
        header("location:form_update.php?id=$category_id&error=Loại file không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF, WEBP");
        exit;
    }
    
    if(move_uploaded_file($_FILES['image_new']['tmp_name'], $target_file)) {
        $image = 'public/uploads/categories/' . $image_name;
        
        // Xóa ảnh cũ nếu tồn tại và không phải là mặc định
        if(!empty($image_old) && file_exists("../../" . $image_old) && strpos($image_old, "no-image") === false) {
            @unlink("../../" . $image_old);
        }
    } else {
        header("location:form_update.php?id=$category_id&error=Không thể upload hình ảnh");
        exit;
    }
}

// Tạo câu lệnh SQL tùy theo cấu trúc bảng
$sql = "UPDATE categories SET category_name = '$category_name'";

// Thêm cột type nếu có
if($has_type_column) {
    $sql .= ", type = '$type'";
}

// Luôn thêm description vì nó là cột cơ bản
$sql .= ", description = '$description'";

// Thêm cột image nếu có và đã có ảnh mới
if($has_image_column && $image != $image_old) {
    $sql .= ", image = '$image'";
}

$sql .= ", updated_at = NOW() WHERE category_id = '$category_id'";

// Thực hiện câu lệnh
$query = mysqli_query($connect, $sql);

if($query) {
    header('location:index.php?success=Cập nhật thành công');
} else {
    // Nếu cập nhật DB thất bại và đã upload ảnh mới, xóa ảnh mới
    if($image != $image_old && file_exists("../../" . $image)) {
        @unlink("../../" . $image);
    }
    
    header("location:form_update.php?id=$category_id&error=" . urlencode(mysqli_error($connect)));
}

mysqli_close($connect);


