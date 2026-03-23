<div class="container">
    <div class="grid_full-width">
        <?php include './partials/menu.php' ?>
        <div class="grid_full-width content">
            <div class="content__brands">
                <?php include './partials/slider.php' ?>
                <div class="grid">
                    <div class="brands__heading">
                        <h1>Kết quả tìm kiếm: 
                            <?php if($search_products != '###'): ?>
                                <?php echo htmlspecialchars($search_products) ?> 
                            <?php endif ?>
                        </h1>
                    </div>
                </div>
                <div class="grid">
                    <div class="row row-category">
                        <?php if (mysqli_num_rows($result) > 0) : ?>
                            <?php while ($each = mysqli_fetch_assoc($result)) : ?>
                                <div class="col col-3 col-2-mt mt-10">
                                    <div class="category">
                                        <a href="view_detail.php?id=<?php echo $each['product_id'] ?>" class="category_link">
                                            <div class="category__img">
                                                <img src="admin/products/uploads/<?php echo $each['image_path'] ?>" alt="<?php echo htmlspecialchars($each['product_name']) ?>">
                                            </div>
                                            <h4 class="category__name"><?php echo htmlspecialchars($each['product_name']) ?></h4>
                                            <div class="manufacturer-info">
                                                <?php if (!empty($each['brand_name'])): ?>
                                                    <div class="manufacturer-info">
                                                        Nhà SX: <?php echo htmlspecialchars($each['brand_name']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="category__price" style="color: #e63946; font-size: 2rem; font-weight: bold; margin-top: 10px;">
                                                <?php echo currency_format($each['price']) ?>
                                            </div>
                                        </a>
                                        <div class="category-btn">
                                            <a href="login.php" class="add-to-cart-btn">
                                                Thêm vào giỏ hàng
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile ?>
                        <?php else: ?>
                            <div class="col col-12">
                                <p class="text-center">Không tìm thấy sản phẩm nào phù hợp với từ khóa tìm kiếm.</p>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
            <?php include './partials/slidebar.php' ?>
        </div>
        <div class="footer">
            <div class="grid_full-width">
                <div class="grid">
                    <div class="row">
                        <div class="col col-4 col-mobi">
                            <div class="logo logo-bottom ml-mobi">
                                <img src="./public/img/logo2.png" alt="" class="img">
                            </div>
                            <div class="footer__text ml-mobi">
                                <p>Vietpro Academy thành lập năm 2009. Chúng
                                    tôi đào tạo chuyên sâu trong 2 lĩnh vực là Lập
                                    trình Website & Mobile nhằm cung cấp cho thị
                                    trường CNTT Việt Nam những lập trình viên
                                    thực sự chất lượng, có khả năng làm việc độc
                                    lập, cũng như Team Work ở mọi môi trường đòi
                                    hỏi sự chuyên nghiệp cao. </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi">
                            <div class="footer__about">
                                <h3>Địa chỉ</h3>
                            </div>
                            <div class="footer__text">
                                <p>
                                    B8A Võ Văn Dũng - Hoàng Cầu Đống Đa -
                                    Hà Nội
                                </p>
                                <p>
                                    Số 25 Ngõ 178/71 - Tây Sơn Đống Đa - Hà
                                    Nội
                                </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi">
                            <div class="footer__about">
                                <h3>Dịch vụ</h3>
                            </div>
                            <div class="footer__text">
                                <p>
                                    Bảo hành rơi vỡ, ngấm nước Care Diamond
                                </p>
                                <p>
                                    Bảo hành Care X60 rơi vỡ ngấm nước vẫn Đổi
                                    mới
                                </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi">
                            <div class="footer__about">
                                <h3>Liên hệ</h3>
                            </div>
                            <div class="footer__text">
                                <p>Phone Sale: <a href="tel:+00 151515">(+84) 0988 550 5535</a></p>
                                <p>Email: <a href="mailto:vietpro.edu.vn@gmail.com">vietpro.edu.vn@gmail.com</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>