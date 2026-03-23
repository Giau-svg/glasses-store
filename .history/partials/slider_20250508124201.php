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
            <a href="view_brand.php?id=3" class="brands__img">
                <img src="public/img/z6579214764760_45c0c06b50e912ed106b51b2bfa94bfa.jpg" alt="Prada Slide 1">
                <div class="slider-caption">
                    <h3>Prada - Đẳng cấp thời thượng</h3>
                    <p>Khám phá bộ sưu tập Prada mới</p>
                </div>
            </a>
        </div>
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

    $.get('get_cart_count.php', function(count){
        $('#cart-count').text(count);
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
    opacity: 0;
    transform: scale(0.96);
    animation: sliderFadeInZoom 1.2s cubic-bezier(0.23, 1, 0.32, 1) forwards;
}

@keyframes sliderFadeInZoom {
    to {
        opacity: 1;
        transform: scale(1);
    }
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
    background: linear-gradient(0deg, rgba(0,0,0,0.85) 60%, rgba(0,0,0,0.1) 100%);
    color: white;
    padding: 25px 15px 18px 15px;
    text-align: center;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    letter-spacing: 1px;
    font-family: 'Roboto', sans-serif;
}

.slider-caption h3 {
    margin: 0;
    font-size: 2rem;
    color: #fff;
    text-shadow: 0 4px 16px rgba(0,0,0,0.4);
    font-weight: 700;
    letter-spacing: 2px;
}

.slider-caption p {
    margin: 10px 0 0;
    font-size: 1.1rem;
    color: #ffb300;
    text-shadow: 0 2px 8px rgba(0,0,0,0.3);
    font-weight: 500;
}
</style>
