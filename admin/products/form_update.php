<?php
require '../check_admin_login.php';
require '../root.php';

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=Không tìm thấy sản phẩm');
    exit;
}

$product_id = (int)$_GET['id'];

// Lấy thông tin sản phẩm
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: index.php?error=Không tìm thấy sản phẩm');
    exit;
}

$product = mysqli_fetch_assoc($result);

// Lấy danh sách danh mục
$sqlCategories = "SELECT category_id as id, category_name as name FROM categories ORDER BY category_name";
$resultCategories = mysqli_query($connect, $sqlCategories);

// Lấy danh sách nhà sản xuất
$sqlManufacturers = "SELECT id, name FROM manufacturers ORDER BY name";
$resultManufacturers = mysqli_query($connect, $sqlManufacturers);

// Lấy danh sách thương hiệu từ bảng brands
$sqlBrands = "SELECT brand_id, brand_name FROM brands ORDER BY brand_name";
$resultBrands = mysqli_query($connect, $sqlBrands);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Chỉnh Sửa Sản Phẩm - EYEGLASSES</title>
    
    <!-- Custom fonts for this template-->
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles for this template-->
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .image-preview-container {
            width: 100%;
            max-width: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
            display: <?php echo !empty($product['image_path']) ? 'block' : 'none'; ?>;
        }
        .image-preview {
            width: 100%;
            height: auto;
        }
    </style>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include '../partials/sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php include '../partials/topbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Chỉnh Sửa Sản Phẩm</h1>
                        <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
                        </a>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_GET['error']; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Product Form -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin sản phẩm #<?php echo $product_id; ?></h6>
                        </div>
                        <div class="card-body">
                            <form action="process_update.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="product_name">Tên Sản Phẩm <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="price">Giá <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="price" name="price" min="0" value="<?php echo $product['price']; ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="description">Mô tả</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stock_quantity">Số lượng trong kho <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo $product['stock_quantity']; ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="category_id">Danh mục</label>
                                            <select class="form-control" id="category_id" name="category_id">
                                                <option value="">-- Chọn danh mục --</option>
                                                <?php while ($category = mysqli_fetch_assoc($resultCategories)): ?>
                                                    <option value="<?php echo $category['id']; ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="brand_id">Thương hiệu <span class="text-danger">*</span></label>
                                            <select class="form-control" id="brand_id" name="brand_id" required>
                                                <option value="">-- Chọn thương hiệu --</option>
                                                <?php while ($brand = mysqli_fetch_assoc($resultBrands)): ?>
                                                    <option value="<?php echo $brand['brand_id']; ?>" <?php echo ($product['brand_id'] == $brand['brand_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($brand['brand_name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <small class="form-text text-muted">Thương hiệu sẽ hiển thị trên trang chủ.</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="manufacturer_id">Nhà sản xuất</label>
                                            <select class="form-control" id="manufacturer_id" name="manufacturer_id">
                                                <option value="">-- Chọn nhà sản xuất --</option>
                                                <?php while ($manufacturer = mysqli_fetch_assoc($resultManufacturers)): ?>
                                                    <option value="<?php echo $manufacturer['id']; ?>" <?php echo ($product['manufacturer_id'] == $manufacturer['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($manufacturer['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="image">Hình ảnh sản phẩm</label>
                                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                                            <small class="form-text text-muted">Định dạng: JPG, PNG, GIF. Tối đa 2MB. Để trống nếu không muốn thay đổi ảnh.</small>
                                            
                                            <?php if (!empty($product['image_path'])): ?>
                                                <div class="mt-2">
                                                    <div class="image-preview-container" id="image-preview-container">
                                                        <img src="<?php echo 'uploads/' . $product['image_path']; ?>" class="image-preview" id="image-preview" alt="Ảnh sản phẩm">
                                                    </div>
                                                    <div class="mt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1">
                                                            <label class="custom-control-label" for="remove_image">Xóa ảnh</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary">Cập Nhật Sản Phẩm</button>
                                    <a href="index.php" class="btn btn-secondary ml-2">Hủy</a>
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

    <!-- Bootstrap core JavaScript-->
    <script src="../public/vendor/jquery/jquery.min.js"></script>
    <script src="../public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../public/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../public/js/sb-admin-2.min.js"></script>

    <!-- Preview image before upload -->
    <script>
        $(document).ready(function() {
            $('#image').change(function() {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        $('#image-preview').attr('src', event.target.result);
                        $('#image-preview-container').show();
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Xử lý khi chọn checkbox xóa ảnh
            $('#remove_image').change(function() {
                if(this.checked) {
                    $('#image-preview-container').hide();
                } else {
                    $('#image-preview-container').show();
                }
            });
        });
    </script>
</body>
</html>