<?php
// Database connection
require_once '../../admin/config/connect.php';

// Capture output instead of sending it to browser
ob_start();

// Check if settings table exists
$check_table = "SHOW TABLES LIKE 'settings'";
$result = mysqli_query($connect, $check_table);

if (mysqli_num_rows($result) == 0) {
    // The table doesn't exist, so create it
    $create_table = "CREATE TABLE settings (
        id INT(11) NOT NULL AUTO_INCREMENT,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT NULL,
        setting_group VARCHAR(50) DEFAULT 'general',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY (setting_key)
    )";
    
    $created = mysqli_query($connect, $create_table);
    
    // Add default company information
    if ($created) {
        $settings = [
            ['company_name', 'EYEGLASSES', 'company'],
            ['company_address', 'Hệ thống kính mắt chất lượng cao', 'company'],
            ['company_phone', '1900 1234', 'company'],
            ['company_email', 'support@opticvision.com', 'company'],
            ['company_logo', '', 'company'],
            ['shipping_fee', '30000', 'shipping'],
            ['currency_symbol', 'đ', 'general'],
            ['currency_format', '0,0', 'general']
        ];
        
        foreach ($settings as $setting) {
            $insert_sql = "INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($connect, $insert_sql);
            mysqli_stmt_bind_param($stmt, "sss", $setting[0], $setting[1], $setting[2]);
            mysqli_stmt_execute($stmt);
        }
    }
} else {
    // Check if company settings exist
    $check_settings = "SELECT COUNT(*) FROM settings WHERE setting_group = 'company'";
    $result_check = mysqli_query($connect, $check_settings);
    $row = mysqli_fetch_array($result_check);
    
    if ($row[0] < 4) {
        // Add company settings if they don't exist
        $company_settings = [
            ['company_name', 'EYEGLASSES', 'company'],
            ['company_address', 'Hệ thống kính mắt chất lượng cao', 'company'],
            ['company_phone', '1900 1234', 'company'],
            ['company_email', 'support@opticvision.com', 'company']
        ];
        
        foreach ($company_settings as $setting) {
            $check_sql = "SELECT id FROM settings WHERE setting_key = ?";
            $check_stmt = mysqli_prepare($connect, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "s", $setting[0]);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) == 0) {
                $insert_sql = "INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($connect, $insert_sql);
                mysqli_stmt_bind_param($stmt, "sss", $setting[0], $setting[1], $setting[2]);
                mysqli_stmt_execute($stmt);
            }
        }
    }
}

// Clear the output buffer
ob_end_clean();
?> 