<?php


require 'admin/root.php';

// Lấy thương hiệu từ URL (nếu có)
$brand_id = isset($_GET['id']) ? $_GET['id'] : '';

// Lấy loại kính từ tham số URL
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Lấy category_id từ URL 
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : '';

// Lấy thông tin thương hiệu để hiển thị tên đúng
$brand_name = '';
if (!empty($brand_id)) {
    $sql_brand = "SELECT brand_name FROM brands WHERE brand_id = $brand_id";
    $result_brand = mysqli_query($connect, $sql_brand);
    if ($result_brand && mysqli_num_rows($result_brand) > 0) {
        $brand_data = mysqli_fetch_assoc($result_brand);
        $brand_name = $brand_data['brand_name'];
    }
}

// Lấy thông tin danh mục để hiển thị tiêu đề chính xác
$category_name = '';
if (!empty($category_id)) {
    $sql_category = "SELECT category_name FROM categories WHERE category_id = $category_id";
    $result_category = mysqli_query($connect, $sql_category);
    if ($result_category && mysqli_num_rows($result_category) > 0) {
        $category_data = mysqli_fetch_assoc($result_category);
        $category_name = $category_data['category_name'];
    }
}


// Xây dựng câu truy vấn SQL
$sql = "SELECT products.*, brands.brand_name 
        FROM products 
        JOIN brands ON brands.brand_id = products.brand_id 
        WHERE 1=1";

// Thêm điều kiện lọc theo thương hiệu
if (!empty($brand_id)) {
    $sql .= " AND products.brand_id = " . mysqli_real_escape_string($connect, $brand_id);
}

// Thêm điều kiện lọc theo danh mục nếu có
if (!empty($category_id)) {
    $sql .= " AND products.category_id = " . mysqli_real_escape_string($connect, $category_id);
}

// Thêm điều kiện sắp xếp
$sql .= " ORDER BY products.product_id DESC";

// Thực hiện truy vấn
$result_products = mysqli_query($connect, $sql);

// Kiểm tra có sản phẩm không
if (mysqli_num_rows($result_products) == 0) {
    // Ghi log để debug (có thể xóa sau khi hoàn thành)
    error_log("Không tìm thấy sản phẩm với: brand=$brand_id, type=$type, category_id=$category_id, SQL: $sql");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Mắt Kính Thời Trang</title>
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
                                <?php if (!empty($brand_name) && !empty($category_name)): ?>
                                    <h1><?php echo $brand_name; ?></h1>
                                    <p class="brand-title"><?php echo $category_name; ?></p>
                                <?php elseif (!empty($brand_name)): ?>
                                    <h1><?php echo $brand_name; ?></h1>
                                <?php elseif (!empty($category_name)): ?>
                                    <h1><?php echo $category_name; ?></h1>
                                <?php else: ?>
                                    <h1>Sản phẩm</h1>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Danh sách sản phẩm -->
                        <div class="grid">
                            <div class="row row-category">
                                <?php if(isset($result_products) && mysqli_num_rows($result_products) > 0) { ?>
                                    <?php foreach($result_products as $product) { ?>
                                        <div class="col col-3 col-2-mt mt-10">
                                            <div class="category">
                                                <a href="view_detail.php?id=<?php echo $product['product_id'] ?>" class="category_link">
                                                    <div class="category__img">
                                                        <img src="admin/products/uploads/<?php echo $product['image_path'] ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                                    </div>
                                                    <h4 class="category__name"><?php echo $product['product_name'] ?></h4>
                                                    <div class="manufacturer-info">
                                                        <?php echo $product['brand_name'] ?><br>
                                                        Nhà SX: <?php echo $product['brand_name'] ?>
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
                                            <?php if (!empty($brand_name) && !empty($category_name)): ?>
                                                <p>Không có sản phẩm <?php echo $category_name; ?> thuộc thương hiệu <?php echo $brand_name; ?></p>
                                            <?php elseif (!empty($brand_name)): ?>
                                                <p>Không có sản phẩm thuộc thương hiệu <?php echo $brand_name; ?></p>
                                            <?php elseif (!empty($category_name)): ?>
                                                <p>Không có sản phẩm trong danh mục <?php echo $category_name; ?></p>
                                            <?php else: ?>
                                                <p>Không có sản phẩm nào</p>
                                            <?php endif; ?>
                                            <a href="index.php" class="btn-back-home">Quay lại trang chủ</a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php include './partials/slidebar.php' ?>
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