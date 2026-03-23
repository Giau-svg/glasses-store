<?php
// Kết nối CSDL
require './admin/root.php';

// Danh sách các danh mục từ menu ngang
$categories = [
    ['name' => 'Kính Nam', 'type' => 'men', 'description' => 'Các mẫu kính dành cho nam'],
    ['name' => 'Kính Nữ', 'type' => 'women', 'description' => 'Các mẫu kính dành cho nữ'],
    ['name' => 'Kính Mát', 'type' => 'sunglasses', 'description' => 'Các mẫu kính mát chống nắng'],
    ['name' => 'Kính Trẻ Em', 'type' => 'children', 'description' => 'Các mẫu kính dành cho trẻ em'],
    ['name' => 'Tròng Kính', 'type' => 'lens', 'description' => 'Các loại tròng kính thay thế'],
    ['name' => 'Phụ Kiện', 'type' => 'accessories', 'description' => 'Phụ kiện dành cho kính mắt']
];

// Thêm các danh mục vào CSDL
$added = 0;
$existed = 0;

foreach($categories as $category) {
    // Kiểm tra xem danh mục đã tồn tại chưa
    $check_sql = "SELECT * FROM categories WHERE category_name = '" . $category['name'] . "'";
    $check_result = mysqli_query($connect, $check_sql);
    
    if(mysqli_num_rows($check_result) > 0) {
        // Danh mục đã tồn tại
        echo "Danh mục '" . $category['name'] . "' đã tồn tại.<br>";
        $existed++;
        
        // Cập nhật type nếu chưa có
        $category_data = mysqli_fetch_assoc($check_result);
        $category_id = $category_data['category_id'];
        
        // Cập nhật trường type trong bảng categories (nếu có)
        $update_sql = "UPDATE categories SET description = '" . $category['description'] . "' WHERE category_id = $category_id";
        mysqli_query($connect, $update_sql);
    } else {
        // Thêm danh mục mới
        $insert_sql = "INSERT INTO categories (category_name, description, created_at, updated_at) 
                        VALUES ('" . $category['name'] . "', '" . $category['description'] . "', NOW(), NOW())";
        
        if(mysqli_query($connect, $insert_sql)) {
            $category_id = mysqli_insert_id($connect);
            echo "Đã thêm danh mục: '" . $category['name'] . "' (ID: $category_id)<br>";
            $added++;
        } else {
            echo "Lỗi khi thêm danh mục '" . $category['name'] . "': " . mysqli_error($connect) . "<br>";
        }
    }
}

// Hiển thị tổng kết
echo "<hr>";
echo "Tổng kết: Đã thêm $added danh mục mới, $existed danh mục đã tồn tại.<br>";
echo "<a href='admin/categories/index.php'>Quay lại trang quản lý danh mục</a>";

// Đóng kết nối
mysqli_close($connect);
?> 