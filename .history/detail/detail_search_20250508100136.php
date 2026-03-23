<div class="container">
    <?php include './partials/menu.php' ?>
    <div class="row">
        <!-- Cột trái: Danh sách sản phẩm -->
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
                        <div class="col-3 col-md-3 col-sm-6 mb-4">
                            <div class="product-card" style="background: #fff; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); overflow: hidden; transition: box-shadow 0.3s, transform 0.3s; height: 100%; display: flex; flex-direction: column;">
                                <a href="view_detail.php?id=<?php echo $each['product_id'] ?>" class="product-link" style="display: flex; flex-direction: column; height: 100%; text-decoration: none;">
                                    <div class="product-img-container" style="padding: 20px; display: flex; justify-content: center; align-items: center; background: #fff; height: 180px; overflow: hidden;">
                                        <img src="admin/products/uploads/<?php echo $each['image_path'] ?>" alt="<?php echo htmlspecialchars($each['product_name']) ?>" style="max-height: 140px; max-width: 100%; object-fit: contain; transition: all 0.5s;">
                                    </div>
                                    <div class="product-info" style="padding: 12px 16px; flex: 1; display: flex; flex-direction: column; align-items: center;">
                                        <h4 class="product-name" style="color: #222; font-size: 16px; font-weight: 500; margin-bottom: 8px; text-align: center; min-height: 40px; overflow: hidden; line-height: 1.2;">
                                            <?php echo $each['product_name'] ?>
                                        </h4>
                                        <?php if (!empty($each['brand_name'])): ?>
                                            <div class="product-brand" style="margin-bottom: 6px;">
                                                <span style="background: #f5f5f5; color: #333; padding: 3px 10px; border-radius: 4px; font-size: 12px; font-weight: 400;"> <?php echo htmlspecialchars($each['brand_name']) ?> </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($each['manufacturer_name'])): ?>
                                            <div class="product-manufacturer" style="margin-bottom: 6px;">
                                                <span style="background: #f5f5f5; color: #333; padding: 3px 10px; border-radius: 4px; font-size: 12px;">Nhà SX: <?php echo htmlspecialchars($each['manufacturer_name']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="product-price" style="margin: 10px 0 0 0;">
                                            <span class="price-value" style="color: #ffa500; font-size: 18px; font-weight: 600;">
                                                <?php echo currency_format($each['price']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                <div class="product-actions" style="padding: 0 16px 16px 16px;">
                                    <a href="javascript:void(0)" class="btn-add-to-cart" data-id="<?php echo $each['product_id'] ?>" style="background: #ffa500; color: #fff; border-radius: 4px; font-weight: 500; display: block; text-align: center; padding: 8px 0; margin-bottom: 6px; text-decoration: none; transition: background 0.2s;">Thêm vào giỏ hàng</a>
                                    <?php if (!empty($_SESSION['id'])) { ?>
                                        <a onclick="return Suc()" href="add_to_cart.php?id=<?php echo $each['product_id'] ?>" class="btn-add-cart" style="background: #f8f8f8; color: #333; border-radius: 4px; font-weight: 500; display: block; text-align: center; padding: 8px 0; text-decoration: none; border: 1px solid #e0e0e0;">
                                            <i class="fas fa-shopping-cart" style="margin-right: 5px;"></i> Thêm vào giỏ
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-center">Không tìm thấy sản phẩm nào phù hợp với từ khóa tìm kiếm.</p>
                    </div>
                <?php endif ?>
            </div>
        </div>
        <!-- Cột phải: Sidebar filter giống layout danh mục -->
        <div class="col-3">
            <div class="content__sidebar">
                <div class="sidebar__category">
                    <div class="sidebar__header">
                        <i class="fas fa-filter category-icon"></i>
                        <h3 class="sidebar__title">Lọc sản phẩm</h3>
                    </div>
                    <form method="get" action="search.php" style="padding: 0 10px 10px 10px;">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_products) ?>">
                        <div class="sidebar__filter-group">
                            <label>Khoảng giá:</label>
                            <select name="price_range" class="sidebar__filter-select">
                                <option value="">Tất cả</option>
                                <option value="1">Dưới 1 triệu</option>
                                <option value="2">1 - 3 triệu</option>
                                <option value="3">3 - 5 triệu</option>
                                <option value="4">Trên 5 triệu</option>
                            </select>
                        </div>
                        <div class="sidebar__filter-group">
                            <label>Thương hiệu:</label>
                            <select name="brand_id" class="sidebar__filter-select">
                                <option value="">Tất cả</option>
                                <?php
                                $brands = mysqli_query($connect, "SELECT * FROM brands");
                                while($b = mysqli_fetch_assoc($brands)) {
                                    echo '<option value="'.$b['brand_id'].'">'.$b['brand_name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="sidebar__filter-btn">Lọc</button>
                    </form>
                    <div class="sidebar__footer">
                        <p>Lọc nhanh sản phẩm theo nhu cầu của bạn</p>
                    </div>
                </div>
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