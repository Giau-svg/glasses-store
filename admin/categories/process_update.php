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

// Thiết lập UTF-8
mysqli_set_charset($connect, 'utf8');

// Lấy dữ liệu từ form
$id = $_POST['id'];
$id_field = isset($_POST['id_field']) ? $_POST['id_field'] : 'category_id'; // ID có thể là category_id hoặc id
$name = trim($_POST['name']);
$type = isset($_POST['type']) ? trim($_POST['type']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$current_image = isset($_POST['current_image']) ? $_POST['current_image'] : '';

// Validation cơ bản
if(empty($id)) {
    header('location:index.php?error=Phải có mã danh mục để sửa');
    exit;
}

if(empty($name)) {
    header("location:form_update.php?id=$id&error=Phải điền tên danh mục");
    exit;
}

// Kiểm tra tên trùng lặp
$name_escaped = mysqli_real_escape_string($connect, $name);
$check_sql = "SELECT * FROM categories WHERE category_name = '$name_escaped' AND $id_field != '$id'";
$check_result = mysqli_query($connect, $check_sql);
if(mysqli_num_rows($check_result) > 0) {
    header("location:form_update.php?id=$id&error=Tên danh mục đã tồn tại");
    exit;
}

// Xử lý upload hình ảnh mới
$image_path = $current_image; // Mặc định giữ hình ảnh cũ

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Kiểm tra kiểu file
    if (!in_array($_FILES['image']['type'], $allowed_types)) {
        header("location:form_update.php?id=$id&error=Loại file không hợp lệ. Chỉ chấp nhận các file ảnh JPG, PNG, GIF, WEBP.");
        exit;
    }
    
    // Kiểm tra kích thước file
    if ($_FILES['image']['size'] > $max_size) {
        header("location:form_update.php?id=$id&error=Kích thước file quá lớn. Kích thước tối đa là 5MB.");
        exit;
    }
    
    // Tạo tên file duy nhất
    $timestamp = time();
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = $timestamp . '_' . uniqid() . '.' . $file_extension;
    
    // Đảm bảo thư mục uploads tồn tại
    $base_upload_dir = '../uploads/';
    if (!is_dir($base_upload_dir)) {
        if (!mkdir($base_upload_dir, 0777, true)) {
            header("location:form_update.php?id=$id&error=Không thể tạo thư mục uploads. Vui lòng kiểm tra quyền thư mục.");
            exit;
        }
    }
    
    // Thư mục lưu trữ cho danh mục
    $upload_dir = $base_upload_dir . 'categories/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            header("location:form_update.php?id=$id&error=Không thể tạo thư mục uploads/categories. Vui lòng kiểm tra quyền thư mục.");
            exit;
        }
    }
    
    $upload_path = $upload_dir . $file_name;
    
    // Upload file
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $upload_error = error_get_last();
        $error_message = isset($upload_error['message']) ? $upload_error['message'] : "Không rõ nguyên nhân";
        header("location:form_update.php?id=$id&error=Có lỗi xảy ra khi upload file: " . urlencode($error_message));
        exit;
    }
    
    $image_path = 'uploads/categories/' . $file_name;
        
    // Xóa file hình ảnh cũ nếu có
    if (!empty($current_image) && file_exists('../' . $current_image) && $current_image != 'uploads/categories/no-image.png') {
        @unlink('../' . $current_image);
    }
}

// Kiểm tra xem bảng categories có cột image không
$check_image_column = mysqli_query($connect, "SHOW COLUMNS FROM categories LIKE 'image'");
$has_image_column = mysqli_num_rows($check_image_column) > 0;

// Nếu chưa có cột image, thêm cột này vào bảng
if (!$has_image_column) {
    $alter_sql = "ALTER TABLE categories ADD COLUMN image VARCHAR(255) NULL AFTER description";
    mysqli_query($connect, $alter_sql);
}

// Kiểm tra xem cột type có tồn tại trong bảng hay không
$check_type_column = mysqli_query($connect, "SHOW COLUMNS FROM categories LIKE 'type'");
$has_type_column = mysqli_num_rows($check_type_column) > 0;

// Tạo câu lệnh SQL tùy theo cấu trúc bảng
$type_escaped = mysqli_real_escape_string($connect, $type);
$description_escaped = mysqli_real_escape_string($connect, $description);
$image_path_escaped = mysqli_real_escape_string($connect, $image_path);

$sql = "UPDATE categories SET category_name = '$name_escaped'";

// Thêm cột type nếu có
if($has_type_column) {
    $sql .= ", type = '$type_escaped'";
}

// Luôn thêm description vì nó là cột cơ bản
$sql .= ", description = '$description_escaped'";

// Thêm cột image nếu có
if($has_image_column) {
    $sql .= ", image = '$image_path_escaped'";
}

$sql .= ", updated_at = NOW() WHERE $id_field = '$id'";

// Thực hiện câu lệnh
$query = mysqli_query($connect, $sql);

if($query) {
    header('location:index.php?success=Cập nhật thành công');
} else {
    // Nếu cập nhật DB thất bại và đã upload ảnh mới, xóa ảnh mới
    if($image_path != $current_image && file_exists('../' . $image_path)) {
        @unlink('../' . $image_path);
    }
    
    header("location:form_update.php?id=$id&error=" . urlencode(mysqli_error($connect)));
}

mysqli_close($connect);


