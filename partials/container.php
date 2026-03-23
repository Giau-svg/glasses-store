<!-- giua -->
<?php
require 'admin/root.php';

$sqlPt = "SELECT count(product_id) as total FROM products";
$arrayNum = mysqli_query($connect, $sqlPt);
$row = mysqli_fetch_assoc($arrayNum);
$total_records = $row['total'];

$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 6;

$total_page = ceil($total_records / $limit);

if ($current_page > $total_page) {
    $current_page = $total_page;
} else if ($current_page < 1) {
    $current_page = 1;
}

$start = ($current_page - 1) * $limit;

$sql = "SELECT products.*, brands.brand_name, m.name as manufacturer_name
    FROM `products`
    LEFT JOIN brands ON products.brand_id = brands.brand_id
    LEFT JOIN manufacturers m ON products.manufacturer_id = m.id
    ORDER BY products.product_id DESC
    LIMIT $limit OFFSET $start";
$result = mysqli_query($connect, $sql);

?>

<div class="container" style="background-color: #ffffff; padding: 0; width: 100%; margin-top: 20px;">
    <div class="grid_full-width" style="max-width: 1400px; margin: 0 auto; padding: 0 15px; width: 100%;">
        <div class="grid_full-width content" style="background-color: #ffffff; display: flex; flex-wrap: wrap; width: 100%;">
            <!-- Main Content Section -->
            <div class="content__brands" style="flex: 1; min-width: 70%; padding-right: 20px; padding-top: 10px;">
                
                <!-- SECTION 1: Featured Slider -->
                <section class="section-slider" style="margin-bottom: 50px; position: relative;">
                    <div class="section-title" style="text-align: center; margin-bottom: 20px;">
                        <h2 style="color: #333; font-size: 28px; position: relative; display: inline-block; padding-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">
                            Mắt kính thời trang - Nâng tầm phong cách
                            <span style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 3px; background-color: #ffa500;"></span>
                        </h2>
                    </div>
                    <div class="slider-container" style="border-radius: 8px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px; position: relative; z-index: 1; height: auto; max-height: none;">
                <?php include './partials/slider.php' ?>
                    </div>
                </section>
                
                <!-- SECTION 2: Chuẩn thị lực – Đúng xu hướng -->
                <section class="section-vision-trend" style="margin: 40px 0; padding: 40px 0; background: #f5f5f5; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 320px; padding: 20px 40px; display: flex; flex-direction: column; justify-content: center;">
                        <h2 style="color: #222; font-size: 2.2rem; font-weight: 700; margin-bottom: 18px; text-transform: uppercase; letter-spacing: 1px;">Chuẩn thị lực – Đúng xu hướng</h2>
                        <p style="color: #555; font-size: 1.1rem; line-height: 1.7;">Khám phá các sản phẩm kính mắt giúp bảo vệ thị lực tối ưu, đồng thời bắt kịp xu hướng thời trang hiện đại. Sự kết hợp hoàn hảo giữa công nghệ và phong cách dành cho bạn!</p>
                    </div>
                    <div style="flex: 1; min-width: 320px; display: flex; align-items: center; justify-content: center; padding: 20px;">
                        <img src="public/img/z6579214741814_7cec7447be87a52f131790e25e29f27b.jpg" alt="Chuẩn thị lực – Đúng xu hướng" style="max-width: 100%; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08);">
                                        </div>
                </section>
                
                <!-- SECTION 3: Hợp tác cùng các thương hiệu kính mắt hàng đầu thế giới -->
                <section class="section-brand-collab" style="margin: 40px 0; padding: 40px 0; background: #f5f5f5; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 320px; display: flex; align-items: center; justify-content: center; padding: 20px;">
                        <img src="public/img/z6579214764760_45c0c06b50e912ed106b51b2bfa94bfa.jpg" alt="Hợp tác thương hiệu kính mắt hàng đầu" style="max-width: 100%; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08);">
                    </div>
                    <div style="flex: 1; min-width: 320px; padding: 20px 40px; display: flex; flex-direction: column; justify-content: center;">
                        <h2 style="color: #222; font-size: 2.2rem; font-weight: 700; margin-bottom: 18px; text-transform: uppercase; letter-spacing: 1px;">Hợp tác cùng các thương hiệu kính mắt hàng đầu thế giới</h2>
                        <p style="color: #555; font-size: 1.1rem; line-height: 1.7;">Chúng tôi tự hào là đối tác của các thương hiệu kính mắt nổi tiếng toàn cầu, mang đến cho khách hàng những sản phẩm chất lượng, chính hãng và luôn cập nhật xu hướng mới nhất.</p>
                    </div>
                </section>
                
                <!-- SECTION 4: Brands Showcase -->
                <!-- Removed as per instructions -->
                
                <!-- SECTION 5: Khám phá các thương hiệu nổi bật (đồng bộ trắng/đen) -->
                <section class="section-featured-brands" style="margin: 40px 0; padding: 40px 0; background: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid #ededed;">
                    <div style="text-align: center; margin-bottom: 32px;">
                        <h2 style="color: #222; font-size: 2.2rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Khám phá các thương hiệu kính mắt nổi bật</h2>
                        <p style="color: #555; font-size: 1.1rem; margin-top: 10px;">Chọn thương hiệu bạn yêu thích để xem các sản phẩm chính hãng, mới nhất!</p>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 40px; align-items: center;">
                        <a href="view_brand.php?id=1" class="brand-logo-link">
                            <img src="public/img/OIP.jpg" alt="RayBan" class="brand-logo-img">
                            <span class="brand-logo-name">RayBan</span>
                        </a>
                        <a href="view_brand.php?id=2" class="brand-logo-link">
                            <img src="public/img/OIP (1).jpg" alt="Oakley" class="brand-logo-img">
                            <span class="brand-logo-name">Oakley</span>
                        </a>
                        <a href="view_brand.php?id=3" class="brand-logo-link">
                            <img src="public/img/R.jpg" alt="Gucci" class="brand-logo-img">
                            <span class="brand-logo-name">Gucci</span>
                        </a>
                        <a href="view_brand.php?id=4" class="brand-logo-link">
                            <img src="public/img/OIP (2).jpg" alt="Prada" class="brand-logo-img">
                            <span class="brand-logo-name">Prada</span>
                        </a>
                        <a href="view_brand.php?id=5" class="brand-logo-link">
                            <img src="public/img/logo-essilor-color.jpg" alt="Essilor" class="brand-logo-img">
                            <span class="brand-logo-name">Essilor</span>
                        </a>
                    </div>
                        <style>
                        .brand-logo-link {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            text-decoration: none;
                            transition: box-shadow 0.3s, transform 0.3s;
                        }
                        .brand-logo-img {
                            width: 110px;
                            height: 110px;
                            object-fit: contain;
                            border-radius: 50%;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                            background: #fff;
                            margin-bottom: 12px;
                            border: 1.5px solid #ededed;
                            transition: box-shadow 0.3s, transform 0.3s;
                        }
                        .brand-logo-link:hover .brand-logo-img {
                            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
                            transform: translateY(-6px) scale(1.07);
                            border-color: #222;
                        }
                        .brand-logo-name {
                            color: #222;
                            font-weight: 600;
                            font-size: 1.1rem;
                            margin-top: 2px;
                        }
                        </style>
                </section>
                
                </div>
            
            <div class="slidebar-container" style="flex: 0 0 25%; min-width: 250px; padding-left: 20px; padding-top: 10px;">
                <?php
                // Kiểm tra xem có phải trang search không
                $is_search_page = strpos($_SERVER['REQUEST_URI'], 'search.php') !== false;
                
                if ($is_search_page) {
                    include './partials/slidebar_filter.php';
                } else {
                    include './partials/slidebar_category.php';
                }
                ?>
            </div>
        </div>
        
        <div class="footer" style="background: #fff; color: #222; border-radius: 0; margin-top: 40px; padding: 48px 0 28px; box-shadow: 0 -2px 16px rgba(0,0,0,0.04); width: 100%; border-top: 1px solid #ededed;">
            <div class="grid_full-width">
                <div class="grid">
                    <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -15px; justify-content: center; gap: 32px;">
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 240px; padding: 0 15px; margin-bottom: 20px; text-align: center;">
                            <div class="logo logo-bottom ml-mobi" style="margin-bottom: 18px;">
                                <a href="index.php" style="font-size: 32px; color: #222; font-weight: bold; text-transform: uppercase; display: flex; align-items: center; justify-content: center; text-decoration: none; letter-spacing: 2px;">
                                    <i class="fas fa-glasses" style="color: #ffa500; margin-right: 12px; font-size: 32px;"></i>
                                    EYE<span style="color: #ffa500;">GLASSES</span>
                                </a>
                            </div>
                            <div class="footer__text ml-mobi" style="color: #666; line-height: 1.7; font-size: 15px; max-width: 340px; margin: 0 auto;">
                                <p>EyeGlasses chuyên cung cấp các loại mắt kính thời trang, kính mát và tròng kính chính hãng. Mua sắm dễ dàng, giao hàng nhanh, khuyến mãi hấp dẫn mỗi ngày!</p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 200px; padding: 0 15px; margin-bottom: 20px; text-align: center;">
                            <div class="footer__about" style="margin-bottom: 12px;">
                                <h3 style="color: #222; font-size: 17px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 8px;">Địa chỉ</h3>
                            </div>
                            <div class="footer__text" style="color: #666; font-size: 15px;">
                                <p>
                                    <i class="fas fa-map-marker-alt" style="color: #ffa500; margin-right: 8px;"></i> Saigon University
                                </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 200px; padding: 0 15px; margin-bottom: 20px; text-align: center;">
                            <div class="footer__about" style="margin-bottom: 12px;">
                                <h3 style="color: #222; font-size: 17px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 8px;">Dịch vụ</h3>
                            </div>
                            <div class="footer__text" style="color: #666; font-size: 15px;">
                                <p>
                                    <i class="fas fa-glasses" style="color: #ffa500; margin-right: 8px;"></i> Kính mắt thời trang chính hãng
                                </p>
                                <p>
                                    <i class="fas fa-truck" style="color: #ffa500; margin-right: 8px;"></i> Giao hàng toàn quốc
                                </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 200px; padding: 0 15px; margin-bottom: 20px; text-align: center;">
                            <div class="footer__about" style="margin-bottom: 12px;">
                                <h3 style="color: #222; font-size: 17px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 8px;">Liên hệ</h3>
                            </div>
                            <div class="footer__text" style="color: #666; font-size: 15px;">
                                <p><i class="fas fa-phone-alt" style="color: #ffa500; margin-right: 8px;"></i> <a href="tel:+84901234567" style="color: #222; text-decoration: none; font-weight: 500;">(+84) 901234567</a></p>
                                <p><i class="fas fa-envelope" style="color: #ffa500; margin-right: 8px;"></i> <a href="mailto:nhom13@saigon.edu.vn" style="color: #222; text-decoration: none; font-weight: 500;">nhom13@saigon.edu.vn</a></p>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: center; color: #aaa; font-size: 13px; margin-top: 32px; letter-spacing: 1px;">
                        © 2024 EyeGlasses. All rights reserved.
                    </div>
                </div>
            </div>
            <style>
                .footer__about h3 {
                    font-family: 'Roboto', sans-serif;
                    font-weight: 700;
                    letter-spacing: 1.5px;
                }
                .footer__text i {
                    vertical-align: middle;
                }
                @media (max-width: 900px) {
                    .footer .row { flex-direction: column; gap: 0; }
                    .footer .col { min-width: 180px !important; margin-bottom: 24px !important; }
                }
            </style>
        </div>
    </div>
