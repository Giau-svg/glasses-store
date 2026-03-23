<?php
    require '../check_admin_login.php';
    require '../root.php';

    $sql = "SELECT * FROM `brands`";
    $result = mysqli_query($connect, $sql);

    //Lấy ra tất cả các danh mục cha
    $type = isset($_GET['type']) ? trim($_GET['type']) : 'product';
    $sql_parent = "SELECT * FROM categoris WHERE parent_id = 0 AND type = ?";
    $stmt_parent = mysqli_prepare($connect, $sql_parent);
    mysqli_stmt_bind_param($stmt_parent, "s", $type);
    mysqli_stmt_execute($stmt_parent);
    $result_parent = mysqli_stmt_get_result($stmt_parent);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    
    <?php
        include '../partials/head_view.php';
    ?>

    <title>Thêm danh mục mới</title>
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
            <h1 class="h3 mb-0 text-gray-800">Thêm danh mục mới</h1>
            <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Danh sách danh mục
            </a>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Thêm Danh Mục Mới</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
                        <?php endif; ?>

                        <form action="process_insert.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="category_name">Tên Danh Mục <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="category_name" name="category_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="type">Loại</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="product">Sản phẩm</option>
                                    <option value="blog">Bài viết</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Mô tả</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Thêm Danh Mục</button>
                                <a href="index.php" class="btn btn-secondary">Hủy</a>
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

        $(document).ready(function() {
            $('#type').change(function() {
                var selectedType = $(this).val();
                // Redirect to the same page with the new type parameter
                window.location.href = 'add.php?type=' + selectedType;
            });
        });
    </script>

</body>

</html>