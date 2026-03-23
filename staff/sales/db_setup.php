<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to MySQL database
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'eyeglasses_shop';

$connect = mysqli_connect($host, $user, $password, $dbname);

// Check if connection is successful
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "<p>MySQL connection successful</p>";

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($connect, $sql)) {
    echo "<p>Database created successfully or already exists</p>";
} else {
    echo "<p>Error creating database: " . mysqli_error($connect) . "</p>";
}

// Select the database
mysqli_select_db($connect, $dbname);

// Create tables
$sql_orders = "CREATE TABLE IF NOT EXISTS `orders` (
    `order_id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
    `total_amount` decimal(10,2) NOT NULL,
    `order_status` enum('pending','confirmed','shipping','delivered','cancelled') DEFAULT 'pending',
    `payment_method` varchar(50) DEFAULT NULL,
    `shipping_address` text DEFAULT NULL,
    `shipping_phone` varchar(20) DEFAULT NULL,
    `shipping_name` varchar(100) DEFAULT NULL,
    `shipping_email` varchar(100) DEFAULT NULL,
    `shipping_notes` text DEFAULT NULL,
    PRIMARY KEY (`order_id`)
)";

if (mysqli_query($connect, $sql_orders)) {
    echo "<p>Orders table created successfully or already exists</p>";
} else {
    echo "<p>Error creating orders table: " . mysqli_error($connect) . "</p>";
}

// Clear existing orders data
$sql_clear = "TRUNCATE TABLE orders";
if (mysqli_query($connect, $sql_clear)) {
    echo "<p>Cleared existing orders data</p>";
} else {
    echo "<p>Error clearing orders data: " . mysqli_error($connect) . "</p>";
}

// Insert sample data
$sql_insert = "INSERT INTO orders (user_id, total_amount, order_status, shipping_notes, shipping_name, shipping_phone, shipping_email, shipping_address, payment_method, order_date) VALUES 
(1, 2500000, 'pending', 'Giao hàng trong giờ hành chính', 'Nguyễn Văn A', '0901234567', 'nguyenvana@gmail.com', '123 Đường Lê Lợi, Quận 1, TP.HCM', 'COD', NOW()),
(2, 1800000, 'confirmed', 'Gọi trước khi giao', 'Trần Thị B', '0912345678', 'tranthib@gmail.com', '456 Đường Nguyễn Huệ, Quận 1, TP.HCM', 'Banking', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 3200000, 'shipping', 'Để hàng tại quầy lễ tân', 'Lê Văn C', '0823456789', 'levanc@gmail.com', '789 Đường 3/2, Quận 10, TP.HCM', 'COD', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 1500000, 'delivered', NULL, 'Nguyễn Văn A', '0901234567', 'nguyenvana@gmail.com', '123 Đường Lê Lợi, Quận 1, TP.HCM', 'Banking', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 950000, 'cancelled', 'Khách hàng đổi ý', 'Phạm Thị D', '0978123456', 'phamthid@gmail.com', '321 Đường Cách Mạng Tháng 8, Quận 3, TP.HCM', 'COD', DATE_SUB(NOW(), INTERVAL 3 DAY))";

if (mysqli_query($connect, $sql_insert)) {
    echo "<p>Sample orders data inserted successfully</p>";
} else {
    echo "<p>Error inserting sample orders data: " . mysqli_error($connect) . "</p>";
}

// Select and display data to verify
$sql_select = "SELECT * FROM orders";
$result = mysqli_query($connect, $sql_select);

echo "<h2>Orders Data:</h2>";
echo "<table border='1'>
<tr>
<th>Order ID</th>
<th>Customer</th>
<th>Phone</th>
<th>Status</th>
<th>Date</th>
</tr>";

while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['order_id'] . "</td>";
    echo "<td>" . $row['shipping_name'] . "</td>";
    echo "<td>" . $row['shipping_phone'] . "</td>";
    echo "<td>" . $row['order_status'] . "</td>";
    echo "<td>" . $row['order_date'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Link to orders.php
echo "<p><a href='orders.php'>Go to Orders Page</a></p>";

// Close connection
mysqli_close($connect);
?> 