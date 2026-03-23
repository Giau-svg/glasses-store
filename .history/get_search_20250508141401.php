<?php 
require 'admin/root.php';

$search = trim(addslashes($_GET['term']));

$sql = "SELECT * from products where product_name like '%$search%' ";
$result = mysqli_query($connect, $sql);

$arr = [];
foreach ($result as $each) {
    $arr[] = [
        'label' => $each['product_name'],
        'value' => 'id=' . $each['product_id'],
        'photo' => $each['image_path'],
        'price' => currency_format($each['price'])
    ];
}
echo json_encode($arr);
mysqli_close($connect);