</div>

<style>
/* Base styles */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body, html {
    color: #333;
    background-color: #ffffff;
    font-family: 'Roboto', sans-serif;
}

/* Section styling */
section {
    position: relative;
    transition: all 0.3s ease;
}

section:hover {
    transform: translateY(-5px);
}

.section-title {
    text-align: center;
    margin-bottom: 30px;
}

.section-title h2 {
    position: relative;
    display: inline-block;
    margin-bottom: 15px;
}

.section-title h2::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background-color: #ffa500;
}

/* Product card styling */
.product-card {
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    height: 100%;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.product-img-container img {
    transition: all 0.5s ease;
}

.product-card:hover .product-img-container img {
    transform: scale(1.05);
}

.btn-buy-now {
    background-color: #ffa500 !important;
    color: #fff !important;
    transition: all 0.3s ease !important;
}

.btn-buy-now:hover {
    background-color: #ff8c00 !important;
    transform: translateY(-2px) !important;
}

.btn-add-cart {
    background-color: #f8f8f8 !important;
    color: #333 !important;
    transition: all 0.3s ease !important;
}

.btn-add-cart:hover {
    background-color: #f0f0f0 !important;
    color: #333 !important;
    transform: translateY(-2px) !important;
}

.product-name {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (max-width: 1024px) {
    .col.col-3.col-2-mt {
        width: calc(33.333% - 30px) !important;
    }
}

@media (max-width: 768px) {
    .col.col-3.col-2-mt {
        width: calc(50% - 30px) !important;
    }
}

@media (max-width: 480px) {
    .col.col-3.col-2-mt {
        width: calc(100% - 30px) !important;
    }
}
</style>