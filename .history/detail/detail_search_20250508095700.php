<div class="container">
    <?php include './partials/menu.php' ?>
    <div class="row">
        <!-- Sidebar filter bên phải -->
        <div class="col-9">
            <div class="brands__heading">
                <h1>Kết quả tìm kiếm: 
                    <?php if($search_products != '###'): ?>
                        <?php echo htmlspecialchars($search_products) ?> 
                    <?php endif ?>
                </h1>
                <p>
                    <?php
                    $total_found = mysqli_num_rows($result);
                    echo "Tìm thấy <b>$total_found</b> sản phẩm";
                    ?>
                </p>
            </div>
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
        <div class="col-3">
            <!-- Filter nhỏ gọn, dọc -->
            <div class="sidebar__filter" style="background: #f7f9fb; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 24px 18px; min-width: 220px;">
                <h3 style="color: #d72660; font-size: 20px; font-weight: 700; margin-bottom: 18px; display: flex; align-items: center;"><i class="fas fa-filter" style="margin-right: 8px; color: #d72660;"></i> Lọc sản phẩm</h3>
                <form method="get" action="search.php">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_products) ?>">
                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 500; margin-bottom: 6px; display: block;">Khoảng giá:</label>
                        <select name="price_range" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #ddd;">
                            <option value="">Tất cả</option>
                            <option value="1">Dưới 1 triệu</option>
                            <option value="2">1 - 3 triệu</option>
                            <option value="3">3 - 5 triệu</option>
                            <option value="4">Trên 5 triệu</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 500; margin-bottom: 6px; display: block;">Thương hiệu:</label>
                        <select name="brand_id" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #ddd;">
                            <option value="">Tất cả</option>
                            <?php
                            $brands = mysqli_query($connect, "SELECT * FROM brands");
                            while($b = mysqli_fetch_assoc($brands)) {
                                echo '<option value="'.$b['brand_id'].'">'.$b['brand_name'].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" style="width: 100%; background: #d72660; color: #fff; border: none; border-radius: 6px; padding: 10px 0; font-weight: 600; font-size: 16px; cursor: pointer; transition: background 0.2s;">Lọc</button>
                </form>
            </div>
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

.sidebar__filter {
    margin: 20px 0;
    padding: 15px 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    display: flex;
    gap: 20px;
    align-items: center;
}
.sidebar__filter label {
    margin-right: 8px;
    font-weight: 500;
}
.sidebar__filter select, .sidebar__filter button {
    padding: 6px 12px;
    border-radius: 4px;
    border: 1px solid #ddd;
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