<?php

// Thêm mục trang chủ và 5 thương hiệu cố định
$menu_items = [
    ['name' => 'Trang chủ', 'url' => 'index.php', 'brand' => 'home'],
    ['name' => 'Ray-Ban', 'url' => 'view_brand.php?brand=Ray-Ban', 'brand' => 'Ray-Ban'],
    ['name' => 'Oakley', 'url' => 'view_brand.php?brand=Oakley', 'brand' => 'Oakley'],
    ['name' => 'Gucci', 'url' => 'view_brand.php?brand=Gucci', 'brand' => 'Gucci'],
    ['name' => 'Prada', 'url' => 'view_brand.php?brand=Prada', 'brand' => 'Prada'],
    ['name' => 'Essilor', 'url' => 'view_brand.php?brand=Essilor', 'brand' => 'Essilor']
];

// Lấy thương hiệu đang được chọn từ URL
$current_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
// Kiểm tra nếu đang ở trang chủ
$is_home = basename($_SERVER['PHP_SELF']) === 'index.php' && empty($current_brand);
?>

<!-- Giua menu -->
<div style="width: 100%; border-bottom: 1px solid #efefef; margin: 0; padding: 0; background-color: #ffffff; position: static; display: block; visibility: visible;">
    <ul class="menu-nav" style="display: flex; justify-content: center; list-style-type: none; padding: 0; margin: 0; width: 100%; max-width: 1200px; margin: 0 auto;">
        <?php
        // Get the brand parameter from URL
        $current_page = basename($_SERVER['PHP_SELF']);
        $current_brand = isset($_GET['id']) ? $_GET['id'] : '';
        $is_home = ($current_page === 'index.php');
        ?>
        <li class="menu-nav__item" style="text-align: center; padding: 0 20px;">
            <a href="index.php" class="menu-nav__link <?php echo $is_home ? 'active' : ''; ?>" style="display: block; padding: 15px 0; color: <?php echo $is_home ? '#ffa500' : '#333'; ?>; font-weight: 500; text-decoration: none; transition: all 0.3s ease; position: relative; text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">
                Trang chủ
            </a>
        </li>
        <li class="menu-nav__item" style="text-align: center; padding: 0 20px;">
            <a href="view_brand.php?id=1" class="menu-nav__link <?php echo $current_brand == '1' ? 'active' : ''; ?>" style="display: block; padding: 15px 0; color: <?php echo $current_brand == '1' ? '#ffa500' : '#333'; ?>; font-weight: 500; text-decoration: none; transition: all 0.3s ease; position: relative; text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">
                Ray-Ban
            </a>
        </li>
        <li class="menu-nav__item" style="text-align: center; padding: 0 20px;">
            <a href="view_brand.php?id=2" class="menu-nav__link <?php echo $current_brand == '2' ? 'active' : ''; ?>" style="display: block; padding: 15px 0; color: <?php echo $current_brand == '2' ? '#ffa500' : '#333'; ?>; font-weight: 500; text-decoration: none; transition: all 0.3s ease; position: relative; text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">
                Oakley
            </a>
        </li>
        <li class="menu-nav__item" style="text-align: center; padding: 0 20px;">
            <a href="view_brand.php?id=3" class="menu-nav__link <?php echo $current_brand == '3' ? 'active' : ''; ?>" style="display: block; padding: 15px 0; color: <?php echo $current_brand == '3' ? '#ffa500' : '#333'; ?>; font-weight: 500; text-decoration: none; transition: all 0.3s ease; position: relative; text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">
                Gucci
            </a>
        </li>
        <li class="menu-nav__item" style="text-align: center; padding: 0 20px;">
            <a href="view_brand.php?id=4" class="menu-nav__link <?php echo $current_brand == '4' ? 'active' : ''; ?>" style="display: block; padding: 15px 0; color: <?php echo $current_brand == '4' ? '#ffa500' : '#333'; ?>; font-weight: 500; text-decoration: none; transition: all 0.3s ease; position: relative; text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">
                Prada
            </a>
        </li>
        <li class="menu-nav__item" style="text-align: center; padding: 0 20px;">
            <a href="view_brand.php?id=5" class="menu-nav__link <?php echo $current_brand == '5' ? 'active' : ''; ?>" style="display: block; padding: 15px 0; color: <?php echo $current_brand == '5' ? '#ffa500' : '#333'; ?>; font-weight: 500; text-decoration: none; transition: all 0.3s ease; position: relative; text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">
                Essilor
            </a>
        </li>
    </ul>
</div>

<style>
    .menu-nav__link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 2px;
        background-color: #ffa500;
        transition: width 0.3s ease;
    }
    
    .menu-nav__link:hover::after,
    .menu-nav__link.active::after {
        width: 70%;
    }
    
    .menu-nav__link:hover,
    .menu-nav__link.active {
        color: #ffa500 !important;
    }
    
    /* Ensure menu visibility */
    .menu-nav {
        position: static !important;
        visibility: visible !important;
        display: flex !important;
    }
    
    /* Responsive menu */
    @media (max-width: 767px) {
        .menu-nav {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .menu-nav__item {
            flex: 0 0 33.333%;
            padding: 0 10px;
        }
        
        .menu-nav__link {
            font-size: 12px !important;
            padding: 10px 0 !important;
        }
    }
    
    @media (max-width: 480px) {
        .menu-nav__item {
            flex: 0 0 50%;
        }
    }
</style>