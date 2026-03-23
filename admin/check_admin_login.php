<?php

session_start();
if (!isset($_SESSION['admin_level']) && (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin')) {
    header('location:../index.php?error=Bạn không có quyền truy cập trang này');
    exit();
}

