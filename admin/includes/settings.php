<?php
// File này chứa các hàm để lấy và quản lý cài đặt trang web

// Hàm để lấy một cài đặt cụ thể theo tên
function get_setting($setting_name, $default_value = '') {
    global $connect;
    
    $setting_name = mysqli_real_escape_string($connect, $setting_name);
    $query = "SELECT setting_value FROM site_settings WHERE setting_name = '$setting_name' LIMIT 1";
    $result = mysqli_query($connect, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'];
    }
    
    return $default_value;
}

// Hàm để lấy tất cả cài đặt
function get_all_settings() {
    global $connect;
    
    $settings = array();
    $query = "SELECT setting_name, setting_value FROM site_settings";
    $result = mysqli_query($connect, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
    }
    
    return $settings;
}

// Hàm để cập nhật một cài đặt
function update_setting($setting_name, $setting_value) {
    global $connect;
    
    $setting_name = mysqli_real_escape_string($connect, $setting_name);
    $setting_value = mysqli_real_escape_string($connect, $setting_value);
    
    $check_query = "SELECT * FROM site_settings WHERE setting_name = '$setting_name'";
    $check_result = mysqli_query($connect, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $query = "UPDATE site_settings SET setting_value = '$setting_value' WHERE setting_name = '$setting_name'";
    } else {
        $query = "INSERT INTO site_settings (setting_name, setting_value) VALUES ('$setting_name', '$setting_value')";
    }
    
    return mysqli_query($connect, $query);
}

// Cài đặt mặc định
$default_settings = array(
    'site_name' => 'EYEGLASSES',
    'site_email' => 'admin@opticvision.com',
    'site_phone' => '1900 123 456',
    'site_address' => 'Số 123 Đường ABC, Quận 1, TP.HCM',
    'site_description' => 'Cửa hàng kính mắt chuyên nghiệp',
    'footer_text' => 'Copyright &copy; EYEGLASSES ' . date('Y'),
    'maintenance_mode' => '0'
);

// Đảm bảo bảng site_settings tồn tại
$sql = "CREATE TABLE IF NOT EXISTS `site_settings` (
    `setting_id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_name` varchar(50) NOT NULL,
    `setting_value` text NOT NULL,
    `setting_group` varchar(50) DEFAULT 'general',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`setting_id`),
    UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($connect, $sql);

// Thêm các cài đặt mặc định nếu chưa tồn tại
foreach ($default_settings as $name => $value) {
    $check_query = "SELECT * FROM site_settings WHERE setting_name = '$name'";
    $check_result = mysqli_query($connect, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $insert_query = "INSERT INTO site_settings (setting_name, setting_value) VALUES ('$name', '$value')";
        mysqli_query($connect, $insert_query);
    }
}
?> 