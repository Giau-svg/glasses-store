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

// Kiểm tra cấu trúc bảng categories
$sql_check = "SHOW COLUMNS FROM categories LIKE 'category_id'";
$result_check = mysqli_query($connect, $sql_check);
$has_category_id = mysqli_num_rows($result_check) > 0;
$id_field = $has_category_id ? 'category_id' : 'id';

// Kiểm tra nếu form đã được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $category_name = trim($_POST["category_name"]);
    $type = isset($_POST["type"]) ? trim($_POST["type"]) : '';
    $description = isset($_POST["description"]) ? trim($_POST["description"]) : '';
    
    // Kiểm tra dữ liệu
    $errors = [];
    
    if (empty($category_name)) {
        $errors[] = "Tên danh mục không được để trống";
    }
    
    // Kiểm tra xem tên danh mục đã tồn tại chưa
    $category_name_escaped = mysqli_real_escape_string($connect, $category_name);
    $check_query = "SELECT COUNT(*) as count FROM categories WHERE category_name = '$category_name_escaped'";
    $result = mysqli_query($connect, $check_query);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        $errors[] = "Tên danh mục đã tồn tại. Vui lòng chọn tên khác.";
    }
    
    // Xử lý upload hình ảnh
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Kiểm tra kiểu file
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Loại file không hợp lệ. Chỉ chấp nhận các file ảnh JPG, PNG, GIF, WEBP.";
        }
        
        // Kiểm tra kích thước file
        if ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Kích thước file quá lớn. Kích thước tối đa là 5MB.";
        }
        
        if (empty($errors)) {
            // Tạo tên file duy nhất
            $timestamp = time();
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = $timestamp . '_' . uniqid() . '.' . $file_extension;
            
            // Đảm bảo thư mục uploads tồn tại
            $base_upload_dir = '../uploads/';
            if (!is_dir($base_upload_dir)) {
                if (!mkdir($base_upload_dir, 0777, true)) {
                    $errors[] = "Không thể tạo thư mục uploads. Vui lòng kiểm tra quyền thư mục.";
                }
            }
            
            if (empty($errors)) {
                // Thư mục lưu trữ cho danh mục
                $upload_dir = $base_upload_dir . 'categories/';
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0777, true)) {
                        $errors[] = "Không thể tạo thư mục uploads/categories. Vui lòng kiểm tra quyền thư mục.";
                    }
                }
                
                if (empty($errors)) {
                    $upload_path = $upload_dir . $file_name;
                    
                    // Upload file
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $upload_error = error_get_last();
                        $error_message = isset($upload_error['message']) ? $upload_error['message'] : "Không rõ nguyên nhân";
                        $errors[] = "Có lỗi xảy ra khi upload file: " . $error_message;
                    } else {
                        $image_path = 'uploads/categories/' . $file_name;
                    }
                }
            }
        }
    }
    
    // Nếu không có lỗi, thêm danh mục vào cơ sở dữ liệu
    if (empty($errors)) {
        $type_escaped = mysqli_real_escape_string($connect, $type);
        $description_escaped = mysqli_real_escape_string($connect, $description);
        $image_path_escaped = mysqli_real_escape_string($connect, $image_path);
        
        $created_at = date("Y-m-d H:i:s");
        
        // Kiểm tra xem bảng categories có cột image không
        $column_check = mysqli_query($connect, "SHOW COLUMNS FROM categories LIKE 'image'");
        $has_image_column = mysqli_num_rows($column_check) > 0;
        
        if (!$has_image_column) {
            // Thêm cột image nếu chưa có
            $alter_sql = "ALTER TABLE categories ADD COLUMN image VARCHAR(255) NULL AFTER description";
            mysqli_query($connect, $alter_sql);
        }
        
        // Kiểm tra xem bảng categories có cột type không
        $column_check = mysqli_query($connect, "SHOW COLUMNS FROM categories LIKE 'type'");
        $has_type_column = mysqli_num_rows($column_check) > 0;
        
        if (!$has_type_column) {
            // Thêm cột type nếu chưa có
            $alter_sql = "ALTER TABLE categories ADD COLUMN type VARCHAR(50) NULL AFTER category_name";
            mysqli_query($connect, $alter_sql);
        }
        
        $sql = "INSERT INTO categories (category_name";
        if ($has_type_column) $sql .= ", type";
        $sql .= ", description";
        if ($has_image_column) $sql .= ", image";
        $sql .= ", created_at) VALUES ('$category_name_escaped'";
        if ($has_type_column) $sql .= ", '$type_escaped'";
        $sql .= ", '$description_escaped'";
        if ($has_image_column) $sql .= ", '$image_path_escaped'";
        $sql .= ", '$created_at')";
        
        if (mysqli_query($connect, $sql)) {
            // Chuyển hướng về trang danh sách với thông báo thành công
            header("Location: index.php?success=Đã thêm danh mục thành công!");
            exit;
        } else {
            $errors[] = "Lỗi: " . mysqli_error($connect);
            
            // Xóa ảnh đã upload nếu có lỗi
            if (!empty($image_path) && file_exists($upload_path)) {
                unlink($upload_path);
            }
        }
    }
    
    // Nếu có lỗi, chuyển hướng về form với thông báo lỗi
    if (!empty($errors)) {
        $error_string = implode("<br>", $errors);
        header("Location: form_insert.php?error=" . urlencode($error_string));
        exit;
    }
}

// Nếu không phải là POST request, chuyển hướng về trang form
header("Location: form_insert.php");
exit;
?>
