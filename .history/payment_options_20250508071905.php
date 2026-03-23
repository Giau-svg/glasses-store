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
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .payment-option {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
        }
        .payment-option:hover {
            border-color: #ffa500;
            background-color: #fff9f6;
            transform: translateY(-2px);
        }
        .payment-option.active {
            border-color: #ffa500;
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
            border-radius: 8px;
            color: #ffa500;
        }
        .payment-option .payment-info {
            flex: 1;
        }
        .payment-option .payment-info h3 {
            margin: 0 0 5px;
            font-size: 16px;
            color: #333;
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
            background-color: #ffa500;
            color: #fff;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            width: 100%;
            font-weight: 500;
        }
        .payment-submit:hover {
            background-color: #e69500;
            transform: translateY(-2px);
        }
        .bank-info {
            background-color: #f9f9f9;
            padding: 15px;
            margin-top: 10px;
            border-radius: 8px;
            border-left: 3px solid #ffa500;
            display: none;
        }
        .bank-info.show {
            display: block;
        }
        .payment-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            border-left: 4px solid #ffa500;
            padding-left: 10px;
        }
        .payment-subtitle {
            font-size: 16px;
            margin-bottom: 15px;
            color: #666;
        }
        .order-summary {
            background-color: #fff;
            padding: 28px 28px 24px 28px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            margin-bottom: 20px;
            font-size: 18px;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }
        .order-summary h3 {
            font-size: 24px;
            margin-top: 0;
            border-bottom: 2px solid #ffa500;
            padding-bottom: 14px;
            margin-bottom: 22px;
            color: #333;
        }
        .order-summary h3 i { margin-right: 8px; }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            color: #444;
            font-size: 18px;
        }
        .summary-item i { margin-right: 6px; color: #ffa500; }
        .summary-total {
            font-weight: bold;
            border-top: 2px solid #ffa500;
            padding-top: 16px;
            margin-top: 20px;
            color: #333;
            font-size: 20px;
        }
        .summary-total span:last-child {
            color: #ffa500;
            font-size: 26px;
        }
        .footer-shop {
            background: #fff;
            border-top: 1px solid #eee;
            padding: 24px 0;
            color: #333;
            text-align: center;
            font-size: 15px;
        }
        .footer-shop a { color: #ffa500; margin: 0 4px; }
        .footer-container { display: flex; justify-content: space-between; max-width: 900px; margin: 0 auto; }
        @media (max-width: 600px) { .footer-container { flex-direction: column; gap: 12px; } }
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
                                        <h3><i class="fas fa-receipt" style="color:#ffa500"></i> Thông tin đơn hàng</h3>
                                        <?php
                                        // Get order info if order ID is available
                                        if ($order_id > 0) {
                                            $sql = "SELECT * FROM orders WHERE order_id = $order_id";
                                            $result = mysqli_query($connect, $sql);
                                            $order = mysqli_fetch_assoc($result);
                                            
                                            if ($order) {
                                                ?>
                                                <div class="summary-item">
                                                    <span><i class="fas fa-barcode"></i> Mã đơn hàng:</span>
                                                    <span>#<?php echo $order_id; ?></span>
                                                </div>
                                                <div class="summary-item">
                                                    <span><i class="fas fa-user"></i> Người nhận:</span>
                                                    <span><?php echo $order['shipping_name']; ?></span>
                                                </div>
                                                <div class="summary-item">
                                                    <span><i class="fas fa-phone"></i> Số điện thoại:</span>
                                                    <span><?php echo $order['shipping_phone']; ?></span>
                                                </div>
                                                <div class="summary-item">
                                                    <span><i class="fas fa-map-marker-alt"></i> Địa chỉ:</span>
                                                    <span><?php echo $order['shipping_address']; ?></span>
                                                </div>
                                                <div class="summary-item summary-total" style="background:#fff9f6;border-radius:8px;">
                                                    <span><i class="fas fa-money-bill-wave" style="color:#ffa500"></i> Tổng tiền:</span>
                                                    <span style="color:#ffa500;font-size:22px;font-weight:bold;"><?php echo number_format($order['total_amount'], 0, ',', '.') . ' đ'; ?></span>
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
                </div>
                <div class="checkout-slogan" style="text-align:center; margin:32px 0 0 0; color:#ffa500; font-size:18px; font-weight:500; letter-spacing:1px;">
                    "Mắt kính đẹp - Phong cách mới, tự tin mỗi ngày!"
                </div>
            </div>
        </div>
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