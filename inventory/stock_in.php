<?php
require_once '../check_inventory_login.php';
require_once '../../admin/root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập trang hiện tại
$active_page = 'stock_in';
$page_title = 'Nhập kho sản phẩm';

// Lấy danh sách sản phẩm
$sql = "SELECT product_id, product_name, stock_quantity 
        FROM products 
        ORDER BY product_name ASC";
$products = mysqli_query($connect, $sql);

// Lấy danh sách nhà cung cấp
$sql = "SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC";
$suppliers = mysqli_query($connect, $sql);

// Kiểm tra nếu có preselect sản phẩm (từ trang low_stock)
$selected_product = isset($_GET['product_id']) ? $_GET['product_id'] : '';

// Xử lý form nhập kho
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy thông tin phiếu nhập
    $supplier_id = $_POST['supplier_id'];
    $receipt_date = date('Y-m-d H:i:s');
    $notes = $_POST['notes'];
    $inventory_manager_id = $_SESSION['staff_user_id'];
    
    // Lấy thông tin sản phẩm nhập
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $unit_prices = $_POST['unit_price'];
    
    // Tính tổng tiền
    $total_amount = 0;
    for ($i = 0; $i < count($product_ids); $i++) {
        if (!empty($product_ids[$i]) && !empty($quantities[$i]) && !empty($unit_prices[$i])) {
            $total_amount += $quantities[$i] * $unit_prices[$i];
        }
    }
    
    // Bắt đầu transaction
    mysqli_begin_transaction($connect);
    
    try {
        // Tạo phiếu nhập
        $sql = "INSERT INTO stock_receipts (supplier_id, receipt_date, total_amount, notes, inventory_manager_id) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "isdsi", $supplier_id, $receipt_date, $total_amount, $notes, $inventory_manager_id);
        mysqli_stmt_execute($stmt);
        
        // Lấy ID phiếu nhập mới tạo
        $receipt_id = mysqli_insert_id($connect);
        
        // Thêm chi tiết phiếu nhập và cập nhật số lượng sản phẩm
        for ($i = 0; $i < count($product_ids); $i++) {
            if (!empty($product_ids[$i]) && !empty($quantities[$i]) && !empty($unit_prices[$i])) {
                $product_id = $product_ids[$i];
                $quantity = $quantities[$i];
                $unit_price = $unit_prices[$i];
                $subtotal = $quantity * $unit_price;
                
                // Thêm chi tiết phiếu nhập
                $sql = "INSERT INTO stock_receipt_details (receipt_id, product_id, quantity, unit_price, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($connect, $sql);
                mysqli_stmt_bind_param($stmt, "iiidi", $receipt_id, $product_id, $quantity, $unit_price, $subtotal);
                mysqli_stmt_execute($stmt);
                
                // Cập nhật số lượng sản phẩm
                $sql = "UPDATE products 
                        SET stock_quantity = stock_quantity + ?, 
                            cost_price = (cost_price + ?) / 2,
                            updated_at = ?
                        WHERE product_id = ?";
                $stmt = mysqli_prepare($connect, $sql);
                mysqli_stmt_bind_param($stmt, "idsi", $quantity, $unit_price, $receipt_date, $product_id);
                mysqli_stmt_execute($stmt);
            }
        }
        
        // Commit transaction
        mysqli_commit($connect);
        
        $success_message = "Nhập kho thành công! Mã phiếu nhập: #" . $receipt_id;
        
        // Reset form
        $selected_product = '';
        
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        mysqli_rollback($connect);
        $error_message = "Lỗi: " . $e->getMessage();
    }
}

