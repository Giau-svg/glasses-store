<?php
// Connect to MySQL database
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'eyeglasses_shop';

// Use the correct socket path for XAMPP on macOS
$connect = mysqli_connect($host, $user, $password, $dbname, 3306, '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');

// Check connection
if (mysqli_connect_errno()) {
    die('Không thể kết nối đến cơ sở dữ liệu: ' . mysqli_connect_error());
}

// Đảm bảo kết nối sử dụng UTF-8
if(!mysqli_set_charset($connect, 'utf8mb4')) {
    die("ERROR: Could not set character set. " . mysqli_error($connect));
}

// Tạo bảng site_settings nếu chưa tồn tại
$sql_check_site_settings = "CREATE TABLE IF NOT EXISTS `site_settings` (
    `setting_id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_name` varchar(50) NOT NULL,
    `setting_value` text NOT NULL,
    `setting_group` varchar(50) DEFAULT 'general',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`setting_id`),
    UNIQUE KEY `setting_name` (`setting_name`)
)";
mysqli_query($connect, $sql_check_site_settings);

// Yêu cầu file helper
if (file_exists(__DIR__ . '/includes/helper.php')) {
    require_once __DIR__ . '/includes/helper.php';
}

if (!function_exists('currency_format')) {
    function currency_format($number, $suffix = 'đ') {
        if (!empty($number)) {
            return number_format($number, 0, ',', '.') . "{$suffix}";
        }
    }
}

// Tạo bảng users nếu chưa tồn tại
$sql_check_users = "CREATE TABLE IF NOT EXISTS `users` (
    `user_id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `email` varchar(100) NOT NULL,
    `full_name` varchar(100) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `role_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
)";
mysqli_query($connect, $sql_check_users);

// Tạo bảng activity_logs nếu chưa tồn tại
$sql_check_logs = "CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `activity` varchar(255) NOT NULL,
    `details` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
)";
mysqli_query($connect, $sql_check_logs);

// Tạo bảng orders nếu chưa tồn tại
$sql_check_orders = "CREATE TABLE IF NOT EXISTS `orders` (
    `order_id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
    `total_amount` decimal(10,2) NOT NULL,
    `order_status` enum('pending','confirmed','processing','shipping','shipped','delivered','cancelled') DEFAULT 'pending',
    `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
    `payment_method` varchar(50) DEFAULT NULL,
    `shipping_address` text DEFAULT NULL,
    `shipping_phone` varchar(20) DEFAULT NULL,
    `shipping_name` varchar(100) DEFAULT NULL,
    `shipping_email` varchar(100) DEFAULT NULL,
    `shipping_notes` text DEFAULT NULL,
    `sales_employee_id` int(11) DEFAULT NULL,
    `processed_date` timestamp NULL DEFAULT NULL,
    `cancelled_date` timestamp NULL DEFAULT NULL,
    `cancelled_reason` text DEFAULT NULL,
    `last_updated` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    `staff_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`order_id`),
    KEY `user_id` (`user_id`)
)";
mysqli_query($connect, $sql_check_orders);

// Tạo bảng order_history nếu chưa tồn tại
$sql_check_order_history = "CREATE TABLE IF NOT EXISTS `order_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `status` varchar(50) NOT NULL,
    `message` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `staff_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`)
)";
mysqli_query($connect, $sql_check_order_history);

// Tạo bảng order_details nếu chưa tồn tại
$sql_check_order_items = "CREATE TABLE IF NOT EXISTS `order_details` (
    `order_detail_id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL,
    `unit_price` decimal(10,2) NOT NULL,
    `subtotal` decimal(10,2) NOT NULL,
    PRIMARY KEY (`order_detail_id`),
    KEY `order_id` (`order_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
    CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT
)";
if (!mysqli_query($connect, $sql_check_order_items)) {
    // If table exists but foreign keys can't be added, drop and recreate without constraints
    $sql_drop_constraints = "ALTER TABLE `order_details` 
                            DROP FOREIGN KEY IF EXISTS `order_details_ibfk_1`,
                            DROP FOREIGN KEY IF EXISTS `order_details_ibfk_2`";
    mysqli_query($connect, $sql_drop_constraints);
    
    // Recreate basic table structure without constraints
    $sql_recreate_order_items = "CREATE TABLE IF NOT EXISTS `order_details` (
        `order_detail_id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `quantity` int(11) NOT NULL,
        `unit_price` decimal(10,2) NOT NULL,
        `subtotal` decimal(10,2) NOT NULL,
        PRIMARY KEY (`order_detail_id`),
        KEY `order_id` (`order_id`),
        KEY `product_id` (`product_id`)
    )";
    mysqli_query($connect, $sql_recreate_order_items);
}

// Tạo bảng customers nếu chưa tồn tại
$sql_check_customers = "CREATE TABLE IF NOT EXISTS `customers` (
    `customer_id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `address` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`customer_id`)
)";
mysqli_query($connect, $sql_check_customers);

