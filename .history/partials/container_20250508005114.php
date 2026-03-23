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
                            Sản Phẩm Nổi Bật
                            <span style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 3px; background-color: #ffa500;"></span>
                        </h2>
                    </div>
                    <div class="slider-container" style="border-radius: 8px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px; position: relative; z-index: 1; height: auto; max-height: none;">
                <?php include './partials/slider.php' ?>
                    </div>
                </section>
                
                <!-- SECTION 2: Featured Products -->
                <section class="section-featured-products" style="width: 100%; position: relative; background-color: #f5f5f5; padding: 50px 30px; margin: 40px 0; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                    <div class="section-title" style="text-align: center; margin-bottom: 40px;">
                        <h2 style="color: #333; font-size: 28px; position: relative; display: inline-block; padding-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">
                            Mắt Kính Thời Trang Nổi Bật
                            <span style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 3px; background-color: #ffa500;"></span>
                        </h2>
                </div>
                    
                <div class="grid">
                        <div class="row row-category" style="display: flex; flex-wrap: wrap; margin: 0 -15px; justify-content: center;">
                        <?php foreach ($result as $each) : ?>
                                <div class="col col-3 col-2-mt mt-10" style="padding: 0 15px; margin-bottom: 30px; width: calc(25% - 30px); box-sizing: border-box;">
                                    <div class="product-card" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; transition: all 0.4s ease; height: 100%; display: flex; flex-direction: column; position: relative;">
                                        <a href="view_detail.php?id=<?php echo $each['product_id'] ?>" class="product-link" style="flex: 1; display: flex; flex-direction: column; text-decoration: none;">
                                            <div class="product-img-container" style="padding: 20px; display: flex; justify-content: center; align-items: center; background-color: #fff; height: 190px; overflow: hidden; position: relative;">
                                                <img src="admin/products/uploads/<?php echo $each['image_path'] ?>" alt="<?php echo htmlspecialchars($each['product_name']) ?>" style="max-height: 160px; max-width: 100%; object-fit: contain; transition: all 0.5s ease;">
                                        </div>
                                            <div class="product-info" style="padding: 15px 20px; flex: 1; display: flex; flex-direction: column; position: relative; z-index: 1;">
                                                <h4 class="product-name" style="color: #333; font-size: 16px; margin-bottom: 10px; font-weight: 500; height: 40px; overflow: hidden; text-align: center;"><?php echo $each['product_name'] ?></h4>
                                        <?php if (!empty($each['brand_name'])): ?>
                                                <div class="product-brand" style="text-align: center; margin-bottom: 8px;">
                                                    <span style="background-color: #f8f8f8; color: #333; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-style: normal; font-weight: 400;"><?php echo htmlspecialchars($each['brand_name']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($each['manufacturer_name'])): ?>
                                                <div class="product-manufacturer" style="text-align: center; margin-bottom: 10px;">
                                                    <span style="background-color: #f8f8f8; color: #333; padding: 5px 12px; border-radius: 0; font-size: 13px;">Nhà SX: <?php echo htmlspecialchars($each['manufacturer_name']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                                <div class="product-price" style="display: flex; justify-content: center; align-items: center; margin: 10px 0; margin-top: auto;">
                                                    <span class="price-value" style="color: #ffa500; font-size: 18px; font-weight: 600;">
                                                <?php echo currency_format($each['price']) ?>
                                            </span>
                                                </div>
                                        </div>
                                    </a>
                                        <!-- Always visible buttons instead of overlay -->
                                        <div class="product-actions" style="padding: 0 15px 15px; text-align: center;">
                                            <a onclick="return Suc()" href="add_to_cart.php?id=<?php echo $each['product_id'] ?>" class="btn-buy-now" style="background-color: #ffa500; color: #fff; text-align: center; padding: 10px 0; border-radius: 4px; font-weight: 500; display: block; transition: all 0.3s ease; text-decoration: none; margin-bottom: 8px;">
                                                    Thêm vào giỏ hàng
                                            </a>
                                            <?php if (!empty($_SESSION['id'])) { ?>
                                                <a onclick="return Suc()" href="add_to_cart.php?id=<?php echo $each['product_id'] ?>" class="btn-add-cart" style="background-color: #f8f8f8; color: #333; text-align: center; padding: 10px 0; border-radius: 4px; font-weight: 500; display: block; transition: all 0.3s ease; text-decoration: none; border: 1px solid #e0e0e0;">
                                                    <i class="fas fa-shopping-cart" style="margin-right: 5px;"></i> Thêm vào giỏ
                                                    </a>
                                                <?php } ?>
                                        </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                        <div class="pagination-container" style="display: flex; justify-content: center; margin: 30px 0;">
                    <?php include './partials/pagination_mb.php' ?>
                </div>
                    </div>
                </section>
                
                <!-- SECTION 3: Brands Showcase -->
                <section class="section-brands" style="margin-top: 50px; padding: 40px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid #efefef;">
                    <div class="section-title" style="text-align: center; margin-bottom: 30px;">
                        <h2 style="color: #333; font-size: 28px; position: relative; display: inline-block; padding-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">
                            Nhà Sản Xuất Kính Mắt
                            <span style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 3px; background-color: #ffa500;"></span>
                        </h2>
                    </div>
                    <div class="manufacturers-container" style="background-color: #ffffff; padding: 20px;">
                        <?php
                        // Lấy tổng số nhà sản xuất
                        $sqlCount = "SELECT COUNT(id) as total FROM manufacturers";
                        $resultCount = mysqli_query($connect, $sqlCount);
                        $rowCount = mysqli_fetch_assoc($resultCount);
                        $total_records = $rowCount['total'];

                        // Thiết lập phân trang - giảm limit xuống để dễ dàng tạo nhiều trang hơn
                        $current_page = isset($_GET['man_page']) ? intval($_GET['man_page']) : 1;
                        $limit = 4; // Giảm xuống 4 nhà sản xuất mỗi trang (thay vì 8)
                        $total_page = ceil($total_records / $limit);

                        // Đảm bảo current_page nằm trong khoảng hợp lệ
                        if ($current_page > $total_page) {
                            $current_page = $total_page;
                        } else if ($current_page < 1) {
                            $current_page = 1;
                        }

                        // Tính vị trí bắt đầu lấy dữ liệu
                        $start = max(0, ($current_page - 1) * $limit);

                        // Lấy danh sách nhà sản xuất
                        $sql = "SELECT id, name, description, created_at 
                                FROM manufacturers 
                                ORDER BY id DESC 
                                LIMIT $limit OFFSET $start";
                        $result = mysqli_query($connect, $sql);

                        // Kiểm tra nếu không có nhà sản xuất nào
                        if (mysqli_num_rows($result) == 0) {
                            echo '<div class="alert alert-info">Chưa có nhà sản xuất nào trong hệ thống.</div>';
                        } else {
                        ?>

                        <div class="grid" style="width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 15px;">
                            <div class="manufacturers__heading">
                                <p>Đối tác chính thức cung cấp kính mắt chất lượng cao</p>
                            </div>
                            
                            <div class="row manufacturers-list" style="display: flex; flex-wrap: wrap; margin: 0 -15px; justify-content: center;">
                                <?php foreach($result as $manufacturer): ?>
                                <div class="col col-3 col-2-mt mt-10" style="padding: 0 15px; margin-bottom: 20px; width: calc(25% - 30px); box-sizing: border-box;">
                                    <div class="manufacturer-card">
                                        <div class="manufacturer-card__inner">
                                            <div class="manufacturer-card__logo">
                                                <i class="fas fa-industry"></i>
                                            </div>
                                            <h3 class="manufacturer-card__name"><?php echo htmlspecialchars($manufacturer['name']); ?></h3>
                                            <div class="manufacturer-card__desc">
                                                <?php echo !empty($manufacturer['description']) ? htmlspecialchars($manufacturer['description']) : 'Nhà sản xuất kính mắt chính hãng'; ?>
                                            </div>
                                            <a href="view_manufacturer.php?id=<?php echo $manufacturer['id']; ?>" class="manufacturer-card__link" style="background-color: #ffa500; width: 100%; box-sizing: border-box; color: #fff; display: block; text-align: center; padding: 10px 0; border-radius: 4px; text-decoration: none; transition: all 0.3s ease;">
                                                Xem sản phẩm
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Phân trang - luôn hiển thị ngay cả khi chỉ có 1 trang -->
                            <div class="row">
                                <div class="row_page">
                                    <nav>
                                        <ul class="pagination">
                                            <?php if ($current_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="index.php?man_page=<?php echo ($current_page - 1); ?>">Prev</a>
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
                                            
                                            // Hiển thị dấu ... nếu không hiển thị từ trang 1
                                            if($start_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="index.php?man_page=1">1</a>
                                                </li>
                                                <?php if($start_page > 2): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php 
                                            // Luôn hiển thị ít nhất 1 trang
                                            if ($total_page == 0) {
                                                $total_page = 1;
                                                $end_page = 1;
                                            }
                                            
                                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                                    <?php if ($i == $current_page): ?>
                                                        <span class="page-link"><?php echo $i; ?></span>
                                                    <?php else: ?>
                                                        <a class="page-link" href="index.php?man_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php 
                                            // Hiển thị dấu ... nếu không hiển thị đến trang cuối
                                            if($end_page < $total_page): ?>
                                                <?php if($end_page < $total_page - 1): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                                <?php endif; ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="index.php?man_page=<?php echo $total_page; ?>"><?php echo $total_page; ?></a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php if ($current_page < $total_page): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="index.php?man_page=<?php echo ($current_page + 1); ?>">Next</a>
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
                </div>

                        <style>
                        .manufacturers__heading {
                            text-align: center;
                            margin-bottom: 30px;
                            padding-bottom: 15px;
                            border-bottom: 1px solid #eee;
                        }

                        .manufacturers__heading h2 {
                            font-size: 28px;
                            color: #333;
                            margin-bottom: 10px;
                            position: relative;
                            display: inline-block;
                        }

                        .manufacturers__heading h2:after {
                            content: "";
                            position: absolute;
                            width: 60px;
                            height: 3px;
                            background-color: #ffa500;
                            bottom: -10px;
                            left: 50%;
                            transform: translateX(-50%);
                        }

                        .manufacturers__heading p {
                            color: #666;
                            font-size: 16px;
                            margin-top: 20px;
                        }

                        .manufacturer-card {
                            background-color: #fff;
                            border-radius: 8px;
                            overflow: hidden;
                            transition: all 0.3s ease;
                            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                            height: 100%;
                            margin-bottom: 20px;
                            display: flex;
                            flex-direction: column;
                        }

                        .manufacturer-card:hover {
                            transform: translateY(-5px);
                            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                        }

                        .manufacturer-card__inner {
                            padding: 25px 20px;
                            text-align: center;
                            display: flex;
                            flex-direction: column;
                            height: 100%;
                            justify-content: space-between;
                        }

                        .manufacturer-card__logo {
                            width: 80px;
                            height: 80px;
                            margin: 0 auto 20px;
                            background-color: #f8f9fa;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 32px;
                            color: #ffa500;
                            box-shadow: 0 5px 15px rgba(255, 165, 0, 0.2);
                        }

                        .manufacturer-card__name {
                            font-size: 18px;
                            font-weight: 600;
                            color: #333;
                            margin-bottom: 15px;
                            height: 40px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }

                        .manufacturer-card__desc {
                            color: #666;
                            font-size: 14px;
                            margin-bottom: 20px;
                            flex-grow: 1;
                            display: -webkit-box;
                            -webkit-line-clamp: 3;
                            -webkit-box-orient: vertical;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            height: 60px;
                        }

                        .manufacturer-card__link {
                            display: inline-block;
                            padding: 10px 20px;
                            background-color: #ffa500;
                            color: #fff;
                            border-radius: 5px;
                            text-decoration: none;
                            font-weight: 500;
                            transition: background-color 0.3s ease;
                            width: 100%;
                        }

                        .manufacturer-card__link:hover {
                            background-color: #ff8c00;
                        }

                        .pagination {
                            display: flex;
                            justify-content: center;
                            list-style: none;
                            padding: 0;
                            margin-top: 30px;
                        }

                        .page-item {
                            margin: 0 5px;
                        }

                        .page-link {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 40px;
                            height: 40px;
                            background-color: #fff;
                            color: #333;
                            border-radius: 5px;
                            text-decoration: none;
                            transition: all 0.3s ease;
                            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                        }

                        .page-item.active .page-link,
                        .page-link:hover {
                            background-color: #ffa500;
                            color: #fff;
                        }

                        .page-item.disabled .page-link {
                            background-color: #f5f5f5;
                            color: #999;
                            cursor: not-allowed;
                        }

                        @media (max-width: 768px) {
                            .manufacturer-card__logo {
                                width: 60px;
                                height: 60px;
                                font-size: 24px;
                            }
                            
                            .manufacturer-card__inner {
                                padding: 15px;
                            }
                            
                            .manufacturer-card__name {
                                font-size: 16px;
                            }
                            
                            .pagination {
                                flex-wrap: wrap;
                            }
                        }
                        </style>
                        <?php } ?>
                    </div>
                </section>
                
                </div>
            
            <div class="slidebar-container" style="flex: 0 0 25%; min-width: 250px; padding-left: 20px; padding-top: 10px;">
                <?php include './partials/slidebar.php' ?>
            </div>
        </div>
        
        <div class="footer" style="background-color: #f8f8f8; color: #333; border-radius: 0; margin-top: 40px; padding: 40px 0 20px; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); width: 100%; border-top: 1px solid #efefef;">
            <div class="grid_full-width">
                <div class="grid">
                    <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -15px;">
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 250px; padding: 0 15px; margin-bottom: 20px;">
                            <div class="logo logo-bottom ml-mobi">
                                <a href="index.php" style="font-size: 28px; color: #333; font-weight: bold; text-transform: uppercase; display: flex; align-items: center; margin-bottom: 20px; text-decoration: none; letter-spacing: 1px;">
                                    <i class="fas fa-glasses" style="color: #ffa500; margin-right: 12px; font-size: 28px;"></i>
                                    EYE<span style="color: #333;">GLASSES</span>
                                </a>
                            </div>
                            <div class="footer__text ml-mobi" style="color: #666; line-height: 1.6; padding-right: 20px; font-size: 14px;">
                                <p>EyeGlasses chuyên cung cấp các loại mắt kính thời trang, 
                                kính mát và tròng kính chính hãng, giá tốt, 
                                đa dạng thương hiệu. Mua sắm dễ dàng, giao hàng nhanh, 
                                khuyến mãi hấp dẫn mỗi ngày! </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 250px; padding: 0 15px; margin-bottom: 20px;">
                            <div class="footer__about">
                                <h3 style="color: #333; border-bottom: 1px solid #efefef; padding-bottom: 10px; margin-bottom: 15px; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Địa chỉ</h3>
                            </div>
                            <div class="footer__text" style="color: #666; line-height: 1.6; font-size: 14px;">
                                <p>
                                    <i class="fas fa-map-marker-alt" style="color: #ffa500; margin-right: 10px;"></i> Saigon University
                                </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 250px; padding: 0 15px; margin-bottom: 20px;">
                            <div class="footer__about">
                                <h3 style="color: #333; border-bottom: 1px solid #efefef; padding-bottom: 10px; margin-bottom: 15px; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Dịch vụ</h3>
                            </div>
                            <div class="footer__text" style="color: #666; line-height: 1.6; font-size: 14px;">
                                <p>
                                    <i class="fas fa-shield-alt" style="color: #ffa500; margin-right: 10px;"></i> Bảo hành kính vỡ, trầy xước trong 30 ngày
                                </p>
                                <p>
                                    <i class="fas fa-eye" style="color: #ffa500; margin-right: 10px;"></i> Đo mắt miễn phí, tư vấn kính mắt phù hợp
                                </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 250px; padding: 0 15px; margin-bottom: 20px;">
                            <div class="footer__about">
                                <h3 style="color: #333; border-bottom: 1px solid #efefef; padding-bottom: 10px; margin-bottom: 15px; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Liên hệ</h3>
                            </div>
                            <div class="footer__text" style="color: #666; line-height: 1.6; font-size: 14px;">
                                <p><i class="fas fa-phone-alt" style="color: #ffa500; margin-right: 10px;"></i> Phone Sale: <a href="tel:+00 151515" style="color: #333; text-decoration: none; font-weight: 500;">(+84) 090000000</a></p>
                                <p><i class="fas fa-envelope" style="color: #ffa500; margin-right: 10px;"></i> Email: <a href="mailto:contact@eyeglasses.com" style="color: #333; text-decoration: none; font-weight: 500;">contact@eyeglasses.com</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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