<?php

session_start();
require 'admin/root.php';

// Lấy từ khóa tìm kiếm
$search_products = isset($_GET['search']) ? trim($_GET['search']) : '';

// Lọc theo giá và thương hiệu nếu có
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$brand_id = isset($_GET['brand_id']) ? $_GET['brand_id'] : '';

// Xây dựng câu truy vấn
$sql = "SELECT products.*, brands.brand_name FROM products JOIN brands ON brands.brand_id = products.brand_id WHERE 1=1";
if ($search_products !== '') {
    $search_escaped = mysqli_real_escape_string($connect, $search_products);
    $sql .= " AND products.product_name LIKE '%$search_escaped%'";
}
if (!empty($brand_id)) {
    $sql .= " AND products.brand_id = '" . mysqli_real_escape_string($connect, $brand_id) . "'";
}
if (!empty($price_range)) {
    if ($price_range == 1) $sql .= " AND price < 1000000";
    elseif ($price_range == 2) $sql .= " AND price >= 1000000 AND price <= 3000000";
    elseif ($price_range == 3) $sql .= " AND price > 3000000 AND price <= 5000000";
    elseif ($price_range == 4) $sql .= " AND price > 5000000";
}
$sql .= " ORDER BY products.product_id DESC";
$result = mysqli_query($connect, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm: <?php echo htmlspecialchars($search_products) ?> - Mắt Kính Thời Trang</title>
    <link rel="stylesheet" href="./public/css/rss.css" />
    <link rel="stylesheet" href="./public/css/style.css" />
    <link rel="stylesheet" href="./public/css/view_all.css" />
    <link rel="stylesheet" href="./public/css/pages.css" />
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
        
        <!-- Hiển thị sản phẩm theo danh mục -->
        <main class="container" style="padding-top: 20px; margin-top: 0;">
            <div class="grid_full-width">
                <div class="grid_full-width content">
                    <div class="content__brands">
                        <div class="grid">
                            <div class="brands__heading">
                                <h1>Kết quả tìm kiếm: <?php echo htmlspecialchars($search_products) ?></h1>
                                <p>Tìm thấy <b><?php echo mysqli_num_rows($result); ?></b> sản phẩm</p>
                            </div>
                        </div>
                        
                        <!-- Danh sách sản phẩm -->
                        <div class="grid">
                            <div class="row row-category">
                                <?php if(mysqli_num_rows($result) > 0) { ?>
                                    <?php while($product = mysqli_fetch_assoc($result)) { ?>
                                        <div class="col col-3 col-2-mt mt-10">
                                            <div class="category">
                                                <a href="view_detail.php?id=<?php echo $product['product_id'] ?>" class="category_link">
                                                    <div class="category__img">
                                                        <img src="admin/products/uploads/<?php echo $product['image_path'] ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                                    </div>
                                                    <h4 class="category__name"><?php echo $product['product_name'] ?></h4>
                                                    <div class="manufacturer-info">
                                                        <?php echo $product['brand_name'] ?>
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
                                            <p>Không tìm thấy sản phẩm nào phù hợp với từ khóa tìm kiếm.</p>
                                            <a href="index.php" class="btn-back-home">Quay lại trang chủ</a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <!-- Sidebar filter -->
                    <div class="col-3">
                        <div class="content__sidebar">
                            <div class="sidebar__category">
                                <div class="sidebar__header">
                                    <i class="fas fa-filter category-icon"></i>
                                    <h3 class="sidebar__title">Lọc sản phẩm</h3>
                                </div>
                                <form method="get" action="detail_search.php" style="padding: 0 10px 10px 10px;">
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_products) ?>">
                                    <div class="sidebar__filter-group">
                                        <label>Khoảng giá:</label>
                                        <select name="price_range" class="sidebar__filter-select">
                                            <option value="">Tất cả</option>
                                            <option value="1" <?php if($price_range=='1') echo 'selected'; ?>>Dưới 1 triệu</option>
                                            <option value="2" <?php if($price_range=='2') echo 'selected'; ?>>1 - 3 triệu</option>
                                            <option value="3" <?php if($price_range=='3') echo 'selected'; ?>>3 - 5 triệu</option>
                                            <option value="4" <?php if($price_range=='4') echo 'selected'; ?>>Trên 5 triệu</option>
                                        </select>
                                    </div>
                                    <div class="sidebar__filter-group">
                                        <label>Thương hiệu:</label>
                                        <select name="brand_id" class="sidebar__filter-select">
                                            <option value="">Tất cả</option>
                                            <?php
                                            $brands = mysqli_query($connect, "SELECT * FROM brands");
                                            while($b = mysqli_fetch_assoc($brands)) {
                                                $selected = ($brand_id == $b['brand_id']) ? 'selected' : '';
                                                echo '<option value="'.$b['brand_id'].'" '.$selected.'>'.$b['brand_name'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="sidebar__filter-btn">Lọc</button>
                                </form>
                                <div class="sidebar__footer">
                                    <p>Lọc nhanh sản phẩm theo nhu cầu của bạn</p>
                                </div>
                            </div>
                        </div>
                    </div>
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
</body>

</html>

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
}

.add-to-cart-btn:hover {
    background-color: #ff8c00;
}

.category-btn {
    margin-top: 15px;
}
</style>