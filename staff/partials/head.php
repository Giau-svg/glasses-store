<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="EYEGLASSES - Hệ thống quản lý nhân viên">
<meta name="author" content="EYEGLASSES">
<title><?php echo $page_title ?? 'Quản lý hệ thống'; ?> - EYEGLASSES</title>

<!-- Favicon -->
<link rel="icon" type="image/png" sizes="16x16" href="../../public/img/favicon.png">

<!-- Custom fonts for this template-->
<link href="../../admin/public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Core styles for this template-->
<link href="../../admin/public/css/sb-admin-2.min.css" rel="stylesheet">

<!-- Custom styles specific to staff -->
<style>
    :root {
        /* New luxury color palette */
        --wood-beige: #d2b48c;      /* Main theme color - warm wood/beige */
        --wood-beige-light: #e6d2b5; /* Lighter version */
        --wood-beige-dark: #b29067;  /* Darker version */
        
        --cream-white: #f5f5f0;     /* Cream white for backgrounds */
        --cream-white-dark: #e8e8e0; /* Darker cream for accents */
        
        --black: #212529;           /* Black for text and details */
        --black-soft: #333333;      /* Softer black */
        
        --light-gold: #d4af37;      /* Light gold for accents */
        --light-gold-light: #f7e7b2; /* Lighter gold for hover effects */
        
        /* Bootstrap primary color replacements */
        --primary: var(--wood-beige);
        --primary-hover: var(--wood-beige-dark);
        --bs-primary: var(--wood-beige);
        --bs-primary-rgb: 210, 180, 140;
    }
    
    body {
        font-family: "Nunito", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 0.9rem;
        background-color: var(--cream-white);
        color: var(--black);
    }
    
    h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
        font-family: "Playfair Display", serif;
        font-weight: 600;
    }
    
    /* Sidebar background */
    .sidebar-wrapper {
        background-color: var(--cream-white);
        border-right: 1px #e3e3da;
    }
    
    .sidenav {
        background-color: var(--cream-white);
    }
    
    /* Left sidebar titles */
    .sidenav-menu-heading {
        color: var(--black-soft);
        font-weight: 700;
        letter-spacing: 1px;
    }
    
    /* Sidebar links */
    .sidenav .nav-link {
        color: var(--black-soft);
    }
    
    .sidenav .nav-link:hover {
        color: var(--wood-beige-dark);
        background-color: var(--cream-white-dark);
    }
    
    .sidenav .nav-link.active {
        color: var(--wood-beige) !important;
        font-weight: 600;
        background-color: var(--cream-white-dark);
        border-left: 3px solid var(--wood-beige);
    }
    
    /* Sidebar footer */
    .sidenav-footer {
        background-color: var(--cream-white-dark);
        border-top: 1px solid #e3e3da;
    }
    
    /* Buttons */
    .btn-primary {
        background-color: var(--wood-beige);
        border-color: var(--wood-beige);
        color: var(--black);
    }
    
    .btn-primary:hover {
        background-color: var(--wood-beige-dark);
        border-color: var(--wood-beige-dark);
        color: var(--black);
    }
    
    .btn-secondary {
        background-color: var(--black);
        border-color: var(--black);
    }
    
    .btn-info {
        background-color: var(--light-gold);
        border-color: var(--light-gold);
        color: var(--black);
    }
    
    .btn-info:hover {
        background-color: #b89c30;
        border-color: #b89c30;
        color: var(--black);
    }
    
    /* Card styling */
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.05);
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(33, 40, 50, 0.125);
        font-family: "Playfair Display", serif;
    }
    
    /* Custom colored borders */
    .border-left-primary {
        border-left: .25rem solid var(--wood-beige) !important;
    }
    
    .border-left-success {
        border-left: .25rem solid var(--light-gold) !important;
    }
    
    .border-left-info {
        border-left: .25rem solid var(--black-soft) !important;
    }
    
    /* Badge colors */
    .badge-primary, .bg-primary {
        background-color: var(--wood-beige) !important;
        color: var(--black) !important;
    }
    
    .badge-success, .bg-success {
        background-color: var(--light-gold) !important;
        color: var(--black) !important;
    }
    
    /* Text colors */
    .text-primary {
        color: var(--wood-beige) !important;
    }
    
    /* Navbar/topbar */
    .topbar {
        background-color: #fff;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.05);
    }
    
    .dropdown-menu {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.05);
    }
    
    /* Footer */
    .sticky-footer {
        background-color: var(--cream-white);
        border-top: 1px solid #e3e3da;
    }
    
    /* Tables */
    .table th {
        background-color: var(--cream-white-dark);
        color: var(--black-soft);
        border-color: #e3e3da;
    }
    
    .table td {
        border-color: #e3e3da;
    }
    
    .table-hover tbody tr:hover {
        background-color: var(--cream-white);
    }
    
    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: var(--cream-white-dark);
    }
    
    ::-webkit-scrollbar-thumb {
        background: var(--wood-beige);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: var(--wood-beige-dark);
    }
    
    /* Dropdown menus */
    .dropdown-item:active, 
    .dropdown-item:focus, 
    .dropdown-item:hover {
        background-color: var(--cream-white-dark);
        color: var(--black);
    }
    
    /* Customize charts */
    .chart-pie canvas, 
    .chart-bar canvas {
        max-height: 300px;
    }
    
    /* Brand name */
    .brand-name {
        font-family: "Playfair Display", serif;
        font-weight: 700;
        color: var(--wood-beige);
        letter-spacing: 1.5px;
        font-size: 1.5rem;
        text-transform: uppercase;
        transition: color 0.3s ease;
    }
    
    /* Improved sidebar brand */
    .sidenav-brand {
        text-align: center;
        padding: 1.5rem 1rem 0.5rem;
    }
    
    .sidenav-brand a {
        text-decoration: none;
        display: block;
    }
    
    .sidenav-brand a:hover .brand-name {
        color: var(--light-gold);
    }
    
    /* Table improvements */
    .table {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .table thead th {
        background-color: var(--cream-white-dark);
        border-color: var(--cream-white-dark);
        color: var(--black-soft);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .table-bordered {
        border: none;
    }
    
    .table-bordered th, .table-bordered td {
        border-color: #e9ecef;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(210, 180, 140, 0.05);
    }
    
    /* Card styling enhancement */
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
    }
    
    .card-header {
        border-bottom: none;
        background: linear-gradient(to right, var(--cream-white), white);
    }
    
    /* Badge styling */
    .badge {
        font-weight: 600;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
    }
    
    .badge.rounded-pill {
        padding-right: 0.8em;
        padding-left: 0.8em;
    }
    
    /* Custom button hover effects */
    .btn {
        transition: all 0.2s ease;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Login styling */
    .login-container {
        background-color: var(--cream-white);
    }
    
    .login-form {
        background: white;
    }
    
    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
        body {
            font-size: 12pt;
        }
        .container-fluid {
            width: 100%;
            max-width: 100%;
        }
    }
    
    /* Fixed Sidebar Styles */
    .sidebar-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 250px;
        z-index: 1050;
        transition: all 0.3s ease;
        box-shadow: 3px 0 15px rgba(0, 0, 0, 0.05);
        overflow-y: auto;
    }
    
    .sidenav {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .sidenav-menu {
        flex-grow: 1;
        overflow-y: auto;
    }
    
    #wrapper {
        display: flex;
    }
    
    #content-wrapper {
        width: calc(100% - 250px);
        margin-left: 250px;
        transition: all 0.3s ease;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    
    /* Toggled sidebar */
    .sidebar-toggled .sidebar-wrapper {
        width: 0;
        overflow: hidden;
    }
    
    .sidebar-toggled #content-wrapper {
        width: 100%;
        margin-left: 0;
    }
    
    /* Top navbar with fixed sidebar */
    .topnav {
        position: fixed;
        top: 0;
        right: 0;
        left: 250px;
        z-index: 1040;
        transition: all 0.3s ease;
    }
    
    .sidebar-toggled .topnav {
        left: 0;
    }
    
    /* Content padding */
    #page-content {
        padding-top: 75px;
        flex: 1 0 auto;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .sidebar-wrapper {
            width: 0;
        }
        
        #content-wrapper {
            width: 100%;
            margin-left: 0;
        }
        
        .topnav {
            left: 0;
        }
        
        body:not(.sidebar-toggled) .sidebar-wrapper {
            width: 250px;
        }
        
        body:not(.sidebar-toggled) #content-wrapper {
            margin-left: 0;
        }
    }
    
    /* Enhance sidebar navigation */
    .sidenav .nav-link {
        border-radius: 6px;
        margin: 3px 10px;
        transition: all 0.2s;
    }
    
    .sidenav .nav-link.active {
        border-left: 3px solid var(--wood-beige);
        padding-left: 13px;
    }
    
    .sidenav .nav-link:hover {
        transform: translateX(3px);
    }
    
    .sidenav-menu-heading {
        padding: 1rem 1.5rem 0.5rem;
        font-size: 0.7rem;
        text-transform: uppercase;
    }
    
    /* Logo in sidebar */
    .navbar-brand {
        margin-bottom: 1rem;
    }
    
    /* Enhanced table styling for a more luxury feel */
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        border-top: none;
        padding: 0.85rem 1rem;
        vertical-align: middle;
    }
    
    .table td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
    }
    
    .table-bordered th:first-child,
    .table-bordered td:first-child {
        border-left: none;
    }
    
    .table-bordered th:last-child,
    .table-bordered td:last-child {
        border-right: none;
    }
    
    .table-bordered tr:last-child td {
        border-bottom: none;
    }
    
    /* Animated hover effect for rows */
    .table-hover tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-hover tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
        z-index: 1;
        position: relative;
    }
    
    /* Improved badges for status */
    .badge {
        padding: 0.5em 0.8em;
        font-weight: 600;
        font-size: 0.75em;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .badge.bg-warning {
        background-color: #f8c146 !important;
        color: #212529 !important;
    }
    
    .badge.bg-success {
        background-color: var(--light-gold) !important;
    }
    
    .badge.bg-primary {
        background-color: var(--wood-beige) !important;
    }
    
    /* Prettier buttons in tables */
    .table .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.25rem;
        margin: 0 0.1rem;
    }
    
    .table .btn i {
        margin-right: 0;
    }
    
    /* Alternating row colors for better readability */
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(240, 235, 228, 0.3);
    }
</style> 

