<?php
// Include database connection
require 'root.php';

// Check if user is logged in as admin
session_start();
if(!isset($_SESSION['level'])){
    header('location:../index.php');
    exit;
}

// ALTER TABLE to add type column if it doesn't exist
$sql_check = "SHOW COLUMNS FROM categories LIKE 'type'";
$result_check = mysqli_query($connect, $sql_check);

if(mysqli_num_rows($result_check) == 0) {
    // Column doesn't exist, add it
    $sql_alter = "ALTER TABLE categories ADD COLUMN type VARCHAR(30) DEFAULT NULL";
    if(mysqli_query($connect, $sql_alter)) {
        echo "Column 'type' added successfully.<br>";
    } else {
        echo "Error adding column: " . mysqli_error($connect) . "<br>";
    }
} else {
    echo "Column 'type' already exists.<br>";
}

// Update categories with appropriate types
$type_map = [
    1 => 'sunglasses',     // Kính mát
    2 => 'eyeglasses',     // Kính cận
    3 => 'lens',           // Kính viễn
    4 => 'contact-lens',   // Kính đa tròng
    5 => 'fashion-glasses' // Kính thời trang
];

foreach($type_map as $category_id => $type) {
    $sql_update = "UPDATE categories SET type = '$type' WHERE id = $category_id";
    if(mysqli_query($connect, $sql_update)) {
        echo "Updated category $category_id with type '$type'<br>";
    } else {
        echo "Error updating category $category_id: " . mysqli_error($connect) . "<br>";
    }
}

echo "<br>Update completed. <a href='products/index.php'>Go to Products</a>";
?> 