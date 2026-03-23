<?php
// Lấy sản phẩm từ bảng products 
$sql = "SELECT p.*, b.brand_name 
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.brand_id 
        WHERE LOWER(b.brand_name) LIKE '%gucci%' 
        ORDER BY p.product_id DESC LIMIT 5";
$result_products = mysqli_query($connect, $sql);

// Mảng các loại kính để liên kết banner
$types = ['men', 'women', 'sunglasses', 'children', 'lens'];
?>
<div class="grid">
    <div class="slider-wrap slider-slick">
        <div class="brands-img__wrap">
            <a href="view_brand.php?brand=gucci" class="brands__img">
                <img src="public/img/slide1.jpg" alt="Gucci Slide 1">
                <div class="slider-caption">
                    <h3>Gucci - Nâng tầm phong cách</h3>
                    <p>Khám phá bộ sưu tập mới</p>
                </div>
            </a>
        </div>
        <!-- Thêm nhiều ảnh nếu muốn slider chuyển động -->
    </div>
</div>

<script>
$(document).ready(function(){
    $('.slider-slick').slick({
        dots: true,
        infinite: true,
        speed: 600,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3500,
        arrows: true,
        fade: false,
        cssEase: 'linear'
    });
});
</script>

<style>
.slider-wrap {
    position: relative;
    margin-bottom: 30px;
    overflow: visible;
    width: 100%;
    max-height: none;
    display: block;
}

.brands-img__wrap {
    position: relative;
    width: 100%;
    display: block;
    margin: 0;
    padding: 0;
}

.brands__img {
    display: block;
    width: 100%;
    position: relative;
    overflow: hidden;
}

.brands__img img {
    width: 100%;
    height: auto;
    display: block;
    max-height: 500px;
    object-fit: cover;
    object-position: center;
}

.slider-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 15px;
    text-align: center;
}

.slider-caption h3 {
    margin: 0;
    font-size: 16px;
    color: white;
}

.slider-caption p {
    margin: 5px 0 0;
    font-size: 14px;
    color: #ff5555;
}
</style>