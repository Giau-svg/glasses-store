<?php

$cart = $_SESSION['cart'] ?? null;
$carts = $_SESSION['carts'] ?? null;

$sum_quantity = 0;
$sum_quantitys = 0;

?>

<div class="container">
    <div class="grid_full-width">
        <?php include './partials/menu.php' ?>
        <div class="grid_full-width content">
            <div class="content__brands">
                <?php include './partials/slider.php' ?>
                <div class="grid">
                    <div class="row">
                        <div class="col col-full">
                            <?php if (!empty($_SESSION['cart'])) { ?>
                                <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
                                    <div class="alert alert-danger">
                                        <p><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['checkout_error'] ?? 'Không thể xử lý đơn hàng'; ?></p>
                                    </div>
                                    <?php unset($_SESSION['checkout_error']); ?>
                                <?php endif; ?>
                                <div class="grid table_cart-info">
                                    <div class="row-table_cart">
                                        <div class="col-table col-table-5">Thông tin sản phẩm</div>
                                        <div class="col-table col-table-1">Tùy chọn</div>
                                        <div class="col-table col-table-2">Giá</div>
                                        <div class="col-table col-table-2 col-table_np">Tổng tiền</div>
                                    </div>
                                    <?php foreach ($cart as $id => $each) : ?>
                                        <div class="row-table_cart row-cart-js">
                                            <div class="col-table-p col-table-5">
                                                <img height="200" src="admin/products/server/uploads/<?php echo $each['image_path'] ?>" alt="">
                                                <p><?php echo $each['product_name'] ?></p>
                                            </div>
                                            <div class="col-table-p col-table-1">
                                                <button class="cart_type btn-update-quantity" data-id='<?php echo $id ?>' data-type='decre'>-</button>
                                                <div class="cart_type cart-quantity"> <?php echo $each['quantity'] ?></div>
                                                <button class="cart_type btn-update-quantity" data-id='<?php echo $id ?>' data-type='incre'>+</button>
                                            </div>
                                            <div class="col-table-p col-table-2 col-table-price">
                                                <span class="cart-price">
                                                    <?php echo $each['price'] ?>
                                                </span>
                                                <button class="cart_type btn-cart-delete" data-id="<?php echo $id ?>">Xóa</button>
                                            </div>
                                            <div class="col-table-p col-table-2">
                                                <div class="table_np">Tổng tiền:</div>
                                                <span class="cart-total">
                                                    <?php
                                                    $total = $each['price'] * $each['quantity'];
                                                    echo $total;
                                                    $sum_quantity += $total;
                                                    ?>
                                                </span>
                                            </div>

                                        </div>
                                    <?php endforeach ?>
                                    <div class="row-table_cart row-after">
                                        <div class="col-table col-table-5">Tổng tiền hóa đơn: $<span id="sum_quantity"><?php echo currency_format($sum_quantity); ?></span> </div>
                                    </div>
                                </div>
                                <div class="grid table_cart-info">
                                    <div class="row-table_cart row-after">
                                        <?php
                                        $id = $_SESSION['id'];
                                        $sql = "SELECT * FROM `users` where `user_id` = '$id'";
                                        $result_ct = mysqli_query($connect, $sql);
                                        $each = mysqli_fetch_array($result_ct);
                                        ?>
                                        <form class="col-full col-form-order" action="process_checkout.php" method="post">
                                            <div class="row-order">
                                                <div class="col col-3">
                                                    <label class="label-address" for="">Họ và tên(bắt buộc)</label>
                                                    <input class="form_order" type="text" name="name_receiver" value="<?php echo $each['full_name'] ?>">
                                                </div>
                                                <div class="col col-3">
                                                    <label class="label-address" for="">Số điện thoại(bắt buộc)</label>
                                                    <input class="form_order" type="text" name="phone_receiver" value="<?php echo $each['phone_number'] ?>">
                                                </div>
                                            </div>
                                            <div class="row-order">
                                                <div class="col col-full">
                                                    <label class="label-address" for="">Địa chỉ người nhận(bắt buộc)</label>
                                                    <input class="form_order" type="text" name="address_receiver" value="<?php echo $each['address'] ?>">
                                                </div>
                                            </div>
                                            <button class="order-btn" type="submit">Đặt hàng</button>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-full">
                            <?php if (!empty($_SESSION['carts'])) { ?>
                                <div class="grid table_cart-info">
                                    <div class="row-table_cart">
                                        <div class="col-table col-table-5">Thông tin sản phẩm</div>
                                        <div class="col-table col-table-1">Tùy chọn</div>
                                        <div class="col-table col-table-2">Giá</div>
                                        <div class="col-table col-table-2 col-table_np">Tổng tiền</div>
                                    </div>
                                    <?php foreach ($carts as $id => $eachs) : ?>
                                        <div class="row-table_cart">
                                            <div class="col-table-p col-table-5">
                                                <img height="200" src="admin/product_laptop/server/uploads/<?php echo $eachs['photo'] ?>" alt="">
                                                <p><?php echo $eachs['name'] ?></p>
                                            </div>
                                            <div class="col-table-p col-table-1">
                                                <a class="cart_type" onclick="return Del('<?php echo $eachs['quantity']; ?>')" href="update_in_cart_laptop.php?id=<?php echo $id ?>&type=decre">-</a>
                                                <div class="cart_type cart-quantity"> <?php echo $eachs['quantity'] ?></div>
                                                <a class="cart_type" onclick="return Inc('<?php echo $eachs['quantity']; ?>')" href="update_in_cart_laptop.php?id=<?php echo $id ?>&type=incre">+</a>
                                            </div>
                                            <div class="col-table-p col-table-2 col-table-price">
                                                <span><?php echo currency_format($eachs['price']) ?></span>
                                                <a class="cart_type" href="delete_from_cart.php?id=<?php echo $id ?>">Xóa</a>
                                            </div>
                                            <div class="col-table-p col-table-2">
                                                <div class="table_np">Tổng tiền:</div>
                                                <span>
                                                    <?php
                                                    $result_quantity = $eachs['price'] * $eachs['quantity'];
                                                    echo currency_format($result_quantity);
                                                    $sum_quantitys += $result_quantity;
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                    <div class="row-table_cart row-after">
                                        <div class="col-table col-table-5">Tổng tiền hóa đơn: $<span><?php echo currency_format($sum_quantitys); ?></span> </div>
                                    </div>
                                </div>
                                <div class="grid table_cart-info">
                                    <div class="row-table_cart row-after">
                                        <?php
                                        $id = $_SESSION['id'];
                                        $sql = "SELECT * FROM `users` where `user_id` = '$id'";
                                        $result_ct = mysqli_query($connect, $sql);
                                        $each = mysqli_fetch_array($result_ct);
                                        ?>
                                        <form class="col-full col-form-order" action="process_checkout_lp.php" method="post">
                                            <div class="row-order">
                                                <div class="col col-3">
                                                    <label class="label-address" for="">Họ và tên(bắt buộc)</label>
                                                    <input class="form_order" type="text" name="name_receiver" value="<?php echo $each['full_name'] ?>">
                                                </div>
                                                <div class="col col-3">
                                                    <label class="label-address" for="">Số điện thoại(bắt buộc)</label>
                                                    <input class="form_order" type="text" name="phone_receiver" value="<?php echo $each['phone_number'] ?>">
                                                </div>
                                            </div>
                                            <div class="row-order">
                                                <div class="col col-full">
                                                    <label class="label-address" for="">Địa chỉ người nhận(bắt buộc)</label>
                                                    <input class="form_order" type="text" name="address_receiver" value="<?php echo $each['address'] ?>">
                                                </div>
                                            </div>
                                            <button class="order-btn" type="submit">Đặt hàng</button>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if (empty($_SESSION['carts']) && empty($_SESSION['cart'])) { ?>
                        <div class="row">
                            <div class="col col-full">
                                <div class="grid table_cart-info">
                                    <div class="row-table_cart">
                                        <div style="margin: 0 auto;" class="content_cart">
                                            <div class="cart_none">
                                                <img src="./public/img/cart_0.png" alt="">
                                                <h3>Giỏ hàng của bạn còn trống</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div></div>
                    <?php }  ?>
                </div>

            </div>
            <?php include './partials/slidebar.php' ?>
        </div>

        <div class="footer">
            <div class="grid_full-width">
                <div class="grid">
                    <div class="row">
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 250px; padding: 0 15px; margin-bottom: 20px;">
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
                        <div class="col col-4 col-mobi" style="flex: 1; min-width: 250px; padding: 0 15px; margin-bottom: 20px;">
                            <div class="footer__about">
                                <h3 style="color: #333; border-bottom: 1px solid #efefef; padding-bottom: 10px; margin-bottom: 15px; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Địa chỉ</h3>
                            </div>
                            <div class="footer__text" style="color: #666; line-height: 1.6; font-size: 14px;">
                                <p>
                                    <i class="fas fa-map-marker-alt" style="color: #ffa500; margin-right: 10px;"></i> Hutech University
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
                                <p>
                                    <i class="fas fa-phone" style="color: #ffa500; margin-right: 10px;"></i> Phone Sale: <a href="tel:+84988550553" style="color: #666; text-decoration: none;">(+84) 0988 550 553</a>
                                </p>
                                <p>
                                    <i class="fas fa-envelope" style="color: #ffa500; margin-right: 10px;"></i> Email: <a href="mailto:vietpro.edu.vn@gmail.com" style="color: #666; text-decoration: none;">vietpro.edu.vn@gmail.com</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>