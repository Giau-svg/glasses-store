<!-- dau -->
<div class="header header-fixed" style="background-color: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); width: 100%; border-bottom: 1px solid #efefef; display: block; position: relative;">
    <div class="header-container" style="max-width: 1400px; margin: 0 auto; padding: 10px 15px; width: 100%;">
        <header class="header-top" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div class="logo">
                <a href="index.php" style="font-size: 32px; color: #000000; font-weight: bold; text-transform: uppercase; transition: all 0.3s ease; display: flex; align-items: center; letter-spacing: 1px;">
                    <i class="fas fa-glasses" style="color: #ffa500; margin-right: 12px; font-size: 36px;"></i>EYE<span style="color: #000000;">GLASSES</span>
                </a>
            </div>
            <div class="header__search">
                <form style="background-color: #ffffff; border-radius: 50px; padding: 0; border: 2px solid #ffa500; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: all 0.3s ease; display: flex; overflow: hidden; width: 400px;" action="search.php" method="post">
                    <input id="project" placeholder="Tìm kiếm mắt kính..." type="text" class="header__input" name="search" style="background-color: #ffffff; color: #333; border-radius: 0; padding: 10px 20px; border: none; flex: 1; font-size: 14px; transition: all 0.3s ease;">
                    <button type="submit" class="header__btn" style="background-color: #ffa500; color: #000000; border-radius: 0 50px 50px 0; padding: 10px 22px; font-weight: bold; border: none; cursor: pointer; transition: all 0.3s ease; white-space: nowrap;">Tìm Kiếm</button>
                </form>
            </div>
            <div class="header__user">
                <div class="user-avatar">
                    <img src="./public/img/user.jpg" alt="" style="border: 1px solid #efefef; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <?php if (empty($_SESSION['customer_id'])) { ?>
                        <div class="avatar-indicator guest" style="background-color: #999;"></div>
                    <?php } else { ?>
                        <div class="avatar-indicator logged-in" style="background-color: #ffa500;"></div>
                    <?php } ?>
                </div>
                <ul class="user__info" style="background-color: #ffffff; border-radius: 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #efefef;">
                    <li>
                        <?php if (empty($_SESSION['customer_id'])) { ?>
                            <div class="auth-links" style="padding: 8px 0;">
                                <a href="signup.php" class="auth-link" style="color: #333; padding: 8px 15px; display: inline-block; font-weight: 500; border-radius: 0; transition: all 0.3s ease;"><i class="fas fa-user-plus" style="color: #ffa500;"></i> Đăng Ký</a>
                                <span class="auth-divider" style="margin: 0 5px; color: #efefef;">|</span>
                                <a href="login.php" class="auth-link" style="color: #333; padding: 8px 15px; display: inline-block; font-weight: 500; border-radius: 0; transition: all 0.3s ease;"><i class="fas fa-sign-in-alt" style="color: #ffa500;"></i> Đăng Nhập</a>
                            </div>
                        <?php } else { ?>
                            <div class="user-welcome" style="padding: 10px 15px; background-color: #f8f8f8; border-radius: 0; margin-bottom: 5px;">
                                <i class="fas fa-user-circle" style="color: #ffa500;"></i>
                                <span style="font-weight: 500; color: #333;">Hi! bạn <?php echo $_SESSION['customer_name'] ?></span>
                            </div>
                            <a href="./logout.php" class="logout-link" style="color: #333; display: block; padding: 10px 15px; text-align: center; background-color: #f8f8f8; border-radius: 0; margin-top: 5px; font-weight: 500; transition: all 0.3s ease;"><i class="fas fa-sign-out-alt" style="color: #ffa500;"></i> Đăng Xuất</a>
                        <?php } ?>
                    </li>
                    <?php if (!empty($_SESSION['customer_id'])) { ?>
                        <li>
                            <a href="./info_order.php" style="color: #333; display: block; padding: 10px 15px; text-align: center; background-color: #f8f8f8; border-radius: 0; margin-top: 5px; font-weight: 500; transition: all 0.3s ease;"><i class="fas fa-clipboard-list" style="color: #ffa500;"></i> Thông tin đơn hàng</a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <a href="view_cart.php" class="header__cart" style="background-color: #f8f8f8; color: #333; border-radius: 0; padding: 7px 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.3s ease; display: flex; align-items: center; border: 1px solid #efefef;">
                <h4 style="margin-right: 10px; font-weight: 500;">Giỏ Hàng</h4>
                <span id="cart-count" class="header__cart-notice" style="background-color: #ffa500; color: #000; font-weight: bold; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">0</span>
            </a>
            <label for="header__mobile-input" class="bars__header-mobile">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                    <!--! Font Awesome Pro 6.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
                    <path d="M0 96C0 78.33 14.33 64 32 64H416C433.7 64 448 78.33 448 96C448 113.7 433.7 128 416 128H32C14.33 128 0 113.7 0 96zM0 256C0 238.3 14.33 224 32 224H416C433.7 224 448 238.3 448 256C448 273.7 433.7 288 416 288H32C14.33 288 0 273.7 0 256zM416 448H32C14.33 448 0 433.7 0 416C0 398.3 14.33 384 32 384H416C433.7 384 448 398.3 448 416C448 433.7 433.7 448 416 448z" />
                </svg>
            </label>
            <input hidden type="checkbox" name="" class="header__input" id="header__mobile-input">
            <label for="header__mobile-input" class="header__overlay"></label>
            <nav class="navbar__mobile">
                <label for="header__mobile-input" class="bars__header-close">
                    <svg fill="#333" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                        <!--! Font Awesome Pro 6.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
                        <path d="M310.6 361.4c12.5 12.5 12.5 32.75 0 45.25C304.4 412.9 296.2 416 288 416s-16.38-3.125-22.62-9.375L160 301.3L54.63 406.6C48.38 412.9 40.19 416 32 416S15.63 412.9 9.375 406.6c-12.5-12.5-12.5-32.75 0-45.25l105.4-105.4L9.375 150.6c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L160 210.8l105.4-105.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-105.4 105.4L310.6 361.4z" />
                    </svg>
                </label>
                <form class="header__search-mobile" action="search.php" method="post" style="margin: 15px; background-color: #ffffff; border-radius: 50px; padding: 0; border: 2px solid #ffa500; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: all 0.3s ease; display: flex; overflow: hidden;">
                    <input id="project" placeholder="Tìm kiếm mắt kính..." class="search-mobile__input" name="search" style="background-color: #ffffff; color: #333; border-radius: 0; padding: 10px 20px; border: none; flex: 1; font-size: 14px; width: 100%;">
                    <button type="submit" class="search-mobile__btn" style="background-color: #ffa500; color: #000000; border-radius: 0 50px 50px 0; padding: 10px 15px; font-weight: bold; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer;"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <ul class="mobile-list">
                    <li class="mobile-item">
                        <a href="view_cart.php" class="mobile-item__link">Giỏ hàng</a>
                    </li>
                    <li class="mobile-item">
                        <a href="info_order.php" class="mobile-item__link">Thông tin đơn hàng</a>
                    </li>
                    <?php if (empty($_SESSION['customer_id'])) { ?>
                        <li class="mobile-item">
                            <a href="signup.php" class="mobile-item__link">Đăng Ký</a>
                        </li>
                        <li class="mobile-item">
                            <a href="login.php" class="mobile-item__link">Đăng Nhập</a>
                        </li>
                    <?php } else { ?>
                        <li class="mobile-item">
                            <a href="logout.php" class="mobile-item__link">Đăng Xuất</a>
                        </li>
                    <?php } ?>
                    <li class="mobile-item">
                        <div class="mobile-item__language">
                            <img src="./public/img/vi.svg" alt="">
                            <p class="mobile-item__language--name">VNG</p>
                            <span class="mobile-item__language--icon">
                                <i class="fa-solid fa-angle-down"></i>
                            </span>
                            <div class="mobile__language">
                                <ul class="mobile__language--list">
                                    <li class="change__language--item">
                                        <div class="change__language-vi">
                                            <img src="./public/img/vi.svg" alt="">
                                            <p class="change__language-name">Tiếng Việt</p>
                                            <span class="change__language-icon">
                                                <i class="fa-solid fa-check"></i>
                                            </span>
                                        </div>
                                    </li>
                                    <li class="change__language--item">
                                        <div class="change__language-vi">
                                            <img src="./public/img/en.svg" alt="">
                                            <p class="change__language-name">English</p>
                                        </div>
                                    </li>
                                    <li class="change__language--item">

                                        <div class="change__language-vi">
                                            <img src="./public/img/ko.svg" alt="">
                                            <p class="change__language-name">Korean</p>
                                        </div>
                                    </li>
                                    <li class="change__language--item">
                                        <div class="change__language-vi">
                                            <strong>VNG</strong>
                                            <p class="change__language-name">Việt Nam Đồng</p>
                                            <span class="change__language-icon">
                                                <i class="fa-solid fa-check"></i>
                                            </span>
                                        </div>
                                    </li>
                                    <li class="change__language--item">
                                        <div class="change__language-vi">
                                            <strong>USD</strong>
                                            <p class="change__language-name">United States Dollar</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li> 
                </ul>
            </nav>
        </header>
    </div>
</div>

<style>
/* Header styling */
.header-fixed {
    background-color: #ffffff !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05) !important;
    width: 100%;
    border-bottom: 1px solid #efefef;
    position: relative !important;
}

body, html {
    margin: 0;
    padding: 0;
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
}

.header-container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 15px;
}

