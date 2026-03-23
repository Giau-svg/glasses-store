<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Kiểm tra kết nối cơ sở dữ liệu</h1>";

// Thông tin kết nối
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'pure';

// Kiểm tra kết nối trực tiếp không cần chọn DB
try {
    echo "<h2>1. Kết nối MySQL</h2>";
    $connect = mysqli_connect($host, $user, $pass);
    if (!$connect) {
        echo "<p style='color:red;'>Không thể kết nối đến MySQL: " . mysqli_connect_error() . "</p>";
    } else {
        echo "<p style='color:green;'>Kết nối MySQL thành công!</p>";
        
        // Kiểm tra CSDL có tồn tại không
        echo "<h2>2. Kiểm tra CSDL 'pure'</h2>";
        $result = mysqli_query($connect, "SHOW DATABASES LIKE 'pure'");
        if (mysqli_num_rows($result) > 0) {
            echo "<p style='color:green;'>Database 'pure' đã tồn tại.</p>";
            
            // Chọn CSDL
            if (mysqli_select_db($connect, $db)) {
                echo "<p style='color:green;'>Đã chọn database 'pure'.</p>";
                
                // Kiểm tra bảng categories
                echo "<h2>3. Kiểm tra bảng 'categories'</h2>";
                $result = mysqli_query($connect, "SHOW TABLES LIKE 'categories'");
                if (mysqli_num_rows($result) > 0) {
                    echo "<p style='color:green;'>Bảng 'categories' đã tồn tại.</p>";
                    
                    // Hiển thị cấu trúc bảng
                    echo "<h2>4. Cấu trúc bảng 'categories'</h2>";
                    $result = mysqli_query($connect, "DESCRIBE categories");
                    if (!$result) {
                        echo "<p style='color:red;'>Không thể lấy cấu trúc bảng: " . mysqli_error($connect) . "</p>";
                    } else {
                        echo "<table border='1' cellpadding='5'>";
                        echo "<tr><th>Tên cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Khóa</th><th>Mặc định</th><th>Extra</th></tr>";
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['Field'] . "</td>";
                            echo "<td>" . $row['Type'] . "</td>";
                            echo "<td>" . $row['Null'] . "</td>";
                            echo "<td>" . $row['Key'] . "</td>";
                            echo "<td>" . $row['Default'] . "</td>";
                            echo "<td>" . $row['Extra'] . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        
                        // Hiển thị dữ liệu trong bảng
                        echo "<h2>5. Dữ liệu trong bảng 'categories'</h2>";
                        $result = mysqli_query($connect, "SELECT * FROM categories LIMIT 10");
                        if (!$result) {
                            echo "<p style='color:red;'>Không thể lấy dữ liệu từ bảng: " . mysqli_error($connect) . "</p>";
                        } else {
                            if (mysqli_num_rows($result) > 0) {
                                echo "<table border='1' cellpadding='5'>";
                                
                                // Lấy thông tin cột
                                $fields = mysqli_fetch_fields($result);
                                echo "<tr>";
                                foreach ($fields as $field) {
                                    echo "<th>" . $field->name . "</th>";
                                }
                                echo "</tr>";
                                
                                // Hiển thị dữ liệu
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    foreach ($row as $key => $value) {
                                        echo "<td>" . htmlspecialchars($value) . "</td>";
                                    }
                                    echo "</tr>";
                                }
                                echo "</table>";
                            } else {
                                echo "<p>Không có dữ liệu trong bảng 'categories'.</p>";
                            }
                        }
                    }
                } else {
                    echo "<p style='color:red;'>Bảng 'categories' không tồn tại.</p>";
                }
            } else {
                echo "<p style='color:red;'>Không thể chọn database 'pure': " . mysqli_error($connect) . "</p>";
            }
        } else {
            echo "<p style='color:red;'>Database 'pure' không tồn tại.</p>";
        }
        
        mysqli_close($connect);
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?> 