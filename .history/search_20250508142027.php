<?php
session_start();

require 'admin/root.php';

// Lấy filter từ GET
$search_products = isset($_GET['search']) ? trim($_GET['search']) : '';
$price = isset($_GET['price']) ? $_GET['price'] : '';
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Xây dựng điều kiện WHERE động
$where = "WHERE 1=1";
$params = [];
$types = "";

// Lọc theo tên sản phẩm
if ($search_products !== '') {
    $where .= " AND products.product_name LIKE ?";
    $params[] = "%$search_products%";
    $types .= "s";
}

// Lọc theo giá
if ($price !== '') {
    list($min, $max) = explode('-', $price);
    $where .= " AND products.price >= ? AND products.price <= ?";
    $params[] = (int)$min;
    $params[] = (int)$max;
    $types .= "ii";
}

// Lọc theo thương hiệu
if ($brand !== '') {
    $where .= " AND products.brand_id = ?";
    $params[] = (int)$brand;
    $types .= "i";
}

// Lọc theo danh mục
if ($category !== '') {
    $where .= " AND products.category_id = ?";
    $params[] = (int)$category;
    $types .= "i";
}

$sql = "SELECT products.*, brands.brand_name 
        FROM products 
        LEFT JOIN brands ON brands.brand_id = products.brand_id
        $where
        ORDER BY products.product_id DESC";

$stmt = mysqli_prepare($connect, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm sản phẩm</title>
    <link rel="stylesheet" href="./public/css/rss.css" />
    <link rel="stylesheet" href="./public/css/style.css" />
    <link rel="stylesheet" href="./public/css/view_all.css" />
    <link rel="stylesheet" href="./public/css/comments.css" />
    <link rel="stylesheet" href="./public/css/breadcrumb.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
</head>

<body>
    <div class="wrapper">
        
        <?php include './detail/detail_search.php'?>
        <?php include './partials/footer.php' ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="./public/js/js.js"></script>
    <script src="./public/js/slider.js"></script>
    <script src="./public/js/live-searchs.js"></script>
</body>

</html>