// Tạo bảng categories nếu chưa tồn tại
$sql_check_categories = "CREATE TABLE IF NOT EXISTS `categories` (
    `category_id` int(11) NOT NULL AUTO_INCREMENT,
    `category_name` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`category_id`)
)";
mysqli_query($connect, $sql_check_categories);

// Tạo bảng manufacturers nếu chưa tồn tại
$sql_check_manufacturers = "CREATE TABLE IF NOT EXISTS `manufacturers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
)";
mysqli_query($connect, $sql_check_manufacturers);

// Kiểm tra nếu chưa có tài khoản admin, thêm tài khoản mặc định
$sql_check_admin = "SELECT * FROM users WHERE role_id = 1 LIMIT 1";
$result_admin = mysqli_query($connect, $sql_check_admin);

if (mysqli_num_rows($result_admin) == 0) {
    // Sử dụng hàm password_hash thay vì md5 để tạo mật khẩu bcrypt có thể verify
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql_insert_admin = "INSERT INTO users (username, email, password, full_name, role_id) 
                         VALUES ('admin', 'admin@opticvision.com', '$admin_password', 'Admin', 1)";
    mysqli_query($connect, $sql_insert_admin);
}

// Kiểm tra và cập nhật tài khoản admin nếu cần
$sql_check_admin_email = "SELECT * FROM users WHERE role_id = 1 AND email = 'admin@eyeglasses.com' LIMIT 1";
$result_admin_email = mysqli_query($connect, $sql_check_admin_email);

if (mysqli_num_rows($result_admin_email) == 1) {
    // Cập nhật email cho admin nếu chưa đúng
    $sql_update_admin = "UPDATE users SET email = 'admin@opticvision.com' WHERE role_id = 1 AND email = 'admin@eyeglasses.com'";
    mysqli_query($connect, $sql_update_admin);
}

// Kiểm tra nếu chưa có tài khoản sales, thêm tài khoản mặc định
$sql_check_sales = "SELECT * FROM users WHERE role_id = 3 LIMIT 1";
$result_sales = mysqli_query($connect, $sql_check_sales);

if (mysqli_num_rows($result_sales) == 0) {
    $sales_password = md5('sales123');
    $sql_insert_sales = "INSERT INTO users (username, email, password, full_name, role_id) VALUES ('sales', 'sales@opticvision.com', '$sales_password', 'Nhân viên bán hàng', 3)";
    mysqli_query($connect, $sql_insert_sales);
}

// Kiểm tra nếu chưa có tài khoản inventory, thêm tài khoản mặc định
$sql_check_inventory = "SELECT * FROM users WHERE role_id = 4 LIMIT 1";
$result_inventory = mysqli_query($connect, $sql_check_inventory);

if (mysqli_num_rows($result_inventory) == 0) {
    $inventory_password = md5('inventory123');
    $sql_insert_inventory = "INSERT INTO users (username, email, password, full_name, role_id) VALUES ('inventory', 'inventory@opticvision.com', '$inventory_password', 'Quản lý kho', 4)";
    mysqli_query($connect, $sql_insert_inventory);
}

// Thêm dữ liệu mẫu vào customers
$sql_check_sample_customer = "SELECT * FROM customers LIMIT 1";
$result_customer = mysqli_query($connect, $sql_check_sample_customer);

if (mysqli_num_rows($result_customer) == 0) {
    $sql_insert_customer = "INSERT INTO customers (name, email, phone, address) VALUES 
    ('Nguyễn Văn A', 'nguyenvana@gmail.com', '0901234567', '123 Đường Lê Lợi, Quận 1, TP.HCM'),
    ('Trần Thị B', 'tranthib@gmail.com', '0912345678', '456 Đường Nguyễn Huệ, Quận 1, TP.HCM'),
    ('Lê Văn C', 'levanc@gmail.com', '0823456789', '789 Đường 3/2, Quận 10, TP.HCM')";
    mysqli_query($connect, $sql_insert_customer);
}