.header__btn {
    background-color: #ffa500 !important;
    color: #000 !important;
    transition: all 0.3s ease !important;
    border: none !important;
    font-weight: 500 !important;
    border-radius: 0 50px 50px 0 !important;
    position: relative;
    overflow: hidden;
}

.header__btn:hover {
    background-color: #e69500 !important;
}

.header__btn:after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 0;
    border-radius: 100%;
    transform: scale(1, 1) translate(-50%);
    transform-origin: 50% 50%;
}

.header__btn:focus:not(:active)::after {
    animation: ripple 1s ease-out;
}

@keyframes ripple {
    0% {
        transform: scale(0, 0);
        opacity: 0.5;
    }
    20% {
        transform: scale(25, 25);
        opacity: 0.3;
    }
    100% {
        opacity: 0;
        transform: scale(40, 40);
    }
}

.header__input {
    border: none !important;
    font-size: 14px !important;
    border-radius: 0 !important;
}

.header__input:focus {
    outline: none !important;
    background-color: #fff !important;
}

/* Search form styling */
.header__search form:hover {
    box-shadow: 0 6px 15px rgba(255, 165, 0, 0.15) !important;
    transform: translateY(-2px);
}

.header__search form:focus-within {
    box-shadow: 0 6px 15px rgba(255, 165, 0, 0.15) !important;
    transform: translateY(-2px);
    border-color: #e69500 !important;
}

