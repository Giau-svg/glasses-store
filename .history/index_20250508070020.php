<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Biến kiểm soát việc tự động thêm dữ liệu mẫu
$auto_insert_sample_data = false; // Đặt thành false để vô hiệu hóa tính năng tự động thêm dữ liệu mẫu

// Kiểm tra và thêm dữ liệu mẫu nếu không có sản phẩm nào
require './admin/root.php';

// Kiểm tra số lượng sản phẩm
$check_products = mysqli_query($connect, "SELECT COUNT(*) as count FROM products");
$product_count = mysqli_fetch_assoc($check_products)['count'];

// Nếu không có sản phẩm nào và cho phép thêm dữ liệu mẫu
if ($product_count == 0 && $auto_insert_sample_data) {
    // Kiểm tra xem có danh mục và thương hiệu chưa
    $check_categories = mysqli_query($connect, "SELECT COUNT(*) as count FROM categories");
    $category_count = mysqli_fetch_assoc($check_categories)['count'];
    
    if ($category_count == 0) {
        // Thêm danh mục mẫu
        mysqli_query($connect, "INSERT INTO categories (category_name, description) VALUES 
            ('Kính mát', 'Các loại kính mát thời trang'),
            ('Gọng kính', 'Các loại gọng kính đa dạng'),
            ('Tròng kính', 'Các loại tròng kính chất lượng')");
    }
    
    $check_brands = mysqli_query($connect, "SELECT COUNT(*) as count FROM brands");
    $brand_count = mysqli_fetch_assoc($check_brands)['count'];
    
    if ($brand_count == 0) {
        // Thêm thương hiệu mẫu
        mysqli_query($connect, "INSERT INTO brands (brand_name, category_id, address, phone, logo) VALUES 
            ('Ray-Ban', 1, 'USA', '123456789', 'rayban.jpg'),
            ('Oakley', 1, 'USA', '987654321', 'oakley.jpg'),
            ('Essilor', 3, 'France', '456789123', 'essilor.jpg')");
    }
    
    // Lấy ID của danh mục và thương hiệu
    $categories = mysqli_query($connect, "SELECT category_id FROM categories LIMIT 3");
    $category_ids = [];
    while ($row = mysqli_fetch_assoc($categories)) {
        $category_ids[] = $row['category_id'];
    }
    
    $brands = mysqli_query($connect, "SELECT brand_id FROM brands LIMIT 3");
    $brand_ids = [];
    while ($row = mysqli_fetch_assoc($brands)) {
        $brand_ids[] = $row['brand_id'];
    }
    
    // Thêm sản phẩm mẫu
    if (!empty($category_ids) && !empty($brand_ids)) {
        $sample_products = [
            [
                'product_name' => 'Kính mát Ray-Ban Aviator',
                'image_path' => '1650700964.jpg',
                'price' => 2500000,
                'cost_price' => 1800000,
                'description' => 'Kính mát Ray-Ban Aviator thời trang, chống tia UV',
                'stock_quantity' => 15,
                'category_id' => $category_ids[0],
                'brand_id' => $brand_ids[0]
            ],
            [
                'product_name' => 'Gọng kính Kim loại',
                'image_path' => '1650701034.jpg',
                'price' => 1200000,
                'cost_price' => 800000,
                'description' => 'Gọng kính kim loại cao cấp, nhẹ và bền',
                'stock_quantity' => 20,
                'category_id' => $category_ids[1],
                'brand_id' => $brand_ids[1]
            ],
            [
                'product_name' => 'Tròng kính Essilor Crizal',
                'image_path' => '1650701176.jpg',
                'price' => 1800000,
                'cost_price' => 1200000,
                'description' => 'Tròng kính chống xước, chống chói, bảo vệ mắt',
                'stock_quantity' => 25,
                'category_id' => $category_ids[2],
                'brand_id' => $brand_ids[2]
            ],
            [
                'product_name' => 'Kính mát Oakley Holbrook',
                'image_path' => '1650701347.jpg',
                'price' => 3200000,
                'cost_price' => 2500000,
                'description' => 'Kính mát Oakley thể thao, chống tia UV',
                'stock_quantity' => 10,
                'category_id' => $category_ids[0],
                'brand_id' => $brand_ids[1]
            ],
            [
                'product_name' => 'Gọng kính acetate',
                'image_path' => '1650701382.jpg',
                'price' => 1500000,
                'cost_price' => 1000000,
                'description' => 'Gọng kính nhựa acetate cao cấp',
                'stock_quantity' => 18,
                'category_id' => $category_ids[1],
                'brand_id' => $brand_ids[0]
            ],
            [
                'product_name' => 'Tròng kính đổi màu',
                'image_path' => '1651137042.jpg',
                'price' => 2200000,
                'cost_price' => 1600000,
                'description' => 'Tròng kính đổi màu khi ra nắng',
                'stock_quantity' => 12,
                'category_id' => $category_ids[2],
                'brand_id' => $brand_ids[2]
            ]
        ];
        
        foreach ($sample_products as $product) {
            mysqli_query($connect, "INSERT INTO products (product_name, image_path, price, cost_price, description, stock_quantity, category_id, brand_id) 
                VALUES (
                '{$product['product_name']}', 
                '{$product['image_path']}', 
                {$product['price']}, 
                {$product['cost_price']}, 
                '{$product['description']}', 
                {$product['stock_quantity']}, 
                {$product['category_id']}, 
                {$product['brand_id']})");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EYEGLASSES - Mắt Kính Thời Trang</title>
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
    <style>
        /* Custom color scheme styling */
        :root {
            --primary-color: #ffffff;
            --secondary-color: #ffa500;
            --light-color: #ffffff;
            --accent-color: #f8f8f8;
            --dark-color: #000000;
            --text-color: #333333;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            --transition: all 0.3s ease;
            --border-color: #efefef;
        }
        
        * {
            transition: var(--transition);
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: var(--light-color);
            color: var(--text-color);
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
        }
        
        /* Custom scrollbar styling */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background-color: var(--light-color);
        }
        
        ::-webkit-scrollbar-thumb {
            background-color: #e0e0e0;
            border-radius: 6px;
            border: 3px solid var(--light-color);
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background-color: #d0d0d0;
        }
        
        /* Main container */
        .wrapper {
            background-color: var(--light-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Make sure all containers are full width */
        .container, .grid_full-width, .header, .footer {
            width: 100%;
            max-width: 100%;
        }
        
        /* Main navigation */
        .menu {
            background-color: var(--primary-color);
            border-radius: 0;
            box-shadow: var(--box-shadow);
            width: 100%;
            border-bottom: 1px solid var(--border-color);
        }
        
        .menu-nav__link {
            color: var(--dark-color);
            font-weight: 500;
            padding: 15px 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            font-size: 14px;
        }
        
        .menu-nav__link:hover {
            background-color: transparent;
            color: var(--secondary-color);
        }
        
        .menu-nav__link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background-color: var(--secondary-color);
            transition: width 0.3s ease;
        }
        
        .menu-nav__link:hover::after {
            width: 70%;
        }
        
        /* Product items */
        .category {
            background-color: var(--accent-color);
            border-radius: 0;
            box-shadow: var(--box-shadow);
            transition: all 0.4s ease;
            height: 100%;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .category:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .category__img {
            padding: 20px;
            background-color: #fff;
            overflow: hidden;
            position: relative;
        }
        
        .category__img::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,165,0,0.05);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .category:hover .category__img::before {
            opacity: 1;
        }
        
        .category__img img {
            transition: transform 0.5s ease;
            max-height: 180px;
            object-fit: contain;
        }
        
        .category:hover .category__img img {
            transform: scale(1.08);
        }
        
        .category__name {
            color: var(--dark-color);
            font-weight: 600;
            margin: 10px 0;
            text-align: center;
            height: 48px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .category__price {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 15px 0;
        }
        
        .category__price p {
            color: var(--dark-color);
            margin-right: 5px;
            font-weight: 500;
        }
        
        .category__money {
            color: var(--secondary-color);
            font-size: 18px;
            font-weight: 700;
        }
        
        /* Buttons */
        .category-cart li a:first-child {
            background-color: var(--dark-color);
            color: var(--light-color);
            border-radius: 0;
            padding: 12px;
            font-weight: 600;
            text-align: center;
            display: block;
            transition: all 0.3s ease;
            box-shadow: none;
        }
        
        .category-cart li a:last-child {
            background-color: var(--secondary-color);
            color: var(--dark-color);
            border-radius: 0;
            padding: 12px;
            font-weight: 600;
            text-align: center;
            display: block;
            transition: all 0.3s ease;
            box-shadow: none;
        }
        
        .category-cart li a:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 12px rgba(0,0,0,0.1);
        }
        
        /* Headings */
        .brands__heading h1 {
            color: var(--dark-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 15px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 24px;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .brands__heading h1::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 2px;
            background-color: var(--secondary-color);
        }
        
        /* Sidebar */
        .slidebar {
            background-color: var(--accent-color);
            border-radius: 0;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .slidebar__heading {
            background-color: var(--primary-color);
            color: var(--dark-color);
            padding: 15px;
            font-weight: bold;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
        }
        
        .slidebar__list {
            padding: 10px;
        }
        
        .slidebar__item {
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .slidebar__item:hover {
            background-color: rgba(255,165,0,0.05);
        }
        
        .slidebar__icon {
            color: var(--secondary-color);
        }
        
        .slidebar__link {
            color: var(--dark-color);
            font-weight: 500;
            transition: all 0.3s ease;
            display: block;
        }
        
        .slidebar__link:hover {
            color: var(--secondary-color);
            text-decoration: none;
            transform: translateX(5px);
        }
        
        /* Header */
        .header-fixed {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            width: 100%;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header__btn {
            background-color: var(--secondary-color);
            color: var(--dark-color);
            border-radius: 0;
            padding: 8px 20px;
            font-weight: bold;
            border: none;
            transition: all 0.3s ease;
        }
        
        .header__btn:hover {
            background-color: #e69500;
            transform: translateY(-2px);
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: var(--dark-color);
            border-radius: 0;
            padding: 40px 0 20px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            margin-top: auto;
            width: 100%;
            border-top: 1px solid var(--border-color);
        }
        
        .footer a {
            color: var(--dark-color);
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: none;
            color: var(--secondary-color);
        }
        
        .footer__about h3 {
            color: var(--dark-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .footer__text i {
            color: var(--secondary-color);
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        
        /* Slider navigation */
        .slick-prev:before, 
        .slick-next:before {
            color: var(--secondary-color);
            font-size: 24px;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .category {
            animation: fadeIn 0.5s ease forwards;
        }
        
        /* Responsive styling */
        @media (max-width: 1200px) {
            .content__brands {
                padding-right: 0 !important;
            }
            
            .slidebar-container {
                padding-left: 0 !important;
            }
        }
        
        @media (max-width: 767px) {
            .brands__heading h1 {
                font-size: 20px;
            }
            
            .category {
                margin-bottom: 20px;
            }
            
            .footer {
                padding: 30px 15px 15px;
            }
        }
        
        /* Additional styles to ensure menu is visible */
        .menu-container {
            position: static !important;
            display: block !important;
            width: 100% !important;
            visibility: visible !important;
            z-index: 100 !important;
        }
        
        /* Ensure proper stacking and layout */
        .header-logo-section {
            position: relative !important;
            z-index: 120 !important;
        }
        
        body, html {
            overflow-x: hidden !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        /* Fix for menu visibility */
        .header-section {
            width: 100%;
            background-color: #ffffff;
            display: block;
            position: relative;
        }
        
        .logo-container {
            width: 100%;
            background-color: #ffffff;
            border-bottom: 1px solid #efefef;
            display: block;
            position: relative;
            z-index: 100;
        }
        
        .menu-container {
            width: 100%;
            background-color: #ffffff;
            border-bottom: 1px solid #efefef;
            display: block !important;
            position: relative;
            z-index: 90;
        }
        
        body {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }
    </style>
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
                <div class="grid_full-width content" style="margin-top: 0;">
        <?php include './partials/container.php' ?>
                </div>
            </div>
        </main>
        
        <?php include './partials/footer.php' ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="./public/js/js.js"></script>
    <script src="./public/js/slider.js"></script>
    <script src="./public/js/live-searchs.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        $(".btn-add-to-cart").click(function() {
            let id = $(this).data('id');
            // alert("Add to cart" + id);
            $.ajax({
              url: 'add_to_cart.php',
              type: 'GET',
              // dataType:'',
              data: {id},
            })
            .done(function(response){
              console.log("success");
              if(response == 1){
                alert('Thêm giỏ hàng thành công');
              }else {
                alert(response);  
              }
            });
        });
        
        // Add smooth scrolling
        $('a[href*="#"]').on('click', function(e) {
          e.preventDefault();
          $('html, body').animate(
            {
              scrollTop: $($(this).attr('href')).offset().top,
            },
            500,
            'linear'
          );
        });
      });
      function Suc() {
        return alert("Thêm giỏ hàng thành công!");
      }
    </script>
    <script>
      // Search form enhancement
      document.addEventListener('DOMContentLoaded', function() {
        // Regular header search effects
        const headerSearchForm = document.querySelector('.header__search form');
        const headerSearchInput = document.querySelector('.header__input');
        
        if (headerSearchForm && headerSearchInput) {
          headerSearchInput.addEventListener('focus', function() {
            headerSearchForm.style.boxShadow = '0 6px 15px rgba(255, 165, 0, 0.15)';
            headerSearchForm.style.transform = 'translateY(-2px)';
            headerSearchForm.style.borderColor = '#e69500';
          });
          
          headerSearchInput.addEventListener('blur', function() {
            if (headerSearchInput.value.trim() === '') {
              headerSearchForm.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
              headerSearchForm.style.transform = 'translateY(0)';
              headerSearchForm.style.borderColor = '#ffa500';
            }
          });
        }
        
        // Mobile search effects
        const mobileSearchForm = document.querySelector('.header__search-mobile');
        const mobileSearchInput = document.querySelector('.search-mobile__input');
        
        if (mobileSearchForm && mobileSearchInput) {
          mobileSearchInput.addEventListener('focus', function() {
            mobileSearchForm.style.boxShadow = '0 6px 15px rgba(255, 165, 0, 0.15)';
            mobileSearchForm.style.transform = 'translateY(-2px)';
            mobileSearchForm.style.borderColor = '#e69500';
          });
          
          mobileSearchInput.addEventListener('blur', function() {
            if (mobileSearchInput.value.trim() === '') {
              mobileSearchForm.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
              mobileSearchForm.style.transform = 'translateY(0)';
              mobileSearchForm.style.borderColor = '#ffa500';
            }
          });
        }
      });
    </script>
</body>
</html>