<?php
    require '../check_admin_login.php';
    require '../root.php';

    $sql = "SELECT * FROM `manufactures`";
    $result = mysqli_query($connect, $sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    
    <?php
        include '../partials/head_view.php';
    ?>

    <title>Thêm quảng cáo mới</title>
    <style>
        .image-preview {
            max-width: 300px;
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
        .card {
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }
        .card-body {
            padding: 20px;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .form-label {
            font-weight: 500;
        }
        
        select option {
            padding: 8px 15px;
        }
        
        select {
            padding: 8px 12px;
            height: auto;
        }
    </style>
</head>

<body id="page-top">
    <?php
    include '../partials/header_view.php';
    ?>

    <div class="container-fluid">
        <?php 
            require '../menu.php';
        ?>
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Thêm quảng cáo mới</h1>
            <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Danh sách quảng cáo
            </a>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin quảng cáo mới</h6>
                    </div>
                    <div class="card-body">
                        <form action="process_insert.php" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="manufacturer_id">Nhà sản xuất</label>
                                        <select class="form-control" name="manufacturer_id" id="manufacturer_id" required>
                                            <option value="">-- Chọn nhà sản xuất --</option>
                                            <?php foreach ($result as $each) : ?>
                                                <option value="<?php echo $each['id'] ?>">
                                                    <?php echo htmlspecialchars($each['name']); ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                        <small class="form-text text-muted">Chọn nhà sản xuất liên quan đến quảng cáo</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label" for="rules">Vị trí hiển thị</label>
                                        <select class="form-control" name="rules" id="rules" required>
                                            <option value="">-- Chọn vị trí hiển thị --</option>
                                            <option value="1">Hiển thị ở slide chính (Active slide)</option>
                                            <option value="2">Hiển thị ở thanh bên (Active sidebar)</option>
                                            <option value="3">Hiển thị ở khu vực độc quyền (Active exclusivenes)</option>
                                        </select>
                                        <small class="form-text text-muted">Chọn vị trí hiển thị quảng cáo trên trang web</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="photo">Ảnh quảng cáo(*)</label>
                                        <input class="form-control" type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/gif,image/webp" required>
                                        <small class="form-text text-muted">Chấp nhận định dạng: JPG, PNG, GIF, WEBP</small>
                                    </div>
                                    
                                    <div class="image-preview">
                                        <img src="../public/img/no-image.svg" alt="No image" id="preview-image">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <a href="index.php" class="btn btn-secondary mr-2">Hủy</a>
                                <button class="btn btn-primary" type="submit">Thêm quảng cáo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include '../partials/footer_view.php';
    include '../partials/js_link.php';
    ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Preview ảnh khi upload
            const imageInput = document.getElementById('photo');
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