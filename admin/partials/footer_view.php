        </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Shop Điện Tử <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </footer>
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
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận đăng xuất?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                    <a class="btn btn-gradient" href="../logout.php">Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>

    <style>
    .sticky-footer {
        padding: 1.5rem 0;
        border-top: 1px solid #e3e6f0;
        background: linear-gradient(to right, #ffffff, #f8f9fc);
    }

    .copyright {
        color: #5a5c69;
        font-weight: 500;
    }

    .scroll-to-top {
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
        line-height: 3rem;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 50%;
        z-index: 1000;
    }

    .scroll-to-top:hover {
        background: linear-gradient(135deg, #2e6ac0, #00b8e0);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .scroll-to-top i {
        color: white;
        font-size: 1.2rem;
    }

    .modal-content {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .modal-header {
        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
        color: white;
        border-bottom: none;
        padding: 1.5rem;
    }

    .modal-title {
        color: white;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .modal-header .close {
        color: white;
        opacity: 0.8;
        text-shadow: none;
        transition: all 0.3s;
    }

    .modal-header .close:hover {
        opacity: 1;
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 1.8rem;
        font-size: 1.1rem;
        color: #5a5c69;
    }

    .modal-footer {
        border-top: none;
        padding: 1rem 1.8rem 1.8rem;
    }

    .btn-gradient {
        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
        border: none;
        color: white;
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }

    .btn-gradient:hover {
        background: linear-gradient(135deg, #2e6ac0, #00b8e0);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        color: white;
    }

    .btn-secondary {
        background: #f8f9fc;
        color: #5a5c69;
        border: none;
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }

    .btn-secondary:hover {
        background: #eaecf4;
        color: #2e2f37;
    }
    </style>