// Thêm dữ liệu mẫu vào orders
$sql_check_sample_order = "SELECT * FROM orders LIMIT 1";
$result_order = mysqli_query($connect, $sql_check_sample_order);

if (mysqli_num_rows($result_order) == 0) {
    // Đảm bảo đã có bảng orders với cấu trúc đúng trước khi thêm dữ liệu
    $sql_check_status = "SHOW COLUMNS FROM orders LIKE 'order_status'";
    $result_status = mysqli_query($connect, $sql_check_status);
    $column_info = mysqli_fetch_assoc($result_status);
    error_log("Current order_status column type: " . print_r($column_info, true));
    
    // Sửa lại cấu trúc cột nếu cần
    $sql_fix_column = "ALTER TABLE orders MODIFY COLUMN order_status ENUM('pending','confirmed','processing','shipping','shipped','delivered','cancelled') DEFAULT 'pending'";
    mysqli_query($connect, $sql_fix_column);
    
    // Thêm dữ liệu từng đơn hàng riêng biệt để tránh lỗi
    // Đơn hàng 1 - pending
    $sql1 = "INSERT INTO orders (user_id, total_amount, order_status, shipping_notes, shipping_name, shipping_phone, shipping_email, shipping_address, payment_method, order_date) 
             VALUES (1, 2500000, 'pending', 'Giao hàng trong giờ hành chính', 'Nguyễn Văn A', '0901234567', 'nguyenvana@gmail.com', '123 Đường Lê Lợi, Quận 1, TP.HCM', 'COD', NOW())";
    
    if (!mysqli_query($connect, $sql1)) {
        error_log("ERROR: Could not insert order 1: " . mysqli_error($connect));
    }
    
    // Đơn hàng 2 - confirmed
    $sql2 = "INSERT INTO orders (user_id, total_amount, order_status, shipping_notes, shipping_name, shipping_phone, shipping_email, shipping_address, payment_method, order_date) 
             VALUES (2, 1800000, 'confirmed', 'Gọi trước khi giao', 'Trần Thị B', '0912345678', 'tranthib@gmail.com', '456 Đường Nguyễn Huệ, Quận 1, TP.HCM', 'Banking', DATE_SUB(NOW(), INTERVAL 1 DAY))";
    
    if (!mysqli_query($connect, $sql2)) {
        error_log("ERROR: Could not insert order 2: " . mysqli_error($connect));
    }
    
    // Đơn hàng 3 - shipping
    $sql3 = "INSERT INTO orders (user_id, total_amount, order_status, shipping_notes, shipping_name, shipping_phone, shipping_email, shipping_address, payment_method, order_date) 
             VALUES (3, 3200000, 'shipping', 'Để hàng tại quầy lễ tân', 'Lê Văn C', '0823456789', 'levanc@gmail.com', '789 Đường 3/2, Quận 10, TP.HCM', 'COD', DATE_SUB(NOW(), INTERVAL 2 DAY))";
    
    if (!mysqli_query($connect, $sql3)) {
        error_log("ERROR: Could not insert order 3: " . mysqli_error($connect));
    }
    
    // Đơn hàng 4 - delivered
    $sql4 = "INSERT INTO orders (user_id, total_amount, order_status, shipping_notes, shipping_name, shipping_phone, shipping_email, shipping_address, payment_method, order_date) 
             VALUES (1, 1500000, 'delivered', '', 'Nguyễn Văn A', '0901234567', 'nguyenvana@gmail.com', '123 Đường Lê Lợi, Quận 1, TP.HCM', 'Banking', DATE_SUB(NOW(), INTERVAL 5 DAY))";
    
    if (!mysqli_query($connect, $sql4)) {
        error_log("ERROR: Could not insert order 4: " . mysqli_error($connect));
    }
    
    // Đơn hàng 5 - cancelled
    $sql5 = "INSERT INTO orders (user_id, total_amount, order_status, shipping_notes, shipping_name, shipping_phone, shipping_email, shipping_address, payment_method, order_date) 
             VALUES (2, 950000, 'cancelled', 'Khách hàng đổi ý', 'Phạm Thị D', '0978123456', 'phamthid@gmail.com', '321 Đường Cách Mạng Tháng 8, Quận 3, TP.HCM', 'COD', DATE_SUB(NOW(), INTERVAL 3 DAY))";
    
    if (!mysqli_query($connect, $sql5)) {
        error_log("ERROR: Could not insert order 5: " . mysqli_error($connect));
    }
}

