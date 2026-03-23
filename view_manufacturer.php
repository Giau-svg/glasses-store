<?php
session_start();
require './admin/root.php';

// Kiểm tra tham số ID
if (empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);

// Lấy thông tin nhà sản xuất
$sql_manufacturer = "SELECT id, name, description, created_at FROM manufacturers WHERE id = ?";
$stmt_manufacturer = mysqli_prepare($connect, $sql_manufacturer);
mysqli_stmt_bind_param($stmt_manufacturer, "i", $id);
mysqli_stmt_execute($stmt_manufacturer);
$result_manufacturer = mysqli_stmt_get_result($stmt_manufacturer);

// Kiểm tra xem nhà sản xuất có tồn tại không
if (mysqli_num_rows($result_manufacturer) == 0) {
    header('Location: index.php');
    exit;
}

$manufacturer = mysqli_fetch_assoc($result_manufacturer);

// Lấy danh sách sản phẩm của nhà sản xuất
$sqlPt = "SELECT COUNT(product_id) as total FROM products WHERE manufacturer_id = ?";
$stmt_count = mysqli_prepare($connect, $sqlPt);
mysqli_stmt_bind_param($stmt_count, "i", $id);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row = mysqli_fetch_assoc($result_count);
$total_records = $row['total'];

// Thiết lập phân trang
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 6; // 6 sản phẩm mỗi trang (giảm từ 12)
$total_page = ceil($total_records / $limit);

// Đảm bảo trang hiện tại nằm trong khoảng hợp lệ
if ($current_page > $total_page) {
    $current_page = $total_page;
} else if ($current_page < 1) {
    $current_page = 1;
}

// Tính vị trí bắt đầu lấy dữ liệu
$start = max(0, ($current_page - 1) * $limit);

// Lấy danh sách sản phẩm
$sql = "SELECT p.*, b.brand_name, c.category_name 
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.brand_id 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.manufacturer_id = ? 
        ORDER BY p.product_id DESC 
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "iii", $id, $limit, $start);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($manufacturer['name']); ?> - Sản phẩm | EYEGLASSES</title>
    <link rel="stylesheet" href="./public/css/rss.css" />
    <link rel="stylesheet" href="./public/css/style.css" />
    <link rel="stylesheet" href="./public/css/pages.css" />
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"
      />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
