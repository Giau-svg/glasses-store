<nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow" style="background: linear-gradient(to right, #ffffff, #f8f9fc);">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" style="color: #3a7bd5;">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Search -->
    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control bg-light border-0 small" placeholder="Tìm kiếm..."
                aria-label="Search" aria-describedby="basic-addon2">
            <div class="input-group-append">
                <button class="btn btn-gradient" type="button">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>

    <?php

        require '../root.php';

        $sql_cn = "SELECT count(*) 
                FROM orders
                WHERE order_status = 'pending'";
        $result_cn = mysqli_query($connect, $sql_cn);
        $each_cn = mysqli_fetch_array($result_cn);
        $num_cn = $each_cn['count(*)'];


        $sql_tb = "SELECT o.*,
        p.product_name,
        p.image_path,
        od.quantity
        FROM order_details od
        LEFT JOIN orders o ON o.order_id = od.order_id
        LEFT JOIN products p ON p.product_id = od.product_id             
        ORDER BY o.order_status ASC, o.order_id DESC
        LIMIT 4;";
        $result_tb = mysqli_query($connect, $sql_tb);
    ?>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw text-gray-600"></i>
                <!-- Counter - Alerts -->
                <?php if (!empty($num_cn)) { ?>
                    <span class="badge badge-notification badge-counter"><?php echo $num_cn ?> +</span>
                <?php } else { ?>
                    <span class="badge badge-notification badge-counter"></span>
                <?php } ?>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Thông Báo Đơn Hàng
                </h6>


                <?php foreach ($result_tb as $each_tb) : ?>
                    <a class="dropdown-item d-flex align-items-center" href="../orders/detail.php?id=<?php echo $each_tb['order_id'] ?>">
                        <div class="mr-3">
                            <div class="icon-circle bg-primary">
                                <!-- <i class="fas fa-file-alt text-white"></i> -->
                                <?php if(!empty($each_tb['image_path'])){ ?>
                                    <img height="50" src="../products/server/uploads/<?php echo $each_tb['image_path'] ?>" alt="">
                                <?php } else { ?>
                                    <img height="50" src="../public/img/undraw_profile.svg" alt="">
                                <?php } ?>
                            </div>
                        </div>
                        <div>
                            <div class="small text-dark">
                                Số lượng đặt: <?php echo $each_tb['quantity'] ?>
                                <span class="text-primary">
                                    <?php echo $each_tb['order_date'] ?>
                                </span>
                                <?php if ($each_tb['order_status'] != 'pending') { ?>
                                    <span class="badge badge-danger badge-counter"></span>
                                <?php } else { ?>
                                    <span class="badge badge-danger badge-counter">mới</span>
                                <?php } ?>
                            </div>
                            <?php if(!empty($each_tb['product_name'])){ ?>
                                <span class="font-weight-bold"><?php echo $each_tb['product_name'] ?></span>
                            <?php } else { ?>
                                <span class="font-weight-bold">Sản phẩm</span>
                            <?php } ?>
                        </div>
                    </a>
                <?php endforeach ?>

                <!-- <a class="dropdown-item d-flex align-items-center" href="#">
                <div class="mr-3">
                    <div class="icon-circle bg-success">
                        <i class="fas fa-donate text-white"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-gray-500">December 7, 2019</div>
                    $290.29 has been deposited into your account!
                </div>
            </a>
            <a class="dropdown-item d-flex align-items-center" href="#">
                <div class="mr-3">
                    <div class="icon-circle bg-warning">
                        <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-gray-500">December 2, 2019</div>
                    Spending Alert: We've noticed unusually high spending for your account.
                </div>
            </a> -->
                <a class="dropdown-item text-center small text-gray-500" href="../orders/index.php">Show All Alerts</a>
            </div>
        </li>
                                
        <!-- Nav Item - Messages -->
        <!-- <li class="nav-item dropdown no-arrow mx-1"> -->
            <!-- 
                <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                    <--! Counter - Messages ->
                    <span class="badge badge-danger badge-counter">7</span>
            </a>
            --> 
            <!-- Dropdown - Messages -->
            <!-- <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                    Message Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="../public/img/undraw_profile_1.svg" alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div class="font-weight-bold">
                        <div class="text-truncate">Hi there! I am wondering if you can help me with a
                            problem I've been having.</div>
                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="../public/img/undraw_profile_2.svg" alt="...">
                        <div class="status-indicator"></div>
                    </div>
                    <div>
                        <div class="text-truncate">I have the photos that you ordered last month, how
                            would you like them sent to you?</div>
                        <div class="small text-gray-500">Jae Chun · 1d</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="../public/img/undraw_profile_3.svg" alt="...">
                        <div class="status-indicator bg-warning"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Last month's report looks great, I am very happy with
                            the progress so far, keep up the good work!</div>
                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60" alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                            told me that people say this to all dogs, even if they aren't good...</div>
                        <div class="small text-gray-500">Chicken the Dog · 2w</div>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
            </div> -->
        <!-- </li> -->

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['fullname'] ?></span>
                <img class="img-profile rounded-circle" src="../public/img/undraw_profile.svg">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <!-- <a class="dropdown-item" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Settings
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Activity Log
                </a> -->
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Đăng Xuất
                </a>
            </div>
        </li>

    </ul>

</nav>

<style>
.navbar {
    box-shadow: 0 4px 10px rgba(0,0,0,0.03) !important;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.navbar-search .input-group {
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.navbar-search .form-control {
    height: 45px;
    padding-left: 20px;
    border-radius: 25px 0 0 25px;
    background-color: #f8f9fc !important;
    border: 1px solid #eaecf4;
    transition: all 0.3s;
}

.navbar-search .form-control:focus {
    box-shadow: none;
    border-color: #3a7bd5;
    background-color: #fff !important;
}

.navbar-search .btn-gradient {
    padding: 0 25px;
    border-radius: 0 25px 25px 0;
    background: linear-gradient(135deg, #3a7bd5, #00d2ff);
    color: white;
    border: none;
}

.img-profile {
    height: 40px;
    width: 40px;
    object-fit: cover;
    border: 3px solid #3a7bd5;
    padding: 2px;
    background-color: white;
}

.topbar .dropdown-list {
    max-width: 350px;
    border-radius: 15px;
    border: none;
}

.topbar .dropdown-list .dropdown-header {
    background: linear-gradient(135deg, #3a7bd5, #00d2ff);
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    color: white;
    font-weight: 600;
    letter-spacing: 1px;
}

.icon-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 100%;
    width: 3rem;
    height: 3rem;
    background: linear-gradient(135deg, #3a7bd5, #00d2ff);
}

.badge-notification {
    background: linear-gradient(135deg, #ff5858, #f09819);
    color: white;
    border: 2px solid white;
}

.topbar-divider {
    border-right: 1px solid rgba(0,0,0,0.1);
    height: 2rem;
}

.dropdown-menu {
    border-radius: 15px;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.dropdown-item {
    border-radius: 10px;
    margin: 5px 10px;
    transition: all 0.3s;
}

.dropdown-item:hover {
    background-color: #f8f9fc;
    transform: translateX(5px);
}

.icon-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 100%;
}
</style>