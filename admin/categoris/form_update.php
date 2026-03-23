<?php
    require '../check_admin_login.php';
    require '../root.php';

    $id = $_GET['id'];
    $sql = "SELECT * FROM `categories`
        WHERE `category_id` = '$id'";
    $result = mysqli_query($connect, $sql);
    $each = mysqli_fetch_array($result);
    if(empty($each)) {
        header('location:../partials/404.php');
    }

    if (empty($_GET['id'])) {
        header('location:index.php?error=Phải truyền mã để sửa');
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    
    <?php
        include '../partials/head_view.php';
    ?>

    <title>Cập nhật danh mục</title>
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
            <h1 class="h3 mb-0 text-gray-800">Cập nhật danh mục</h1>
            <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Danh sách danh mục
            </a>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin danh mục</h6>
                    </div>
                    <div class="card-body">
                        <form action="process_update.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="category_id" value="<?php echo $each['category_id']; ?>">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label" for="category_name">Tên danh mục (*)</label>
                                        <input class="form-control" type="text" name="category_name" id="category_name" value="<?php echo htmlspecialchars($each['category_name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label" for="type">Loại danh mục (Type)</label>
                                        <select class="form-control" name="type" id="type">
                                            <option value="">-- Chọn loại --</option>
                                            <option value="men" <?php echo $each['type'] == 'men' ? 'selected' : ''; ?>>Kính Nam (men)</option>
                                            <option value="women" <?php echo $each['type'] == 'women' ? 'selected' : ''; ?>>Kính Nữ (women)</option>
                                            <option value="sunglasses" <?php echo $each['type'] == 'sunglasses' ? 'selected' : ''; ?>>Kính Mát (sunglasses)</option>
                                            <option value="children" <?php echo $each['type'] == 'children' ? 'selected' : ''; ?>>Kính Trẻ Em (children)</option>
                                            <option value="lens" <?php echo $each['type'] == 'lens' ? 'selected' : ''; ?>>Tròng Kính (lens)</option>
                                            <option value="accessories" <?php echo $each['type'] == 'accessories' ? 'selected' : ''; ?>>Phụ Kiện (accessories)</option>
                                            <option value="other" <?php echo $each['type'] == 'other' ? 'selected' : ''; ?>>Khác</option>
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
                                        <div class="image-preview">
                                            <?php if(!empty($each['image'])): ?>
                                                <img src="../../<?php echo $each['image']; ?>" alt="<?php echo $each['category_name']; ?>" id="preview-image">
                                                <input type="hidden" name="image_old" value="<?php echo $each['image']; ?>">
                                            <?php else: ?>
                                                <img src="../public/img/no-image.svg" alt="No image" id="preview-image">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label" for="image_new">Cập nhật hình ảnh mới</label>
                                        <input class="form-control" type="file" name="image_new" id="image_new" accept="image/jpeg,image/png,image/gif,image/webp">
                                        <small class="form-text text-muted">Để trống nếu muốn giữ hình ảnh cũ.</small>
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
        </div>
    </div>

    <?php
    include '../partials/footer_view.php';
    include '../partials/js_link.php';
    ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Preview ảnh khi upload
            const imageInput = document.getElementById('image_new');
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