</head>
<body>
    <div class="wrapper">
        <?php include './partials/sticky.php' ?>
        
        <div class="container">
            <div class="grid_full-width">
                <?php include './partials/menu.php' ?>
                
                <div class="grid_full-width content">
                    <div class="content__brands">
                        <div class="grid">
                            <div class="manufacturer-banner">
                                <div class="manufacturer-banner__info">
                                    <h1 class="manufacturer-banner__title"><?php echo htmlspecialchars($manufacturer['name']); ?></h1>
                                    <?php if (!empty($manufacturer['description'])): ?>
                                    <p class="manufacturer-banner__desc"><?php echo htmlspecialchars($manufacturer['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="manufacturer-banner__meta">
                                        <div class="manufacturer-banner__stats">
                                            <i class="fas fa-glasses"></i> <?php echo $total_records; ?> sản phẩm
                                        </div>
                                        <div class="manufacturer-banner__since">
                                            <i class="far fa-calendar-alt"></i> Đối tác từ: <?php echo date('d/m/Y', strtotime($manufacturer['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="manufacturer-banner__icon">
                                    <i class="fas fa-industry"></i>
                                </div>
                            </div>
                            
                            <div class="manufacturer-breadcrumb">
                                <a href="index.php">Trang chủ</a> &gt; 
                                <a href="index.php?man_page=1">Nhà sản xuất</a> &gt; 
                                <span><?php echo htmlspecialchars($manufacturer['name']); ?></span>
                            </div>
                            
                            <!-- Hiển thị danh sách sản phẩm -->
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <div class="row row-category">
                                    <?php foreach ($result as $product): ?>
                                        <div class="col col-3 col-2-mt mt-10">
                                            <div class="category">
                                                <a href="view_detail.php?id=<?php echo $product['product_id']; ?>" class="category_link">
                                                    <div class="category__img">
                                                        <img src="admin/products/uploads/<?php echo $product['image_path']; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                                    </div>
                                                    <h4 class="category__name"><?php echo htmlspecialchars($product['product_name']); ?></h4>
                                                    <?php if (!empty($product['brand_name'])): ?>
                                                    <div class="category__brand">
                                                        <span><?php echo htmlspecialchars($product['brand_name']); ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="category__price">
                                                        <p>Giá bán:</p>
                                                        <span class="category__money">
                                                            <?php echo currency_format($product['price']); ?>
                                                        </span>
                                                    </div>
                                                </a>
                                                <div class="category-btn">
                                                    <ul class="category-cart">
                                                        <li>
                                                            <a href="view_detail.php?id=<?php echo $product['product_id']; ?>">
                                                                Mua ngay
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <?php if (!empty($_SESSION['id'])): ?>
                                                                <a onclick="return Suc()" href="add_to_cart.php?id=<?php echo $product['product_id']; ?>">
                                                                    Thêm vào giỏ hàng
                                                                </a>
                                                            <?php endif; ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Phân trang - luôn hiển thị -->
                                <div class="row">
                                    <div class="row_page">
                                        <nav>
                                            <ul class="pagination">
                                                <?php if ($current_page > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="view_manufacturer.php?id=<?php echo $id; ?>&page=<?php echo ($current_page - 1); ?>">Prev</a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">Prev</span>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                // Hiển thị tối đa 5 trang
                                                $start_page = max(1, $current_page - 2);
                                                $end_page = min($total_page, $start_page + 4);
                                                
                                                // Luôn hiển thị ít nhất 1 trang
                                                if ($total_page == 0) {
                                                    $total_page = 1;
                                                    $end_page = 1;
                                                }
                                                
                                                // Hiển thị dấu ... nếu không bắt đầu từ trang 1
                                                if ($start_page > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="view_manufacturer.php?id=<?php echo $id; ?>&page=1">1</a>
                                                    </li>
                                                    <?php if ($start_page > 2): ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">...</span>
                                                    </li>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                                        <?php if ($i == $current_page): ?>
                                                            <span class="page-link"><?php echo $i; ?></span>
                                                        <?php else: ?>
                                                            <a class="page-link" href="view_manufacturer.php?id=<?php echo $id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endfor; ?>
                                                
                                                <?php
                                                // Hiển thị dấu ... nếu không hiển thị đến trang cuối
                                                if ($end_page < $total_page): ?>
                                                    <?php if ($end_page < $total_page - 1): ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">...</span>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="view_manufacturer.php?id=<?php echo $id; ?>&page=<?php echo $total_page; ?>"><?php echo $total_page; ?></a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php if ($current_page < $total_page): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="view_manufacturer.php?id=<?php echo $id; ?>&page=<?php echo ($current_page + 1); ?>">Next</a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">Next</span>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-products">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <p>Hiện chưa có sản phẩm nào từ nhà sản xuất này.</p>
                                    <a href="index.php" class="btn-back">Quay lại trang chủ</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php include './partials/slidebar.php' ?>
                </div>
                
                <?php include './partials/footer.php' ?>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="./public/js/js.js"></script>
    <script src="./public/js/slider.js"></script>
    <script type="text/javascript">
      function Suc() {
        return alert("Thêm giỏ hàng thành công!");
      }
    </script>
</body>
</html>

<style>
/* Breadcrumb */
.manufacturer-breadcrumb {
    margin: 15px 0 30px;
    padding: 10px 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
    font-size: 14px;
}

.manufacturer-breadcrumb a {
    color: #0066cc;
    text-decoration: none;
}

.manufacturer-breadcrumb a:hover {
    text-decoration: underline;
}

.manufacturer-breadcrumb span {
    color: #333;
    font-weight: 500;
}

/* Banner */
.manufacturer-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 10px;
    padding: 40px;
    margin-top: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.manufacturer-banner__info {
    flex: 1;
}

.manufacturer-banner__title {
    font-size: 32px;
    color: #333;
    margin-bottom: 15px;
    position: relative;
}

.manufacturer-banner__title:after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -10px;
    width: 60px;
    height: 3px;
    background-color: #e63946;
}

.manufacturer-banner__desc {
    color: #555;
    font-size: 16px;
    margin-bottom: 20px;
    max-width: 600px;
    line-height: 1.6;
}

.manufacturer-banner__meta {
    display: flex;
    gap: 20px;
}

.manufacturer-banner__stats,
.manufacturer-banner__since {
    display: flex;
    align-items: center;
    color: #666;
    font-size: 14px;
}

.manufacturer-banner__stats i,
.manufacturer-banner__since i {
    margin-right: 5px;
    color: #e63946;
}

.manufacturer-banner__icon {
    font-size: 80px;
    color: rgba(230, 57, 70, 0.15);
    margin-left: 20px;
}

.no-products {
    text-align: center;
    padding: 40px 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin: 30px 0;
}

.no-products i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 20px;
}

.no-products p {
    font-size: 16px;
    color: #666;
    margin-bottom: 20px;
}

.btn-back {
    display: inline-block;
    padding: 10px 20px;
    background-color: #e63946;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 500;
    transition: background-color 0.3s;
}

.btn-back:hover {
    background-color: #d62c39;
}

@media (max-width: 768px) {
    .manufacturer-banner {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .manufacturer-banner__title:after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .manufacturer-banner__meta {
        justify-content: center;
    }
    
    .manufacturer-banner__icon {
        margin: 20px 0 0;
    }
}
</style> 