// Thêm dữ liệu mẫu vào products nếu chưa có
$sql_check_products = "SHOW TABLES LIKE 'products'";
$result_products = mysqli_query($connect, $sql_check_products);

if (mysqli_num_rows($result_products) == 0) {
    // Tạo bảng products
    $sql_create_products = "CREATE TABLE `products` (
        `product_id` int(11) NOT NULL AUTO_INCREMENT,
        `product_name` varchar(255) NOT NULL,
        `price` decimal(10,0) NOT NULL,
        `description` text DEFAULT NULL,
        `image` varchar(255) DEFAULT NULL,
        `stock` int(11) NOT NULL DEFAULT 0,
        `stock_quantity` int(11) NOT NULL DEFAULT 0, 
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`product_id`)
    )";
    mysqli_query($connect, $sql_create_products);
    
    // Thêm dữ liệu mẫu
    $sql_insert_products = "INSERT INTO products (product_name, price, description, stock, stock_quantity) VALUES 
    ('Kính mát Ray-Ban Aviator', 1200000, 'Kính mát Ray-Ban Aviator Classic với thiết kế biểu tượng', 15, 15),
    ('Kính cận Titan', 850000, 'Gọng kính cận Titan siêu nhẹ, bền bỉ', 20, 20),
    ('Kính râm thời trang', 650000, 'Kính râm phong cách, chống tia UV', 10, 10)";
    if (!mysqli_query($connect, $sql_insert_products)) {
        die("ERROR: Could not insert sample products: " . mysqli_error($connect));
    }
}

// Thêm dữ liệu mẫu vào order_details sau khi đã có orders và products
$sql_check_sample_order_items = "SELECT * FROM order_details LIMIT 1";
$result_order_items = mysqli_query($connect, $sql_check_sample_order_items);

if (mysqli_num_rows($result_order_items) == 0) {
    // Lấy order IDs thực tế từ bảng orders
    $sql_order_ids = "SELECT order_id FROM orders ORDER BY order_id LIMIT 3";
    $result_orders = mysqli_query($connect, $sql_order_ids);
    
    // Lấy IDs sản phẩm thực tế từ bảng products
    $sql_product_ids = "SELECT product_id FROM products ORDER BY product_id LIMIT 3";
    $result_products = mysqli_query($connect, $sql_product_ids);
    
    if (mysqli_num_rows($result_orders) > 0 && mysqli_num_rows($result_products) > 0) {
        $order_ids = [];
        while ($row = mysqli_fetch_assoc($result_orders)) {
            $order_ids[] = $row['order_id'];
        }
        
        $product_ids = [];
        while ($row = mysqli_fetch_assoc($result_products)) {
            $product_ids[] = $row['product_id'];
        }
        
        if (count($order_ids) > 0 && count($product_ids) > 0) {
            // Đảm bảo có ít nhất 1 order_id và 1 product_id
            $order_id1 = isset($order_ids[0]) ? $order_ids[0] : 1;
            $order_id2 = isset($order_ids[1]) ? $order_ids[1] : 1;
            $order_id3 = isset($order_ids[2]) ? $order_ids[2] : 1;
            
            $product_id1 = isset($product_ids[0]) ? $product_ids[0] : 1;
            $product_id2 = isset($product_ids[1]) ? $product_ids[1] : (isset($product_ids[0]) ? $product_ids[0] : 1);
            
            // Thêm dữ liệu mẫu với dữ liệu tồn tại trong CSDL
            $sql_insert_order_items = "INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) VALUES 
            ($order_id1, $product_id1, 2, 1200000, 2400000),
            ($order_id1, $product_id2, 1, 650000, 650000),
            ($order_id2, $product_id1, 2, 850000, 1700000),
            ($order_id3, $product_id2, 2, 1200000, 2400000),
            ($order_id3, $product_id1, 1, 850000, 850000)";
            
            if (!mysqli_query($connect, $sql_insert_order_items)) {
                error_log("ERROR: Could not insert sample order details: " . mysqli_error($connect));
                // Try to insert one by one to prevent total failure
                foreach ([$order_id1, $order_id2, $order_id3] as $oid) {
                    foreach ([$product_id1, $product_id2] as $pid) {
                        $sql = "INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) 
                                VALUES ($oid, $pid, 1, 1000000, 1000000)";
                        mysqli_query($connect, $sql);
                    }
                }
            }
        }
    }
}

