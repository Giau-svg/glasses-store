<?php
require_once '../../config/database.php';
require_once '../../admin/auth/check_admin.php';

// Kiểm tra nếu form đã được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $category_name = trim($_POST["category_name"]);
    $type = isset($_POST["type"]) ? trim($_POST["type"]) : null;
    $description = isset($_POST["description"]) ? trim($_POST["description"]) : null;
    
    // Kiểm tra dữ liệu
    $errors = [];
    
    // Kiểm tra xem tên danh mục đã tồn tại chưa
    $check_query = "SELECT COUNT(*) FROM categories WHERE category_name = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $category_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        $errors[] = "Tên danh mục đã tồn tại. Vui lòng chọn tên khác.";
    }
    
    // Nếu không có lỗi, thêm danh mục vào cơ sở dữ liệu
    if (empty($errors)) {
        // Kiểm tra xem bảng categories có cột type không
        $result = $conn->query("SHOW COLUMNS FROM categories LIKE 'type'");
        $has_type_column = $result->num_rows > 0;
        
        // Xây dựng câu lệnh SQL tùy thuộc vào cấu trúc bảng
        if ($has_type_column) {
            $sql = "INSERT INTO categories (category_name, type, description) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $category_name, $type, $description);
        } else {
            $sql = "INSERT INTO categories (category_name, description) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $category_name, $description);
        }
        
        if ($stmt->execute()) {
            // Chuyển hướng về trang danh sách với thông báo thành công
            header("Location: index.php?success=Đã thêm danh mục thành công!");
            exit;
        } else {
            $errors[] = "Lỗi: " . $conn->error;
        }
        
        $stmt->close();
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
