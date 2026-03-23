<?php
require '../check_admin_login.php';
require '../root.php';

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
    <title>Thêm Sản Phẩm Mới - EYEGLASSES</title>
    
    <!-- Custom fonts for this template-->
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles for this template-->
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">
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
                        <h1 class="h3 mb-0 text-gray-800">Thêm Sản Phẩm Mới</h1>
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
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin sản phẩm</h6>
                        </div>
                        <div class="card-body">
                            <form action="process_insert.php" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="product_name">Tên Sản Phẩm <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="price">Giá bán <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="price" name="price" min="0" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="cost_price">Giá nhập <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="cost_price" name="cost_price" min="0">
                                            <small class="form-text text-muted">Nếu bỏ trống, giá nhập sẽ được tính là 70% giá bán.</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="description">Mô tả</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stock_quantity">Số lượng trong kho <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="category_id">Danh mục</label>
                                            <select class="form-control" id="category_id" name="category_id">
                                                <option value="">-- Chọn danh mục --</option>
                                                <?php while ($category = mysqli_fetch_assoc($resultCategories)): ?>
                                                    <option value="<?php echo $category['id']; ?>">
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
                                                    <option value="<?php echo $brand['brand_id']; ?>">
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
                                                    <option value="<?php echo $manufacturer['id']; ?>">
                                                        <?php echo htmlspecialchars($manufacturer['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="image">Hình ảnh sản phẩm <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required>
                                            <small class="form-text text-muted">Định dạng: JPG, PNG, GIF. Tối đa 2MB.</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary">Thêm Sản Phẩm</button>
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
                        // Tạo hoặc hiển thị container nếu chưa có
                        if ($('#image-preview-container').length === 0) {
                            $('<div id="image-preview-container" class="mt-2" style="max-width:200px; border:1px solid #ddd; padding:5px; border-radius:5px;"><img id="image-preview" class="img-fluid" /></div>').insertAfter('#image');
                        } else {
                            $('#image-preview-container').show();
                        }
                        $('#image-preview').attr('src', event.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>