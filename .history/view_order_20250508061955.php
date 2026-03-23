<?php

session_start();
require 'admin/root.php';

// Get payment information from the URL parameter or session
$payment_status = $_GET['payment'] ?? '';
$payment_info = $_SESSION['payment_info'] ?? ['method' => 'cod'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đơn hàng</title>
    <link rel="stylesheet" href="./public/css/rss.css" />
    <link rel="stylesheet" href="./public/css/style.css" />
    <link rel="stylesheet" href="./public/css/view_all.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <style>
        .order_success {
            text-align: center;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
            animation: fadeIn 0.8s ease-in-out;
        }
        .order_success h2 {
            color: #28a745;
            margin-bottom: 20px;
            font-size: 28px;
            position: relative;
            display: inline-block;
        }
        .order_success h2:after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: #f26522;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .order_img {
            display: block;
            max-width: 150px;
            margin: 0 auto 20px;
            animation: bounceIn 1s ease;
        }
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        .payment-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 500px;
            text-align: left;
            border-left: 3px solid #f26522;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffecb5;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 500px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 500px;
        }
        .btn-view-order {
            display: inline-block;
            background-color: #f26522;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 30px;
            font-weight: bold;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 8px rgba(242, 101, 34, 0.3);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
            z-index: 1;
            animation: pulse 2s infinite;
            width: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
        
        .btn-view-order:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
            z-index: -1;
        }
        
        .btn-view-order:hover:before {
            left: 100%;
        }
        
        .btn-view-order:hover {
            background-color: #e55511;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(242, 101, 34, 0.4);
            animation: none;
        }
        
        .btn-view-order:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(242, 101, 34, 0.3);
        }
        .btn-view-order i {
            margin-right: 8px;
            font-size: 16px;
        }
        .order-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 30px auto;
            max-width: 700px;
            position: relative;
        }
        
        .order-card-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        
        .order-card-body {
            padding: 30px 20px;
        }
        
        .order-success-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background-color: #28a745;
            color: white;
            border-radius: 50%;
            margin-bottom: 20px;
            font-size: 40px;
        }
        
        @media (max-width: 768px) {
            .order-card {
                margin: 15px;
                border-radius: 8px;
            }
            
            .order-card-header {
                padding: 15px;
            }
            
            .order-card-body {
                padding: 20px 15px;
            }
            
            .order-success-icon {
                width: 60px;
                height: 60px;
                font-size: 30px;
                margin-bottom: 15px;
            }
            
            .order_success h2 {
                font-size: 22px;
            }
            
            .btn-view-order {
                padding: 10px 16px;
                font-size: 13px;
                width: auto;
                max-width: 90%;
                margin-top: 20px;
                white-space: nowrap;
                letter-spacing: 0;
            }
            
            .alert-warning, .alert-success, .payment-info {
                padding: 12px;
                margin: 15px auto;
            }
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(242, 101, 34, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(242, 101, 34, 0); }
            100% { box-shadow: 0 0 0 0 rgba(242, 101, 34, 0); }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include './partials/sticky.php' ?>
        <div class="container">
            <div class="grid_full-width">
                <?php include './partials/menu.php' ?>
                <div class="grid_full-width content">
                    <div class="content__brands">
                        <?php include './partials/slider.php' ?>
                        <div class="grid">
                            <div class="row">
                                <div class="col col-full">
                                    <div class="grid table_cart-info">
                                        <div class="row-table_cart">
                                            <div class="col-table col-table-5">
                                                <div class="order-card">
                                                    <div class="order-card-header">
                                                        <div class="order-success-icon">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                        <h2>Đặt hàng thành công</h2>
                                                    </div>
                                                    <div class="order-card-body">
                                                        <div class="order_success">
                                                            <?php if($payment_status == 'pending'): ?>
                                                                <div class="alert-warning">
                                                                    <h3><i class="fas fa-clock"></i> Đang chờ thanh toán</h3>
                                                                    <p>Đơn hàng của bạn đã được tạo. Vui lòng hoàn tất thanh toán để chúng tôi xử lý đơn hàng.</p>
                                                                </div>
                                                                <?php if($payment_info['method'] == 'bank_transfer'): ?>
                                                                    <div class="payment-info">
                                                                        <h3>Thông tin chuyển khoản:</h3>
                                                                        <p>Ngân hàng: Vietcombank</p>
                                                                        <p>Số tài khoản: 1234567890</p>
                                                                        <p>Chủ tài khoản: SHOP DIEN TU</p>
                                                                        <p>Nội dung chuyển khoản: Thanh toan don hang #<?php echo $_SESSION['last_order_id'] ?? ''; ?></p>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php elseif($payment_status == 'completed'): ?>
                                                                <div class="alert-success">
                                                                    <h3><i class="fas fa-check-circle"></i> Thanh toán thành công</h3>
                                                                    <p>Đơn hàng của bạn đã được thanh toán thành công qua <?php 
                                                                        switch($payment_info['method']) {
                                                                            case 'credit_card':
                                                                                echo 'thẻ tín dụng/ghi nợ';
                                                                                break;
                                                                            case 'momo':
                                                                                echo 'MoMo';
                                                                                break;
                                                                            case 'vnpay':
                                                                                echo 'VNPay';
                                                                                break;
                                                                            case 'zalopay':
                                                                                echo 'ZaloPay';
                                                                                break;
                                                                            default:
                                                                                echo 'hình thức thanh toán trực tuyến';
                                                                        }
                                                                    ?>.</p>
                                                                </div>
                                                            <?php else: ?>
                                                                <p>Đơn hàng của bạn sẽ được xử lý và giao đến bạn trong thời gian sớm nhất.</p>
                                                                <?php if($payment_info['method'] == 'cod'): ?>
                                                                    <p>Bạn sẽ thanh toán khi nhận hàng.</p>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                            
                                                            <a href="./info_order.php" class="btn btn-primary" style="display: inline-block; background-color: #f26522; border: none; color: white; padding: 10px 15px; text-align: center; text-decoration: none; font-size: 14px; margin-top: 20px; cursor: pointer; border-radius: 5px; font-weight: bold;">
                                                                <i class="fas fa-eye"></i> Xem chi tiết
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include './partials/slidebar.php' ?>
                </div>
                <div class="footer">
                    <div class="grid_full-width">
                        <div class="grid">
                            <div class="row">
                                <div class="col col-4 col-mobi">
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
                                <div class="col col-4 col-mobi">
                                    <div class="footer__about">
                                        <h3>Địa chỉ</h3>
                                    </div>
                                    <div class="footer__text">
                                        <p>
                                            B8A Võ Văn Dũng - Hoàng Cầu Đống Đa -
                                            Hà Nội
                                        </p>
                                        <p>
                                            Số 25 Ngõ 178/71 - Tây Sơn Đống Đa - Hà
                                            Nội
                                        </p>
                                    </div>
                                </div>
                                <div class="col col-4 col-mobi">
                                    <div class="footer__about">
                                        <h3>Dịch vụ</h3>
                                    </div>
                                    <div class="footer__text">
                                        <p>
                                            Bảo hành rơi vỡ, ngấm nước Care Diamond
                                        </p>
                                        <p>
                                            Bảo hành Care X60 rơi vỡ ngấm nước vẫn Đổi
                                            mới
                                        </p>
                                    </div>
                                </div>
                                <div class="col col-4 col-mobi">
                                    <div class="footer__about">
                                        <h3>Liên hệ</h3>
                                    </div>
                                    <div class="footer__text">
                                        <p>Phone Sale: <a href="tel:+00 151515">(+84) 0988 550 5535</a></p>
                                        <p>Email: <a href="mailto:vietpro.edu.vn@gmail.com">vietpro.edu.vn@gmail.com</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include './partials/footer.php' ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <!-- <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.0.min.js"></script> -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="./public/js/js.js"></script>
    <script src="./public/js/slider.js"></script>
    <script src="./public/js/live-searchs.js"></script>
    
</body>

</html>