// Hàm định dạng số tiền VND
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
    <style>
        .product-row td {
            vertical-align: middle;
        }
        .remove-product {
            color: #e74a3b;
            cursor: pointer;
        }
    </style>
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include 'inventory_sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include 'inventory_topbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                    </div>

                    <?php if(!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Form nhập kho -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu nhập kho</h6>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" id="stock-in-form">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="supplier_id">Nhà cung cấp <span class="text-danger">*</span></label>
                                        <select class="form-control" id="supplier_id" name="supplier_id" required>
                                            <option value="">-- Chọn nhà cung cấp --</option>
                                            <?php while($supplier = mysqli_fetch_assoc($suppliers)): ?>
                                                <option value="<?php echo $supplier['supplier_id']; ?>">
                                                    <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="receipt_date">Ngày nhập kho</label>
                                        <input type="text" class="form-control" id="receipt_date" value="<?php echo date('d/m/Y H:i'); ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="notes">Ghi chú</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                </div>

                                <h6 class="mt-4 mb-3 font-weight-bold text-primary">Chi tiết sản phẩm nhập</h6>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="products-table">
                                        <thead>
                                            <tr>
                                                <th width="40%">Sản phẩm <span class="text-danger">*</span></th>
                                                <th width="15%">Số lượng <span class="text-danger">*</span></th>
                                                <th width="20%">Đơn giá <span class="text-danger">*</span></th>
                                                <th width="20%">Thành tiền</th>
                                                <th width="5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="product-row">
                                                <td>
                                                    <select class="form-control product-select" name="product_id[]" required>
                                                        <option value="">-- Chọn sản phẩm --</option>
                                                        <?php 
                                                        mysqli_data_seek($products, 0);
                                                        while($product = mysqli_fetch_assoc($products)): 
                                                        ?>
                                                            <option value="<?php echo $product['product_id']; ?>" 
                                                                    <?php echo ($product['product_id'] == $selected_product) ? 'selected' : ''; ?>
                                                                    data-stock="<?php echo $product['stock_quantity']; ?>">
                                                                <?php echo htmlspecialchars($product['product_name'] . ' - Tồn: ' . $product['stock_quantity']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control quantity" name="quantity[]" min="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control unit-price" name="unit_price[]" min="0" step="1000" required>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control subtotal" readonly>
                                                </td>
                                                <td class="text-center">
                                                    <i class="fas fa-times remove-product"></i>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5">
                                                    <button type="button" class="btn btn-success btn-sm" id="add-product">
                                                        <i class="fas fa-plus-circle"></i> Thêm sản phẩm
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right font-weight-bold">Tổng tiền:</td>
                                                <td>
                                                    <input type="text" class="form-control" id="total-amount" readonly>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Lưu phiếu nhập
                                    </button>
                                    <a href="stock_history.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Hủy
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php include '../partials/footer.php'; ?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Bạn muốn đăng xuất?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Chọn "Đăng xuất" bên dưới nếu bạn muốn kết thúc phiên làm việc hiện tại.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                    <a class="btn btn-primary" href="../logout.php">Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for dynamic form handling -->
    <script>
        $(document).ready(function() {
            // Tính tổng tiền
            function calculateTotals() {
                let total = 0;
                $('.product-row').each(function() {
                    let quantity = $(this).find('.quantity').val() || 0;
                    let unitPrice = $(this).find('.unit-price').val() || 0;
                    let subtotal = quantity * unitPrice;
                    
                    $(this).find('.subtotal').val(subtotal.toLocaleString('vi-VN') + ' đ');
                    total += subtotal;
                });
                
                $('#total-amount').val(total.toLocaleString('vi-VN') + ' đ');
            }
            
            // Tính lại khi thay đổi số lượng hoặc đơn giá
            $(document).on('input', '.quantity, .unit-price', function() {
                calculateTotals();
            });
            
            // Thêm sản phẩm mới
            $('#add-product').click(function() {
                let productRow = $('.product-row').first().clone();
                productRow.find('select, input').val('');
                $('#products-table tbody').append(productRow);
            });
            
            // Xóa sản phẩm
            $(document).on('click', '.remove-product', function() {
                if ($('.product-row').length > 1) {
                    $(this).closest('.product-row').remove();
                    calculateTotals();
                } else {
                    alert('Phải có ít nhất một sản phẩm!');
                }
            });
            
            // Kiểm tra dữ liệu trước khi submit
            $('#stock-in-form').submit(function(e) {
                let isValid = true;
                let productIds = [];
                
                $('.product-select').each(function() {
                    let productId = $(this).val();
                    if (productId) {
                        if (productIds.includes(productId)) {
                            alert('Sản phẩm đã được chọn! Vui lòng điều chỉnh số lượng thay vì chọn lại.');
                            isValid = false;
                            return false;
                        }
                        productIds.push(productId);
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            // Calculate totals on page load
            calculateTotals();
        });
    </script>
</body>
</html> 