<?php
session_start();
require 'admin/root.php';

// If payment was successful, process and redirect
if (isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    $order_id = $_POST['order_id'] ?? 0;
    
    // If there's order ID in the form, update the payment method for this order
    if ($order_id > 0) {
        $sql = "UPDATE orders SET payment_method = '$payment_method' WHERE order_id = $order_id";
        mysqli_query($connect, $sql);
    }
    
    // If payment needs processing (anything but COD), redirect to appropriate page
    switch ($payment_method) {
        case 'bank_transfer':
            // Store payment info in session
            $_SESSION['payment_info'] = [
                'method' => 'bank_transfer',
                'order_id' => $order_id
            ];
            header('location:view_order.php?payment=pending');
            exit;
        case 'credit_card':
            // In a real app, redirect to credit card processor
            $_SESSION['payment_info'] = [
                'method' => 'credit_card',
                'order_id' => $order_id
            ];
            header('location:view_order.php?payment=completed');
            exit;
        case 'momo':
        case 'vnpay':
        case 'zalopay':
            // In a real app, redirect to e-wallet processor
            $_SESSION['payment_info'] = [
                'method' => $payment_method,
                'order_id' => $order_id
            ];
            header('location:view_order.php?payment=completed');
            exit;
        case 'cod':
        default:
            // Cash on delivery, no processing needed
            $_SESSION['payment_info'] = [
                'method' => 'cod',
                'order_id' => $order_id
            ];
            header('location:view_order.php');
            exit;
    }
}

