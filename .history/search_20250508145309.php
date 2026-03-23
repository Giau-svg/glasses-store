<?php
session_start();

require 'admin/root.php';

// Lấy filter từ GET
$search_products = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
$price = isset($_REQUEST['price']) ? $_REQUEST['price'] : '';
$brand = isset($_REQUEST['brand']) ? $_REQUEST['brand'] : '';
$category = isset($_REQUEST['category']) ? $_REQUEST['category'] : '';

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

$num_results = mysqli_num_rows($result);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

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
        <header class="header-section">
            <div class="logo-container">
                <?php include './partials/sticky.php' ?>
            </div>
            <div class="menu-container">
                <div style="max-width: 1400px; margin: 0 auto; width: 100%;">
                    <?php include './partials/menu.php' ?>
                </div>
            </div>
        </header>
        <main class="container" style="padding-top: 20px; margin-top: 0;">
            <div class="grid_full-width">
                <div class="grid_full-width content">
                    <div class="content__brands">
                        <div class="grid">
                            <div class="brands__heading">
                                <h1>Kết quả tìm kiếm</h1>
                                <p class="brand-title">Tìm thấy <?php echo $num_results; ?> sản phẩm phù hợp</p>
                            </div>
                        </div>
                        <!-- Danh sách sản phẩm -->
                        <div class="grid">
                            <div class="row row-category">
                                <?php if ($num_results > 0) { ?>
                                    <?php foreach ($products as $product) { ?>
                                        <div class="col col-3 col-2-mt mt-10">
                                            <div class="category">
                                                <a href="view_detail.php?id=<?php echo $product['product_id'] ?>" class="category_link">
                                                    <div class="category__img">
                                                        <img src="admin/products/uploads/<?php echo $product['image_path'] ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                                    </div>
                                                    <h4 class="category__name"><?php echo $product['product_name'] ?></h4>
                                                    <div class="manufacturer-info">
                                                        <?php echo $product['brand_name'] ?><br>
                                                    </div>
                                                    <div class="category__price">
                                                        <?php echo number_format($product['price'], 0, ',', '.'); ?>đ
                                                    </div>
                                                </a>
                                                <div class="category-btn">
                                                    <?php if (!empty($_SESSION['customer_id'])) { ?>
                                                        <a onclick="return addToCart(<?php echo $product['product_id']; ?>)" href="javascript:void(0)" class="add-to-cart-btn">
                                                            Thêm vào giỏ hàng
                                                        </a>
                                                    <?php } else { ?>
                                                        <a href="login.php" class="add-to-cart-btn">
                                                            Thêm vào giỏ hàng
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } else { ?>
                                    <div class="col col-12">
                                        <div class="empty-product">
                                            <p>Không tìm thấy sản phẩm phù hợp.</p>
                                            <a href="index.php" class="btn-back-home">Quay lại trang chủ</a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php include './partials/slidebar_filter.php' ?>
                </div>
            </div>
        </main>
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