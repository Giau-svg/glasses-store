<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION['level']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location:/Pure/admin/index.php?error=Vui lòng đăng nhập để tiếp tục');
    exit();
}

// Kết nối đến cơ sở dữ liệu
$connect = mysqli_connect('localhost', 'root', '', 'eyeglasses_shop');
if (!$connect) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Thiết lập UTF-8
mysqli_set_charset($connect, 'utf8');

// Kiểm tra ID
if (empty($_GET['id'])) {
    header('location:index.php?error=Phải truyền mã danh mục để sửa');
    exit();
}

$id = $_GET['id'];

// Kiểm tra cột id trong bảng categories
$sql_check = "SHOW COLUMNS FROM categories LIKE 'category_id'";
$result_check = mysqli_query($connect, $sql_check);
if (mysqli_num_rows($result_check) > 0) {
    // Nếu tồn tại cột category_id
    $sql = "SELECT * FROM categories WHERE category_id = '$id'";
    $id_field = 'category_id';
} else {
    // Nếu không tồn tại cột category_id, dùng id
    $sql = "SELECT * FROM categories WHERE id = '$id'";
    $id_field = 'id';
}

$result = mysqli_query($connect, $sql);

if (mysqli_num_rows($result) == 0) {
    header('location:index.php?error=Danh mục không tồn tại');
    exit();
}

$each = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Cập nhật danh mục</title>

    <!-- Custom fonts for this template-->
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .image-preview {
            max-width: 200px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        .image-preview img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include "../partials/sidebar.php"; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include "../partials/topbar.php"; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Cập nhật danh mục</h1>
                        <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Danh sách danh mục
                        </a>
                    </div>

                    <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Main Content -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin danh mục</h6>
                        </div>
                        <div class="card-body">
                            <form action="process_update.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $each[$id_field]; ?>">
                                <input type="hidden" name="id_field" value="<?php echo $id_field; ?>">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label" for="name">Tên danh mục (*)</label>
                                            <input class="form-control" type="text" name="name" id="name" value="<?php echo htmlspecialchars($each['category_name']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="type">Loại danh mục (Type)</label>
                                            <select class="form-control" name="type" id="type">
                                                <option value="men" <?php echo isset($each['type']) && $each['type'] == 'men' ? 'selected' : ''; ?>>Kính Nam (men)</option>
                                                <option value="women" <?php echo isset($each['type']) && $each['type'] == 'women' ? 'selected' : ''; ?>>Kính Nữ (women)</option>
                                                <option value="sunglasses" <?php echo isset($each['type']) && $each['type'] == 'sunglasses' ? 'selected' : ''; ?>>Kính Mát (sunglasses)</option>
                                                <option value="children" <?php echo isset($each['type']) && $each['type'] == 'children' ? 'selected' : ''; ?>>Kính Trẻ Em (children)</option>
                                                <option value="lens" <?php echo isset($each['type']) && $each['type'] == 'lens' ? 'selected' : ''; ?>>Tròng Kính (lens)</option>
                                                <option value="accessories" <?php echo isset($each['type']) && $each['type'] == 'accessories' ? 'selected' : ''; ?>>Phụ Kiện (accessories)</option>
                                                <option value="other" <?php echo isset($each['type']) && $each['type'] == 'other' ? 'selected' : ''; ?>>Khác</option>
                                            </select>
                                            <small class="form-text text-muted">Loại danh mục sẽ được sử dụng để hiển thị sản phẩm trên menu.</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="description">Mô tả</label>
                                            <textarea class="form-control" name="description" id="description" rows="4"><?php echo htmlspecialchars($each['description']); ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Hình ảnh hiện tại</label>
                                            <div class="image-preview mb-3">
                                                <?php 
                                                $image_exists = false;
                                                if (!empty($each['image'])) {
                                                    $image_path = '../' . $each['image'];
                                                    if (file_exists($image_path)) {
                                                        $image_exists = true;
                                                    }
                                                }
                                                
                                                if ($image_exists): 
                                                ?>
                                                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($each['category_name']); ?>" id="preview-image" class="img-fluid">
                                                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($each['image']); ?>">
                                                <?php else: ?>
                                                    <img src="../public/img/no-image.png" alt="Không có hình ảnh" id="preview-image" class="img-fluid">
                                                <?php endif; ?>
                                            </div>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                            <small class="form-text text-muted">Chọn hình ảnh mới để thay thế (để trống nếu không muốn thay đổi)</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="index.php" class="btn btn-secondary mr-2">Hủy</a>
                                    <button class="btn btn-primary" type="submit">Cập nhật danh mục</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php include "../partials/footer.php"; ?>
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

    <script>
        // Preview ảnh khi upload
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('preview-image');
            
            if (imageInput && imagePreview) {
                imageInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        });
    </script>
</body>

</html>