// Get the order ID from session
$order_id = $_SESSION['last_order_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phương thức thanh toán - SHOPDIENTU</title>
    <link rel="stylesheet" href="./public/css/rss.css" />
    <link rel="stylesheet" href="./public/css/style.css" />
    <link rel="stylesheet" href="./public/css/view_all.css" />
    <link rel="stylesheet" href="./public/css/breadcrumb.css" />
    
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <style>
        .payment-options {
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .payment-option {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-option:hover {
            border-color: #f26522;
            background-color: #fff9f6;
        }
        .payment-option.active {
            border-color: #f26522;
            background-color: #fff9f6;
        }
        .payment-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            color: #f26522;
        }
        .payment-option .payment-info {
            flex: 1;
        }
        .payment-option .payment-info h3 {
            margin: 0 0 5px;
            font-size: 16px;
        }
        .payment-option .payment-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .payment-radio {
            margin-right: 10px;
        }
        .payment-submit {
            background-color: #f26522;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
            width: 100%;
        }
        .payment-submit:hover {
            background-color: #d35400;
        }
        .bank-info {
            background-color: #f9f9f9;
            padding: 15px;
            margin-top: 10px;
            border-radius: 5px;
            border-left: 3px solid #f26522;
            display: none;
        }
        .bank-info.show {
            display: block;
        }
        .payment-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            border-left: 4px solid #f26522;
            padding-left: 10px;
        }
        .payment-subtitle {
            font-size: 16px;
            margin-bottom: 15px;
            color: #666;
        }
        .order-summary {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .order-summary h3 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-total {
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
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
                                <div class="col col-8">
                                    <div class="payment-options">
                                        <h2 class="payment-title">Chọn phương thức thanh toán</h2>
                                        <p class="payment-subtitle">Hãy chọn phương thức thanh toán phù hợp với bạn</p>
                                        
                                        <form action="" method="post" id="payment-form">
                                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                            
                                            <div class="payment-option" onclick="selectPayment('cod')">
                                                <input type="radio" name="payment_method" id="cod" value="cod" class="payment-radio" checked>
                                                <div class="payment-icon">
                                                    <i class="fas fa-money-bill-wave fa-2x"></i>
                                                </div>
                                                <div class="payment-info">
                                                    <h3>Thanh toán khi nhận hàng (COD)</h3>
                                                    <p>Quý khách sẽ thanh toán bằng tiền mặt khi nhận hàng</p>
                                                </div>
                                            </div>
                                            
                                            <div class="payment-option" onclick="selectPayment('bank_transfer')">
                                                <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" class="payment-radio">
                                                <div class="payment-icon">
                                                    <i class="fas fa-university fa-2x"></i>
                                                </div>
                                                <div class="payment-info">
                                                    <h3>Chuyển khoản ngân hàng</h3>
                                                    <p>Chuyển khoản qua ngân hàng với nội dung là mã đơn hàng</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bank-info" id="bank-info">
                                                <p><strong>Thông tin tài khoản:</strong></p>
                                                <p>Ngân hàng: Vietcombank</p>
                                                <p>Số tài khoản: 1234567890</p>
                                                <p>Chủ tài khoản: SHOP DIEN TU</p>
                                                <p>Nội dung chuyển khoản: Thanh toan don hang #<?php echo $order_id; ?></p>
                                            </div>
                                            
                                            <div class="payment-option" onclick="selectPayment('credit_card')">
                                                <input type="radio" name="payment_method" id="credit_card" value="credit_card" class="payment-radio">
                                                <div class="payment-icon">
                                                    <i class="fas fa-credit-card fa-2x"></i>
                                                </div>
                                                <div class="payment-info">
                                                    <h3>Thẻ tín dụng/Ghi nợ</h3>
                                                    <p>Thanh toán qua thẻ Visa, Mastercard, JCB</p>
                                                </div>
                                            </div>
                                            
                                            <div class="payment-option" onclick="selectPayment('momo')">
                                                <input type="radio" name="payment_method" id="momo" value="momo" class="payment-radio">
                                                <div class="payment-icon" style="color: #ae2070;">
                                                    <i class="fas fa-wallet fa-2x"></i>
                                                </div>
                                                <div class="payment-info">
                                                    <h3>Ví điện tử MoMo</h3>
                                                    <p>Thanh toán qua ví MoMo</p>
                                                </div>
                                            </div>
                                            
                                            <div class="payment-option" onclick="selectPayment('vnpay')">
                                                <input type="radio" name="payment_method" id="vnpay" value="vnpay" class="payment-radio">
                                                <div class="payment-icon" style="color: #004a9c;">
                                                    <i class="fas fa-money-check-alt fa-2x"></i>
                                                </div>
                                                <div class="payment-info">
                                                    <h3>Thanh toán qua VNPay</h3>
                                                    <p>Thanh toán qua VNPay, hỗ trợ nhiều ngân hàng tại Việt Nam</p>
                                                </div>
                                            </div>
                                            
                                            <div class="payment-option" onclick="selectPayment('zalopay')">
                                                <input type="radio" name="payment_method" id="zalopay" value="zalopay" class="payment-radio">
                                                <div class="payment-icon" style="color: #0068ff;">
                                                    <i class="fas fa-qrcode fa-2x"></i>
                                                </div>
                                                <div class="payment-info">
                                                    <h3>Thanh toán qua ZaloPay</h3>
                                                    <p>Thanh toán nhanh chóng qua ví ZaloPay</p>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="payment-submit">Xác nhận thanh toán</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="col col-4">
                                    <div class="order-summary">
                                        <h3>Thông tin đơn hàng</h3>
                                        <?php
                                        // Get order info if order ID is available
                                        if ($order_id > 0) {
                                            $sql = "SELECT * FROM orders WHERE order_id = $order_id";
                                            $result = mysqli_query($connect, $sql);
                                            $order = mysqli_fetch_assoc($result);
                                            
                                            if ($order) {
                                                ?>
                                                <div class="summary-item">
                                                    <span>Mã đơn hàng:</span>
                                                    <span>#<?php echo $order_id; ?></span>
                                                </div>
                                                <div class="summary-item">
                                                    <span>Người nhận:</span>
                                                    <span><?php echo $order['shipping_name']; ?></span>
                                                </div>
                                                <div class="summary-item">
                                                    <span>Số điện thoại:</span>
                                                    <span><?php echo $order['shipping_phone']; ?></span>
                                                </div>
                                                <div class="summary-item">
                                                    <span>Địa chỉ:</span>
                                                    <span><?php echo $order['shipping_address']; ?></span>
                                                </div>
                                                <div class="summary-item summary-total">
                                                    <span>Tổng tiền:</span>
                                                    <span><?php echo number_format($order['total_amount'], 0, ',', '.') . ' đ'; ?></span>
                                                </div>
                                                <?php
                                            } else {
                                                echo "<p>Không tìm thấy thông tin đơn hàng.</p>";
                                            }
                                        } else {
                                            echo "<p>Không có đơn hàng để thanh toán.</p>";
                                        }
                                        ?>
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
    <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="./public/js/js.js"></script>
    <script src="./public/js/slider.js"></script>
    <script src="./public/js/live-searchs.js"></script>
    <script>
        function selectPayment(method) {
            // Remove active class from all options
            document.querySelectorAll('.payment-option').forEach(function(option) {
                option.classList.remove('active');
            });
            
            // Add active class to selected option
            document.querySelector('.payment-option input[value="' + method + '"]').closest('.payment-option').classList.add('active');
            
            // Check the radio button
            document.getElementById(method).checked = true;
            
            // Show/hide bank info for bank transfer
            if (method === 'bank_transfer') {
                document.getElementById('bank-info').classList.add('show');
            } else {
                document.getElementById('bank-info').classList.remove('show');
            }
        }
        
        // Set initial active state
        document.addEventListener('DOMContentLoaded', function() {
            selectPayment('cod');
        });
    </script>
</body>

</html> 