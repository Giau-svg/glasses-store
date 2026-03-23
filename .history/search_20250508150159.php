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
                                <div class="search-result-info">
                                    Tìm thấy <?php echo $num_results; ?> sản phẩm phù hợp
                                </div>
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
    <script type="text/javascript">
      function addToCart(productId) {
        $.ajax({
            url: 'add_to_cart.php',
            type: 'GET',
            data: {
                id: productId
            },
            success: function(response) {
                alert("Thêm vào giỏ hàng thành công!");
                // Gọi lại get_cart_count.php để cập nhật số giỏ hàng
                $.get('get_cart_count.php', function(count){
                    $('#cart-count').text(count);
                });
            },
            error: function() {
                alert("Có lỗi xảy ra, vui lòng thử lại!");
            }
        });
        return false;
      }

      $(document).ready(function() {
        $.get('get_cart_count.php', function(count){
            $('#cart-count').text(count);
        });
      });
    </script>
    <style>
    .brand-title {
        font-size: 20px;
        color: #e63946;
        margin-top: 10px;
        font-weight: 600;
    }

    .empty-product {
        text-align: center;
        padding: 40px;
        background-color: #f8f9fa;
        border-radius: 10px;
        margin: 20px 0;
    }

    .empty-product p {
        font-size: 18px;
        color: #666;
        margin-bottom: 20px;
    }

    .btn-back-home {
        display: inline-block;
        background-color: #ffa500;
        color: #000;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-back-home:hover {
        background-color: #e69500;
        transform: translateY(-2px);
    }

    /* Animation for product cards */
    .category {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .category:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .category_link {
        text-decoration: none;
        color: inherit;
    }

    .category__img {
        width: 100%;
        aspect-ratio: 1;
        overflow: hidden;
        margin-bottom: 15px;
    }

    .category__img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .category__name {
        font-size: 16px;
        font-weight: 500;
        color: #333;
        margin: 10px 0;
        min-height: 40px;
    }

    .manufacturer-info {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }

    .category__price {
        font-size: 18px;
        color: #ff6b6b;
        font-weight: 600;
        margin: 10px 0;
    }

    .add-to-cart-btn {
        display: block;
        width: 100%;
        padding: 10px;
        background-color: #ffa500;
        color: #fff;
        text-align: center;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s ease;
        font-weight: 500;
        margin-top: 10px;
        font-size: 16px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        letter-spacing: 0.5px;
    }

    .add-to-cart-btn:hover {
        background-color: #ff8c00;
        color: #fff;
        text-decoration: none;
    }

    .category-btn {
        margin-top: 15px;
    }

    .search-result-info {
        font-size: 18px;
        font-weight: 500;
        color: #222;
        margin-bottom: 24px;
        margin-top: 8px;
        padding-left: 8px;
    }
    </style>
</body>

</html>