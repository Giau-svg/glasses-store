<!-- products list -->
<div class="products-list" style="display: flex; flex-wrap: wrap; margin: 0 -15px; justify-content: center;">
    <?php foreach ($products as $product) : ?>
        <div class="product-card-container" style="padding: 0 15px; margin-bottom: 30px; width: calc(33.333% - 30px); box-sizing: border-box;">
            <div class="product-card" style="background-color: #ffffff; border: 1px solid #efefef; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 15px; display: flex; flex-direction: column; transition: all 0.4s ease; height: 100%;">
                <a href="product.php?id=<?= $product['id'] ?>" style="text-decoration: none; display: block; flex-grow: 1;">
                    <div class="product-img" style="height: 180px; display: flex; justify-content: center; align-items: center; margin-bottom: 15px; padding: 10px; background-color: #fff; overflow: hidden;">
                        <?php if (!empty($product['image_path'])) : ?>
                            <img src="admin/products/uploads/<?= $product['image_path'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.5s ease;">
                        <?php else : ?>
                            <div style="width: 100px; height: 100px; background-color: #f8f8f8; display: flex; justify-content: center; align-items: center; color: #ccc;">
                                <i class="fas fa-box" style="font-size: 40px;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 500; color: #333; height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; text-align: center;"><?= htmlspecialchars($product['name']) ?></h3>
                    
                    <?php if (!empty($product['manufacturer_name'])): ?>
                    <div style="text-align: center; margin-bottom: 10px;">
                        <span style="background-color: #f8f8f8; color: #333; padding: 5px 12px; border-radius: 0; font-size: 13px;">Nhà SX: <?= htmlspecialchars($product['manufacturer_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: center; align-items: center; margin: 15px 0; margin-top: auto;">
                        <p style="color: #333; margin-right: 5px; font-weight: 400;"> Giá bán:</p>
                        <span style="color: #ffa500; font-size: 18px; font-weight: 600;">
                            <?= number_format($product['price'], 0, ',', '.') ?> đ
                        </span>
                    </div>
                </a>
                <div style="padding: 0 0 10px;">
                    <ul style="display: flex; flex-direction: column; gap: 10px; padding: 0; list-style: none; margin: 0;">
                        <li style="width: 100%;">
                            <a href="product.php?id=<?= $product['id'] ?>" style="background-color: #333; color: #fff; text-align: center; padding: 12px; border-radius: 0; font-weight: 500; display: block; transition: all 0.3s ease; text-decoration: none;">
                                Mua ngay
                            </a>
                        </li>
                        <li style="width: 100%;">
                            <?php if (isset($product['stock']) && $product['stock'] > 0) : ?>
                                <a href="add_to_cart.php?id=<?= $product['id'] ?>" style="background-color: #ffa500; color: #000; text-align: center; padding: 12px; border-radius: 0; font-weight: 500; display: block; transition: all 0.3s ease; text-decoration: none;">
                                    Thêm vào giỏ hàng
                                </a>
                            <?php else : ?>
                                <button disabled style="background-color: #f8f8f8; border: 1px solid #ddd; color: #aaa; padding: 12px; border-radius: 0; font-weight: 500; width: 100%; cursor: not-allowed;">
                                    Hết hàng
                                </button>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.product-card:hover .product-img img {
    transform: scale(1.08);
}

.product-card a[style*="background-color: #333"]:hover {
    background-color: #222;
    transform: translateY(-2px);
}

.product-card a[style*="background-color: #ffa500"]:hover {
    background-color: #e69500;
    transform: translateY(-2px);
}

@media (max-width: 1200px) {
    .product-card-container {
        width: calc(33.333% - 30px);
    }
}

@media (max-width: 767px) {
    .product-card-container {
        width: calc(50% - 30px);
    }
    
    .product-img {
        height: 150px;
    }
}

@media (max-width: 480px) {
    .product-card-container {
        width: 100%;
    }
}
</style> 