// Thêm dữ liệu mẫu cho categories nếu chưa có
$sql_check_sample_category = "SELECT * FROM categories LIMIT 1";
$result_category = mysqli_query($connect, $sql_check_sample_category);

if (mysqli_num_rows($result_category) == 0) {
    $sql_insert_categories = "INSERT INTO categories (category_name, description) VALUES 
    ('Kính mát', 'Các loại kính chống nắng, bảo vệ mắt khỏi tia UV'),
    ('Kính cận', 'Kính gọng cho người cận thị'),
    ('Kính thời trang', 'Kính không độ dùng để trang trí, làm đẹp')";
    mysqli_query($connect, $sql_insert_categories);
}

// Thêm dữ liệu mẫu cho manufacturers nếu chưa có
$sql_check_sample_manufacturer = "SELECT * FROM manufacturers LIMIT 1";
$result_manufacturer = mysqli_query($connect, $sql_check_sample_manufacturer);

if (mysqli_num_rows($result_manufacturer) == 0) {
    $sql_insert_manufacturers = "INSERT INTO manufacturers (name, description) VALUES 
    ('Ray-Ban', 'Thương hiệu kính mắt nổi tiếng của Mỹ'),
    ('Gucci', 'Thương hiệu thời trang cao cấp của Ý'),
    ('Prada', 'Thương hiệu xa xỉ chuyên về thời trang và phụ kiện')";
    mysqli_query($connect, $sql_insert_manufacturers);
}

// Kiểm tra và tạo bảng brands nếu chưa tồn tại (cho tương thích ngược)
$sql_check_brands = "SHOW TABLES LIKE 'brands'";
$result_brands = mysqli_query($connect, $sql_check_brands);

if (mysqli_num_rows($result_brands) == 0) {
    // Tạo bảng brands
    $sql_create_brands = "CREATE TABLE `brands` (
        `brand_id` int(11) NOT NULL AUTO_INCREMENT,
        `brand_name` varchar(100) NOT NULL,
        `description` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`brand_id`)
    )";
    mysqli_query($connect, $sql_create_brands);
    
    // Sao chép dữ liệu từ bảng manufacturers sang brands
    $sql_insert_brands = "INSERT INTO brands (brand_name, description)
        SELECT name, description FROM manufacturers";
    mysqli_query($connect, $sql_insert_brands);
}

// Kiểm tra xem cột brand_id có tồn tại trong bảng products không
$sql_check_brand_id_column = "SHOW COLUMNS FROM products LIKE 'brand_id'";
$result_brand_id_column = mysqli_query($connect, $sql_check_brand_id_column);

if (mysqli_num_rows($result_brand_id_column) == 0) {
    // Thêm cột brand_id vào bảng products
    $sql_add_brand_id_column = "ALTER TABLE products ADD COLUMN brand_id INT DEFAULT NULL AFTER manufacturer_id";
    mysqli_query($connect, $sql_add_brand_id_column);
    
    // Cập nhật giá trị brand_id dựa trên manufacturer_id (nếu có dữ liệu)
    $sql_update_brand_id = "UPDATE products p
                           JOIN manufacturers m ON p.manufacturer_id = m.id
                           JOIN brands b ON m.name = b.brand_name
                           SET p.brand_id = b.brand_id
                           WHERE p.manufacturer_id IS NOT NULL";
    mysqli_query($connect, $sql_update_brand_id);
}

// Kiểm tra xem cột image_path có tồn tại trong bảng products không
$sql_check_image_path_column = "SHOW COLUMNS FROM products LIKE 'image_path'";
$result_image_path_column = mysqli_query($connect, $sql_check_image_path_column);

if (mysqli_num_rows($result_image_path_column) == 0) {
    // Thêm cột image_path và đồng bộ với cột image
    $sql_add_image_path_column = "ALTER TABLE products ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER image";
    mysqli_query($connect, $sql_add_image_path_column);
    
    // Cập nhật giá trị image_path từ image
    $sql_update_image_path = "UPDATE products SET image_path = image WHERE image IS NOT NULL";
    mysqli_query($connect, $sql_update_image_path);
}