<?php
require_once './admin/root.php';

// Kiểm tra xem đã có phiên người dùng chưa
if (!isset($_SESSION)) {
    session_start();
}

// Comment out the migration code as we decided to use category_id
/*
// Check which column exists in the categories table
$check_id_column = "SHOW COLUMNS FROM categories LIKE 'id'";
$id_result = mysqli_query($connect, $check_id_column);

$check_category_id_column = "SHOW COLUMNS FROM categories LIKE 'category_id'";
$category_id_result = mysqli_query($connect, $check_category_id_column);

// If categories uses category_id but not id, we need to convert the structure
if (mysqli_num_rows($category_id_result) > 0 && mysqli_num_rows($id_result) == 0) {
    // Rename the column
    $alter_sql = "ALTER TABLE categories CHANGE category_id id INT(11) NOT NULL AUTO_INCREMENT";
    mysqli_query($connect, $alter_sql);
    
    // Also rename category_name to name if needed
    $check_name_column = "SHOW COLUMNS FROM categories LIKE 'name'";
    $name_result = mysqli_query($connect, $check_name_column);
    
    $check_category_name_column = "SHOW COLUMNS FROM categories LIKE 'category_name'";
    $category_name_result = mysqli_query($connect, $check_category_name_column);
    
    if (mysqli_num_rows($category_name_result) > 0 && mysqli_num_rows($name_result) == 0) {
        $alter_name_sql = "ALTER TABLE categories CHANGE category_name name VARCHAR(100) NOT NULL";
        mysqli_query($connect, $alter_name_sql);
    }
}
*/

// Now get the first category
$sql_category = "SELECT category_id FROM categories ORDER BY category_id ASC LIMIT 1";
$category_result = mysqli_query($connect, $sql_category);
$category_row = mysqli_fetch_assoc($category_result);
$premium_category_id = isset($category_row['category_id']) ? $category_row['category_id'] : 1;

// Kiểm tra xem cột category_id có tồn tại trong bảng products không
$sql_check_category_column = "SHOW COLUMNS FROM products LIKE 'category_id'";
$result_category_column = mysqli_query($connect, $sql_check_category_column);

if (mysqli_num_rows($result_category_column) == 0) {
    // Nếu cột chưa tồn tại, thêm vào
    $sql_add_category_column = "ALTER TABLE products ADD COLUMN category_id INT DEFAULT NULL";
    mysqli_query($connect, $sql_add_category_column);
    
    // Cập nhật một số sản phẩm vào danh mục này để hiển thị
    $sql_update_category = "UPDATE products SET category_id = $premium_category_id WHERE category_id IS NULL LIMIT 5";
    mysqli_query($connect, $sql_update_category);
}

$sqlPt = "SELECT count(product_id) as total FROM products WHERE category_id = $premium_category_id";

$arrayNum = mysqli_query($connect, $sqlPt);
$row = mysqli_fetch_assoc($arrayNum);
$total_records = $row['total'];

$current_page = isset($_GET['pages']) ? intval($_GET['pages']) : 1;
$limit = 6;

$total_page = ceil($total_records / $limit);

// Đảm bảo current_page luôn nằm trong khoảng hợp lệ
if ($current_page > $total_page) {
    $current_page = $total_page;
} else if ($current_page < 1) {
    $current_page = 1;
}

// Đảm bảo start luôn là số không âm
$start = max(0, ($current_page - 1) * $limit);

// Sử dụng LIMIT và OFFSET riêng biệt trong câu truy vấn để tránh lỗi
$sql = "SELECT 
        products.*,
        b.brand_name,
        c.category_name,
        m.name as manufacturer_name
        FROM `products`
        LEFT JOIN brands b on products.brand_id = b.brand_id
        LEFT JOIN categories c on products.category_id = c.category_id
        LEFT JOIN manufacturers m on products.manufacturer_id = m.id
        WHERE products.category_id = $premium_category_id
        ORDER BY products.product_id DESC
        LIMIT $limit OFFSET $start";
$result = mysqli_query($connect, $sql);

// Nếu không có sản phẩm nào, hiển thị tất cả sản phẩm
if (mysqli_num_rows($result) == 0) {
    $sql = "SELECT 
        products.*,
        b.brand_name,
        c.category_name,
        m.name as manufacturer_name
        FROM `products`
        LEFT JOIN brands b on products.brand_id = b.brand_id
        LEFT JOIN categories c on products.category_id = c.category_id
        LEFT JOIN manufacturers m on products.manufacturer_id = m.id
        ORDER BY products.product_id DESC
        LIMIT $limit OFFSET $start";
    $result = mysqli_query($connect, $sql);
}

// Debug để kiểm tra
// echo "Current page: $current_page, Start: $start, Limit: $limit";

?>

<style>
.category__manufacturer {
    text-align: center;
    margin-bottom: 5px;
}
.category__manufacturer span {
    font-size: 14px;
    color: #555;
    display: inline-block;
    background-color: #e7f0fd;
    padding: 2px 8px;
    border-radius: 10px;
}
</style>

<div class="grid">
    <div class="row row-category">
        <?php foreach ($result as $each) : ?>
            <div class="col col-3 col-2-mt mt-10">
                <div class="category">
                    <a href="view_detail.php?id=<?php echo $each['product_id'] ?>" class="category_link">
                        <div class="category__img">
                            <img src="admin/products/uploads/<?php echo $each['image_path'] ?>" alt="">
                        </div>
                        <h4 class="category__name"><?php echo $each['product_name'] ?></h4>
                        <?php if (!empty($each['brand_name'])): ?>
                        <div class="category__brand">
                            <span><?php echo htmlspecialchars($each['brand_name']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($each['manufacturer_name'])): ?>
                        <div class="category__manufacturer">
                            <span>Nhà SX: <?php echo htmlspecialchars($each['manufacturer_name']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="category__price">
                            <p> Giá bán:</p>
                            <span class="category__money">
                                <?php echo currency_format($each['price']) ?>
                            </span>
                        </div>
                    </a>
                    <div class="category-btn">
                        <ul class="category-cart">
                            <li>
                                <a href="view_detail.php?id=<?php echo $each['product_id'] ?>">
                                    Mua ngay
                                </a>
                            </li>
                            <li>
                                <?php if (!empty($_SESSION['id'])) { ?>
                                    <a onclick="return Suc()" href="add_to_cart.php?id=<?php echo $each['product_id'] ?>">
                                        Thêm vào giỏ hàng
                                    </a>
                                <?php } ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
    <div class="row">
        <div class="row_page">
            <nav>
                <ul class="pagination">
                    <?php if ($current_page > 1 && $total_page > 1) { ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?pages=<?php echo ($current_page - 1) ?>">Prev</a>
                        </li>
                    <?php } ?>
                    <?php for ($i = 1; $i <= $total_page; $i++) { ?>
                        <li class="page-item">
                            <?php if ($i == $current_page) { ?>
                                <span class="page-link text-muted"><?php echo  $i ?></span>
                            <?php } else { ?>
                                <a class="page-link" href="index.php?pages=<?php echo  $i ?>"><?php echo  $i ?></a>
                            <?php } ?>
                        </li>
                    <?php } ?>
                    <?php if ($current_page < $total_page && $total_page > 1) { ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?pages=<?php echo ($current_page + 1) ?>">Next</a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        </div>
    </div>
</div>