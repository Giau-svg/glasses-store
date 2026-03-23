<?php
session_start();
require 'admin/root.php';

if (empty($_SESSION['customer_id'])) {
    echo 0;
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE product_id = '$id'";
$result = mysqli_query($connect, $sql);
$each = mysqli_fetch_array($result);

if (empty($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['product_name'] = $each['product_name'];
    $_SESSION['cart'][$id]['image_path'] = $each['image_path'];
    $_SESSION['cart'][$id]['price'] = $each['price'];
    $_SESSION['cart'][$id]['quantity'] = 1;
} else {
    $_SESSION['cart'][$id]['quantity']++;
}

echo 1;
mysqli_close($connect);