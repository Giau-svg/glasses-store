<?php
// Lấy danh mục 
$sql = "SELECT category_id, category_name, description FROM categories ORDER BY category_id ASC";
$result_categories = mysqli_query($connect, $sql);

// Lấy thương hiệu đang được chọn từ URL
$current_brand_id = isset($_GET['id']) ? $_GET['id'] : '';

// Lấy loại danh mục đang xem từ URL nếu có
$current_category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';

// Mảng ánh xạ ID danh mục
$category_links = [
    1 => 'sunglasses',     // Kính mát
    2 => 'eyeglasses',     // Kính cận
    3 => 'lens',           // Kính viễn
    4 => 'contact-lens',   // Kính đa tròng
    5 => 'fashion-glasses' // Kính thời trang
];

if (!isset($current_category_id)) $current_category_id = '';
?>

<div class="content__sidebar">
    <div class="sidebar__filter">
        <div class="sidebar__header">
            <i class="fas fa-filter filter-icon"></i>
            <h3 class="sidebar__title">Lọc sản phẩm</h3>
        </div>
        <form action="" method="GET" class="filter-form">
            <div class="filter-group">
                <label class="filter-label">Tên sản phẩm</label>
                <input type="text" name="search" class="filter-input" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </div>

            <div class="filter-group">
                <label class="filter-label">Giá</label>
                <select name="price" class="filter-select">
                    <option value="">Tất cả</option>
                    <option value="0-1000000" <?php echo (isset($_GET['price']) && $_GET['price'] == '0-1000000') ? 'selected' : ''; ?>>Dưới 1 triệu</option>
                    <option value="1000000-2000000" <?php echo (isset($_GET['price']) && $_GET['price'] == '1000000-2000000') ? 'selected' : ''; ?>>1 - 2 triệu</option>
                    <option value="2000000-3000000" <?php echo (isset($_GET['price']) && $_GET['price'] == '2000000-3000000') ? 'selected' : ''; ?>>2 - 3 triệu</option>
                    <option value="3000000-5000000" <?php echo (isset($_GET['price']) && $_GET['price'] == '3000000-5000000') ? 'selected' : ''; ?>>3 - 5 triệu</option>
                    <option value="5000000-10000000" <?php echo (isset($_GET['price']) && $_GET['price'] == '5000000-10000000') ? 'selected' : ''; ?>>5 - 10 triệu</option>
                    <option value="10000000-999999999" <?php echo (isset($_GET['price']) && $_GET['price'] == '10000000-999999999') ? 'selected' : ''; ?>>Trên 10 triệu</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Thương hiệu</label>
                <select name="brand" class="filter-select">
                    <option value="">Tất cả</option>
                    <?php
                    $sql_brands = "SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC";
                    $result_brands = mysqli_query($connect, $sql_brands);
                    while($brand = mysqli_fetch_assoc($result_brands)) {
                        $selected = (isset($_GET['brand']) && $_GET['brand'] == $brand['brand_id']) ? 'selected' : '';
                        echo '<option value="'.$brand['brand_id'].'" '.$selected.'>'.$brand['brand_name'].'</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Danh mục</label>
                <select name="category" class="filter-select">
                    <option value="">Tất cả</option>
                    <?php
                    $sql_categories = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
                    $result_categories = mysqli_query($connect, $sql_categories);
                    while($category = mysqli_fetch_assoc($result_categories)) {
                        $selected = (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'selected' : '';
                        echo '<option value="'.$category['category_id'].'" '.$selected.'>'.$category['category_name'].'</option>';
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="filter-button">
                <i class="fas fa-search"></i>
                Lọc sản phẩm
            </button>
        </form>
    </div>
</div>

<style>
.sidebar__filter {
    margin-top: 20px;
    padding: 20px;
    background: linear-gradient(145deg, #f8f9fa, #e9ecef);
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.4);
}

.filter-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-label {
    font-size: 14px;
    font-weight: 500;
    color: #666;
}

.filter-input,
.filter-select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.filter-input:focus,
.filter-select:focus {
    border-color: #e63946;
    box-shadow: 0 0 0 2px rgba(230, 57, 70, 0.1);
    outline: none;
}

.filter-button {
    margin-top: 10px;
    padding: 12px;
    background: linear-gradient(to right, #e63946, #457b9d);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.filter-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(230, 57, 70, 0.2);
}

.filter-button i {
    font-size: 16px;
}
</style>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">