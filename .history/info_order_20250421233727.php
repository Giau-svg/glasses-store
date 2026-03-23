<?php

session_start();
require 'admin/root.php';

if(!isset($_SESSION['customer_id'])) {
    echo "Bạn chưa đăng nhập. Vui lòng <a href='login.php'>đăng nhập</a> để xem đơn hàng.";
    exit();
}

$customer_id = $_SESSION['customer_id'];

$sql = "SELECT o.*, od.*, 
        p.product_name, p.image_path
        FROM orders o
        JOIN order_details od ON o.order_id = od.order_id
        JOIN products p ON od.product_id = p.product_id
        WHERE o.user_id = '$customer_id'
        ORDER BY o.order_id DESC";

$result = mysqli_query($connect, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($connect));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đơn hàng - EYEGLASSES</title>
    <link rel="stylesheet" href="./public/css/rss.css" />
    <link rel="stylesheet" href="./public/css/style.css" />
    <link rel="stylesheet" href="./public/css/view_all.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <style>
        .order-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .order-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #333;
        }
        .order-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .order-table tr:hover {
            background-color: #f8f9fa;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .product-name {
            font-weight: 500;
            color: #333;
        }
        .price {
            color: #ff6b6b;
            font-weight: 500;
        }
        .no-orders {
            text-align: center;
            padding: 40px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .shop-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background: #ff6b6b;
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .shop-link:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include './partials/sticky.php' ?>
        
        <div class="order-container">
            <h2 style="text-align: center; margin-bottom: 30px; color: #333;">Lịch Sử Đơn Hàng</h2>
            
            <?php if(mysqli_num_rows($result) > 0) { ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Ngày đặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td>#<?php echo $row['order_id']; ?></td>
                                <td>
                                    <div class="product-info">
                                        <img src="admin/products/uploads/<?php echo $row['image_path']; ?>" 
                                             alt="<?php echo $row['product_name']; ?>" 
                                             class="product-image">
                                        <span class="product-name"><?php echo $row['product_name']; ?></span>
                                    </div>
                                </td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td class="price"><?php echo number_format($row['unit_price'], 0, ',', '.'); ?>đ</td>
                                <td><?php echo date('d/m/Y', strtotime($row['order_date'])); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="no-orders">
                    <p>Bạn chưa có đơn hàng nào.</p>
                    <a href="index.php" class="shop-link">Tiếp tục mua sắm</a>
                </div>
            <?php } ?>
        </div>

        <?php include './partials/footer.php' ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="./public/js/js.js"></script>
    <script src="./public/js/slider.js"></script>
    <script src="./public/js/live-search.js"></script>
</body>

</html>