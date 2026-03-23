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
?>

<div class="content__sidebar">
    <div class="sidebar__category" style="min-width: 0; max-width: 100%;">
        <div class="sidebar__header">
            <i class="fas fa-filter category-icon"></i>
            <h3 class="sidebar__title">Lọc sản phẩm</h3>
        </div>
        <form method="get" action="search.php" style="padding: 0 10px 10px 10px;">
            <input type="hidden" name="search" value="<?php echo isset($search_products) ? htmlspecialchars($search_products) : '' ?>">
            <div class="sidebar__filter-group">
                <label>Khoảng giá:</label>
                <select name="price_range" class="sidebar__filter-select" style="width: 100%;">
                    <option value="">Tất cả</option>
                    <option value="1" <?php if(isset($price_range) && $price_range=='1') echo 'selected'; ?>>Dưới 1 triệu</option>
                    <option value="2" <?php if(isset($price_range) && $price_range=='2') echo 'selected'; ?>>1 - 3 triệu</option>
                    <option value="3" <?php if(isset($price_range) && $price_range=='3') echo 'selected'; ?>>3 - 5 triệu</option>
                    <option value="4" <?php if(isset($price_range) && $price_range=='4') echo 'selected'; ?>>Trên 5 triệu</option>
                </select>
            </div>
            <div class="sidebar__filter-group">
                <label>Thương hiệu:</label>
                <select name="brand_id" class="sidebar__filter-select" style="width: 100%;">
                    <option value="">Tất cả</option>
                    <?php
                    $brands = mysqli_query($connect, "SELECT * FROM brands");
                    while($b = mysqli_fetch_assoc($brands)) {
                        $selected = (isset($brand_id) && $brand_id == $b['brand_id']) ? 'selected' : '';
                        echo '<option value="'.$b['brand_id'].'" '.$selected.'>'.$b['brand_name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="sidebar__filter-group">
                <label>Danh mục:</label>
                <select name="category_id" class="sidebar__filter-select" style="width: 100%;">
                    <option value="">Tất cả</option>
                    <?php
                    $categories = mysqli_query($connect, "SELECT * FROM categories");
                    while($c = mysqli_fetch_assoc($categories)) {
                        $selected = (isset($category_id) && $category_id == $c['category_id']) ? 'selected' : '';
                        echo '<option value="'.$c['category_id'].'" '.$selected.'>'.$c['category_name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="sidebar__filter-btn" style="width: 100%;">Lọc</button>
        </form>
        <div class="sidebar__footer">
            <p>Lọc nhanh sản phẩm theo nhu cầu của bạn</p>
        </div>
    </div>
</div>

<style>
    .sidebar__category {
        margin-top: 20px;
        padding: 20px;
        background: linear-gradient(145deg, #f8f9fa, #e9ecef);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.4);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .sidebar__category:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    
    .sidebar__header {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e63946;
    }
    
    .sidebar__brand {
        margin-top: 8px;
        font-size: 16px;
        font-weight: 600;
        color: #e63946;
    }
    
    .category-icon {
        font-size: 24px;
        margin-right: 10px;
        color: #e63946;
    }
    
    .sidebar__title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin: 0;
        background: linear-gradient(to right, #e63946, #457b9d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
    }
    
    .sidebar__list {
        list-style: none;
        padding: 0;
    }
    
    .sidebar__item {
        margin-bottom: 12px;
        transition: transform 0.2s ease;
    }
    
    .sidebar__item:hover {
        transform: translateX(5px);
    }
    
    /* Mục active */
    .sidebar__item.active {
        transform: translateX(5px);
    }
    
    .sidebar__link {
        display: flex;
        align-items: center;
        padding: 14px 15px;
        font-size: 16px;
        font-weight: 500;
        color: #444;
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border-left: 4px solid transparent;
    }
    
    .sidebar__link:hover {
        color: #e63946;
        border-left: 4px solid #e63946;
        background-color: #f8f9fa;
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }
    
    /* Link active */
    .sidebar__link.active {
        color: #e63946;
        border-left: 4px solid #e63946;
        background-color: #f8f9fa;
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        font-weight: bold;
    }
    
    .sidebar__link i {
        margin-right: 10px;
        font-size: 12px;
        transition: transform 0.2s ease;
    }
    
    .sidebar__link:hover i,
    .sidebar__link.active i {
        transform: translateX(3px);
    }
    
    .sidebar__footer {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px dashed #ddd;
        text-align: center;
        font-size: 14px;
        color: #666;
        font-style: italic;
    }
</style>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">