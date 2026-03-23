<?php
// Kết nối CSDL
require './admin/root.php';

// Kiểm tra xem cột type đã tồn tại trong bảng categories chưa
$check_column = mysqli_query($connect, "SHOW COLUMNS FROM categories LIKE 'type'");
if (mysqli_num_rows($check_column) == 0) {
    // Thêm cột type nếu chưa tồn tại
    mysqli_query($connect, "ALTER TABLE categories ADD COLUMN type VARCHAR(50) NULL AFTER description");
    echo "Đã thêm cột 'type' vào bảng categories.<br>";
}

// Danh sách các danh mục và loại tương ứng
$category_types = [
    'Kính Nam' => 'men',
    'Kính Nữ' => 'women',
    'Kính Mát' => 'sunglasses',
    'Kính Trẻ Em' => 'children',
    'Tròng Kính' => 'lens',
    'Phụ Kiện' => 'accessories'
];

// Cập nhật type cho các danh mục
$updated = 0;
foreach ($category_types as $category_name => $type) {
    $sql = "UPDATE categories SET type = '$type' WHERE category_name = '$category_name'";
    if (mysqli_query($connect, $sql) && mysqli_affected_rows($connect) > 0) {
        echo "Đã cập nhật type '$type' cho danh mục '$category_name'.<br>";
        $updated++;
    }
}

// Hiển thị tổng kết
echo "<hr>";
echo "Đã cập nhật type cho $updated danh mục.<br>";
echo "<a href='admin/categories/index.php'>Quay lại trang quản lý danh mục</a>";

// Đóng kết nối
mysqli_close($connect);
?> 