.header__search form {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
}

/* User menu styling */
.user__info {
    background-color: #ffffff !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important;
    border: 1px solid #efefef !important;
    border-radius: 0 !important;
    padding: 10px !important;
    width: 250px !important;
}

.user__info a, .user__info span {
    color: #333 !important;
}

.user__info a:hover {
    color: #ffa500 !important;
    background-color: #f8f8f8 !important;
}

.auth-link:hover, .logout-link:hover {
    background-color: #f8f8f8 !important;
    color: #ffa500 !important;
}

/* Mobile menu styling */
.navbar__mobile {
    background-color: #ffffff !important;
    border-radius: 0;
}

.mobile-item__link {
    color: #333 !important;
    border-bottom: 1px solid #efefef;
    padding: 15px !important;
    font-weight: 500;
}

.mobile-item__link:hover {
    background-color: #f8f8f8 !important;
    color: #ffa500 !important;
}

/* Cart styling */
.header__cart {
    background-color: #f8f8f8 !important;
    color: #333 !important;
    border: 1px solid #efefef !important;
    border-radius: 0 !important;
}

.header__cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.08) !important;
    color: #ffa500 !important;
}

.header__cart-notice {
    background-color: #ffa500 !important;
    color: #000 !important;
}

.bars__header-mobile svg {
    fill: #333 !important;
}

.header-overlay {
    background-color: rgba(0,0,0,0.5) !important;
}

.header__search-mobile {
    margin: 15px !important;
    transition: all 0.3s ease !important;
}

.header__search-mobile:focus-within {
    box-shadow: 0 6px 15px rgba(0,0,0,0.08) !important;
    transform: translateY(-2px);
    border-color: #e69500 !important;
}

.search-mobile__input {
    font-size: 14px !important;
}

.search-mobile__input:focus {
    outline: none !important;
}

.search-mobile__btn {
    background-color: #ffa500 !important;
    transition: all 0.3s ease !important;
}

.search-mobile__btn:hover {
    background-color: #e69500 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenu = document.querySelector('.header__user');
    const userInfo = document.querySelector('.user__info');
    
    // Thêm class cho user menu khi hover
    userMenu.addEventListener('mouseenter', function() {
        userInfo.classList.add('show-menu');
    });
    
    userMenu.addEventListener('mouseleave', function() {
        userInfo.classList.remove('show-menu');
    });
});

$(document).ready(function(){
    $.get('get_cart_count.php', function(count){
      $('#cart-count').text(count);
    });
});
</script>