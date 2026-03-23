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
                                        <div class="product-actions">
                                            <a href="javascript:void(0)" class="btn-add-to-cart" data-id="<?php echo $each['product_id'] ?>">Thêm vào giỏ hàng</a>
                                            <?php if (!empty($_SESSION['id'])) { ?>
                                                <a onclick="return Suc()" href="add_to_cart.php?id=<?php echo $each['product_id'] ?>" class="btn-add-cart" style="background-color: #f8f8f8; color: #333; text-align: center; padding: 10px 0; border-radius: 4px; font-weight: 500; display: block; transition: all 0.3s ease; text-decoration: none; border: 1px solid #e0e0e0;">
                                                    <i class="fas fa-shopping-cart" style="margin-right: 5px;"></i> Thêm vào giỏ
                                                </a>
                                            <?php } ?>
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
    </div>
</div>

<style>
.product-actions {
    opacity: 0;
    visibility: hidden;
    height: 0;
    transition: opacity 0.2s, height 0.2s;
    overflow: hidden;
}

.product-card:hover .product-actions {
    opacity: 1;
    visibility: visible;
    height: auto;
}

.product-card {
    position: relative;
    transition: box-shadow 0.3s, transform 0.3s;
}

.product-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    transform: translateY(-5px) scale(1.03);
}

.btn-add-to-cart {
    background-color: #ffa500;
    color: #fff;
    text-align: center;
    padding: 10px 0;
    border-radius: 4px;
    font-weight: 500;
    display: block;
    transition: all 0.3s ease;
    text-decoration: none;
    margin-bottom: 8px;
}
.btn-add-to-cart:hover {
    background-color: #ff8c00;
}
</style>

<script>
$(document).ready(function() {
    $(".btn-add-to-cart").click(function() {
        let id = $(this).data('id');
        $.ajax({
            url: 'add_to_cart.php',
            type: 'GET',
            data: {id},
        })
        .done(function(response){
            if(response == 1){
                alert('Thêm giỏ hàng thành công');
            }else {
                alert(response);  
            }
        });
    });
});
</script>