<?php 
session_start();
require 'admin/root.php';

// Debug information
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['checkout_error'] = 'Vui lòng đăng nhập để đặt hàng';
    header('location:view_cart.php?error=1');
    exit();
}

$name_receiver = $_POST['name_receiver'] ?? '';
$phone_receiver = $_POST['phone_receiver'] ?? '';
$address_receiver = $_POST['address_receiver'] ?? '';

// Validate required fields
if (empty($name_receiver) || empty($phone_receiver) || empty($address_receiver)) {
    $_SESSION['checkout_error'] = 'Vui lòng điền đầy đủ thông tin người nhận';
    header('location:view_cart.php?error=1');
    exit();
}

$carts = $_SESSION['carts'] ?? null;

// Check if cart is empty
if (empty($carts)) {
    $_SESSION['checkout_error'] = 'Giỏ hàng trống, không thể đặt hàng';
    header('location:view_cart.php?error=1');
    exit();
}

$customer_id = $_SESSION['customer_id'];
$status = 'pending';

$total_price = 0;
if(!empty($_SESSION['carts'])){
    foreach($carts as $each){
        $total_price += $each['quantity'] * $each['price'];
    }
    
    // Debug the database connection
    if (!$connect) {
        echo "Database connection failed: " . mysqli_connect_error();
        exit();
    }
    
    // Add payment_method column to the SQL if it doesn't exist yet
    // To check if payment_method column exists
    $check_column = mysqli_query($connect, "SHOW COLUMNS FROM `orders` LIKE 'payment_method'");
    if (mysqli_num_rows($check_column) == 0) {
        // Add payment_method column if it doesn't exist
        $alter_table = "ALTER TABLE `orders` ADD COLUMN `payment_method` VARCHAR(50) DEFAULT 'cod' AFTER `status`";
        mysqli_query($connect, $alter_table);
    }
    
    $sql = "INSERT INTO `orders`(user_id, shipping_name, shipping_phone, shipping_address, order_status, total_amount)
    VALUES ('$customer_id','$name_receiver', '$phone_receiver', '$address_receiver', '$status', '$total_price')";
    $insert_result = mysqli_query($connect, $sql);
    
    if (!$insert_result) {
        echo "Error in INSERT query: " . mysqli_error($connect);
        $_SESSION['checkout_error'] = 'Lỗi khi xử lý đơn hàng: ' . mysqli_error($connect);
        header('location:view_cart.php?error=1');
        exit();
    }
    
    $sql = "SELECT max(order_id) as last_order_id FROM orders WHERE user_id = '$customer_id'";
    $result = mysqli_query($connect, $sql);
    
    if (!$result) {
        $_SESSION['checkout_error'] = 'Lỗi khi lấy ID đơn hàng: ' . mysqli_error($connect);
        header('location:view_cart.php?error=1');
        exit();
    }
    
    $order_id = mysqli_fetch_assoc($result)['last_order_id'];
    $_SESSION['last_order_id'] = $order_id; // Store order ID in session for payment page
    
    $order_detail_success = true;
    
    foreach($carts as $product_lp_id => $each){
        $quantity = $each['quantity'];
        $price = $each['price'];
        $subtotal = $quantity * $price;
        $sql = "INSERT INTO `order_details`(order_id, product_id, quantity, unit_price, subtotal) 
               VALUES ('$order_id', '$product_lp_id', '$quantity', '$price', '$subtotal')";
        $detail_result = mysqli_query($connect, $sql);
        
        if (!$detail_result) {
            $order_detail_success = false;
            echo "Error in order detail insert: " . mysqli_error($connect);
        }
    }
    
    if (!$order_detail_success) {
        $_SESSION['checkout_error'] = 'Lỗi khi lưu chi tiết đơn hàng: ' . mysqli_error($connect);
        header('location:view_cart.php?error=1');
        exit();
    }
    
    unset($_SESSION['carts']);
}

mysqli_close($connect);

// Redirect to payment options instead of directly to order view
header('location:payment_options.php');