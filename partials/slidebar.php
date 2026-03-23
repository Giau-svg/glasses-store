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
    <!-- Danh mục sản phẩm -->
    <div class="sidebar__category">
        <div class="sidebar__header">
            <i class="fas fa-glasses category-icon"></i>
            <h3 class="sidebar__title">Danh mục sản phẩm</h3>
        </div>
        <ul class="sidebar__list">
            <?php 
            // Lấy tất cả danh mục
            $sql = "SELECT category_id, category_name FROM categories ORDER BY category_id ASC";
            $result_categories = mysqli_query($connect, $sql);
            
            foreach ($result_categories as $category) : 
                // Kiểm tra xem danh mục có active không
                $is_active = ($current_category_id == $category['category_id']);
            ?>
                <li class="sidebar__item <?php echo $is_active ? 'active' : ''; ?>">
                    <a href="view_brand.php?id=<?php echo $current_brand_id; ?>&category_id=<?php echo $category['category_id']; ?>" 
                       class="sidebar__link <?php echo $is_active ? 'active' : ''; ?>">
                        <i class="fas fa-chevron-right"></i>
                        <?php echo $category['category_name']; ?>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
        <div class="sidebar__footer">
            <p>Đa dạng mẫu mã - Chất lượng cao</p>
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