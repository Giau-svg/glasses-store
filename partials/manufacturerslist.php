<!-- manufacturers list -->
<div class="manufacturers-list" style="display: flex; flex-wrap: wrap; margin: 0 -15px; justify-content: center;">
    <?php foreach ($manufacturers as $manufacturer) : ?>
        <div class="manufacturer-item-container" style="padding: 0 15px; margin-bottom: 30px; width: calc(33.333% - 30px); box-sizing: border-box;">
            <a href="manufacturer.php?id=<?= $manufacturer['id'] ?>" class="manufacturer-item" style="display: flex; flex-direction: column; align-items: center; background-color: #ffffff; padding: 20px; text-decoration: none; border-radius: 0; border: 1px solid #efefef; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: all 0.3s ease; height: 100%;">
                <div class="manufacturer-img" style="width: 100%; height: 140px; display: flex; justify-content: center; align-items: center; margin-bottom: 15px;">
                    <?php if (!empty($manufacturer['image_path'])) : ?>
                        <img src="admin/manufacturers/uploads/<?= $manufacturer['image_path'] ?>" alt="<?= htmlspecialchars($manufacturer['name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.5s ease;">
                    <?php else : ?>
                        <i class="fas fa-building" style="font-size: 50px; color: #ffa500;"></i>
                    <?php endif; ?>
                </div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 500; color: #333; text-align: center; transition: all 0.3s ease;"><?= htmlspecialchars($manufacturer['name']) ?></h3>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<style>
.manufacturer-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.manufacturer-item:hover h3 {
    color: #ffa500;
}

.manufacturer-item:hover .manufacturer-img img {
    transform: scale(1.08);
}

@media (max-width: 1200px) {
    .manufacturer-item-container {
        width: calc(33.333% - 30px);
    }
}

@media (max-width: 767px) {
    .manufacturer-item-container {
        width: calc(50% - 30px);
    }
    
    .manufacturer-img {
        height: 120px;
    }
}

@media (max-width: 480px) {
    .manufacturer-item-container {
        width: 100%;
    }
}
</style> 