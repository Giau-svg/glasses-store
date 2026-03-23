<?php
// Database connection
require_once '../../admin/config/connect.php';

// Capture output instead of sending it to browser
ob_start();

// Check if invoice_number column exists
$check_column = "SHOW COLUMNS FROM orders LIKE 'invoice_number'";
$result = mysqli_query($connect, $check_column);

if (mysqli_num_rows($result) == 0) {
    // The column doesn't exist, so add it
    $add_column = "ALTER TABLE orders ADD COLUMN invoice_number VARCHAR(50) NULL AFTER total_amount";
    mysqli_query($connect, $add_column);
} 

// Check if shipping_fee column exists
$check_shipping_fee = "SHOW COLUMNS FROM orders LIKE 'shipping_fee'";
$result_shipping = mysqli_query($connect, $check_shipping_fee);

if (mysqli_num_rows($result_shipping) == 0) {
    // The column doesn't exist, so add it
    $add_shipping = "ALTER TABLE orders ADD COLUMN shipping_fee DECIMAL(10,2) DEFAULT 0 AFTER total_amount";
    mysqli_query($connect, $add_shipping);
}

// Update the orders table to ensure all records have an invoice number
$update_invoices = "UPDATE orders SET invoice_number = CONCAT('INV', DATE_FORMAT(order_date, '%Y%m%d'), order_id) 
                    WHERE invoice_number IS NULL AND order_status IN ('processing', 'shipped', 'delivered')";
mysqli_query($connect, $update_invoices);

// Clear the output buffer
ob_end_clean();
?> 