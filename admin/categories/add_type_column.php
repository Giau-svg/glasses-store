<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Thêm cột type vào bảng Categories</h1>";

// Kết nối đến cơ sở dữ liệu
$connect = mysqli_connect('localhost', 'root', '', 'pure');

if (!$connect) {
    die("<p style='color:red;'>Không thể kết nối đến MySQL: " . mysqli_connect_error() . "</p>");
}

// Kiểm tra xem cột type có tồn tại trong bảng hay không
$result = mysqli_query($connect, "SHOW COLUMNS FROM categories LIKE 'type'");
$has_type_column = mysqli_num_rows($result) > 0;

if (!$has_type_column) {
    // Thêm cột type vào bảng
    $sql = "ALTER TABLE categories ADD COLUMN type VARCHAR(50) NULL AFTER name";
    
    if (mysqli_query($connect, $sql)) {
        echo "<p style='color:green;'>Đã thêm cột 'type' vào bảng categories thành công!</p>";
        
        // Cập nhật dữ liệu cho cột type dựa trên tên danh mục
        $update_data = [
            ['Kính mát', 'sunglasses'],
            ['Kính cận', 'men'],
            ['Kính thời trang', 'sunglasses']
        ];
        
        foreach ($update_data as $item) {
            $name = mysqli_real_escape_string($connect, $item[0]);
            $type = mysqli_real_escape_string($connect, $item[1]);
            
            $update_sql = "UPDATE categories SET type = '$type' WHERE name = '$name'";
            if (mysqli_query($connect, $update_sql)) {
                echo "<p>Đã cập nhật loại cho danh mục '$name' thành '$type'</p>";
            } else {
                echo "<p style='color:orange;'>Lỗi cập nhật dữ liệu: " . mysqli_error($connect) . "</p>";
            }
        }
    } else {
        echo "<p style='color:red;'>Lỗi khi thêm cột 'type': " . mysqli_error($connect) . "</p>";
    }
} else {
    echo "<p style='color:green;'>Cột 'type' đã tồn tại trong bảng categories.</p>";
}

mysqli_close($connect);

echo "<p><a href='index.php'>Quay lại trang quản lý danh mục</a></p>";
?> 