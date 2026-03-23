<!-- categories list -->
<div class="categories-list" style="display: flex; flex-wrap: wrap; margin: 0 -15px; justify-content: center;">
    <?php foreach ($categories as $category) : ?>
        <div class="category-card-container" style="padding: 0 15px; margin-bottom: 30px; width: calc(33.333% - 30px); box-sizing: border-box;">
            <a href="category.php?id=<?= $category['id'] ?>" class="category-card" style="background-color: #ffffff; border: 1px solid #efefef; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 20px; text-decoration: none; display: flex; flex-direction: column; align-items: center; transition: all 0.3s ease; height: 100%;">
                <div class="category-icon" style="height: 80px; width: 80px; background-color: #f9f9f9; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-bottom: 15px; color: #ffa500;">
                    <i class="fas fa-folder" style="font-size: 32px;"></i>
                </div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 500; color: #333; text-align: center;"><?= htmlspecialchars($category['name']) ?></h3>
                <p style="font-size: 14px; color: #777; margin: 10px 0 0 0; text-align: center;">
                    <?php 
                    $productCount = isset($category['product_count']) ? $category['product_count'] : 0;
                    echo $productCount . ' ' . ($productCount == 1 ? 'product' : 'products');
                    ?>
                </p>
            </a>
        </div>
    <?php endforeach ?>
</div>

<style>
.category-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.category-card:hover .category-icon {
    background-color: #fff5e6;
}

.category-card:hover h3 {
    color: #ffa500;
}

@media (max-width: 1200px) {
    .category-card-container {
        width: calc(33.333% - 30px);
    }
}

@media (max-width: 767px) {
    .category-card-container {
        width: calc(50% - 30px);
    }
}

@media (max-width: 480px) {
    .category-card-container {
        width: 100%;
    }
}
</style> 