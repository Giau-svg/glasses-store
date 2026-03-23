-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th4 14, 2025 lúc 02:32 PM
-- Phiên bản máy phục vụ: 10.4.28-MariaDB
-- Phiên bản PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `eyeglasses_shop`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `details`, `created_at`) VALUES
(1, 4, 'Đăng xuất hệ thống', 'Đăng xuất khỏi hệ thống', '2025-04-13 01:42:54'),
(2, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 01:56:55'),
(3, 1, 'Đăng xuất hệ thống', 'Đăng xuất khỏi hệ thống', '2025-04-13 01:57:01'),
(4, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 01:57:03'),
(5, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 06:20:59'),
(6, 1, 'Đăng xuất hệ thống', 'Đăng xuất khỏi hệ thống admin', '2025-04-13 06:31:32'),
(7, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 06:31:40'),
(8, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 06:47:20'),
(9, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 06:48:13'),
(10, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 06:50:15'),
(11, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 06:52:35'),
(12, 1, 'Đăng xuất hệ thống', 'Đăng xuất khỏi hệ thống admin', '2025-04-13 07:16:57'),
(13, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 07:18:12'),
(14, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 07:21:44'),
(15, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 07:34:24'),
(16, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 07:34:42'),
(17, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 07:37:27'),
(18, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 07:37:31'),
(19, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 07:37:41'),
(20, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 07:39:01'),
(21, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-13 14:17:02'),
(22, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-14 03:28:33'),
(23, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-14 11:49:56'),
(24, 1, 'Đăng nhập hệ thống', 'Đăng nhập với email admin@opticvision.com', '2025-04-14 12:14:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `gender` tinyint(1) DEFAULT 1,
  `role` varchar(20) DEFAULT 'admin',
  `lever` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`, `email`, `fullname`, `phone`, `gender`, `role`, `lever`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin@example.com', 'Administrator', NULL, 1, 'admin', 2, '2025-04-12 08:23:48'),
(2, 'salesstaff', 'd23c4dcbbe8e4937141264d5a1426d40', 'sales@opticvision.com', 'Nhân Viên Bán Hàng', NULL, 1, 'sales', 1, '2025-04-12 08:23:48');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Ray-Ban', 'Thương hiệu kính mắt cao cấp từ Mỹ', '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(2, 'Oakley', 'Thương hiệu kính thể thao chuyên nghiệp', '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(3, 'Gucci', 'Thương hiệu thời trang cao cấp từ Ý', '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(4, 'Prada', 'Thương hiệu thời trang cao cấp của Ý', '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(5, 'Essilor', 'Nhà sản xuất tròng kính hàng đầu thế giới', '2025-04-11 14:33:04', '2025-04-11 14:33:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Kính mát', 'Kính bảo vệ mắt khỏi ánh nắng mặt trời', NULL, '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(2, 'Kính cận', 'Kính điều chỉnh thị lực cho người cận thị', NULL, '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(3, 'Kính viễn', 'Kính điều chỉnh thị lực cho người viễn thị', NULL, '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(4, 'Kính đa tròng', 'Kính có thể xử lý nhiều vấn đề thị lực', NULL, '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(5, 'Kính thời trang', 'Kính phục vụ mục đích thời trang', 'uploads/categories/1744509983_67fb1c1f85fee.jpg', '2025-04-11 14:33:04', '2025-04-13 02:06:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_lap_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `email`, `phone`, `address`, `created_at`) VALUES
(1, 'Nguyễn Văn A', 'nguyenvana@gmail.com', '0901234567', '123 Đường Lê Lợi, Quận 1, TP.HCM', '2025-04-12 15:58:19'),
(2, 'Trần Thị B', 'tranthib@gmail.com', '0912345678', '456 Đường Nguyễn Huệ, Quận 1, TP.HCM', '2025-04-12 15:58:19'),
(3, 'Lê Văn C', 'levanc@gmail.com', '0823456789', '789 Đường 3/2, Quận 10, TP.HCM', '2025-04-12 15:58:19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `invoice_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `manufacturers`
--

CREATE TABLE `manufacturers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `manufacturers`
--

INSERT INTO `manufacturers` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Ray-Ban', 'Thương hiệu kính mắt nổi tiếng của Mỹ', '2025-04-12 16:02:33', NULL),
(3, 'Prada', 'Thương hiệu xa xỉ chuyên về thời trang và phụ kiện', '2025-04-12 16:02:33', NULL),
(5, 'Gucci', 'Thương hiệu thời trang cao cấp của Ý', '2025-04-12 17:04:14', NULL),
(6, 'Essilor', 'Kính Essilor là của nước ngoài', '2025-04-14 07:12:03', NULL),
(7, 'Oakley', 'Kính Oakley đẹp', '2025-04-14 07:14:51', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `invoice_number` varchar(50) DEFAULT NULL,
  `order_status` enum('pending','confirmed','processing','shipping','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_phone` varchar(20) DEFAULT NULL,
  `shipping_name` varchar(100) DEFAULT NULL,
  `shipping_email` varchar(100) DEFAULT NULL,
  `shipping_notes` text DEFAULT NULL,
  `sales_employee_id` int(11) DEFAULT NULL,
  `processed_date` timestamp NULL DEFAULT NULL,
  `cancelled_date` timestamp NULL DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `shipping_fee`, `invoice_number`, `order_status`, `payment_status`, `payment_method`, `shipping_address`, `shipping_phone`, `shipping_name`, `shipping_email`, `shipping_notes`, `sales_employee_id`, `processed_date`, `cancelled_date`, `cancelled_reason`) VALUES
(47, 1, '2025-04-13 15:21:55', 2500000.00, 0.00, 'INV2025041347', 'delivered', 'pending', 'COD', '123 Đường Lê Lợi, Quận 1, TP.HCM', '0901234567', 'Nguyễn Văn A', 'nguyenvana@gmail.com', 'Giao hàng trong giờ hành chính', NULL, NULL, NULL, NULL),
(48, 2, '2025-04-12 15:21:55', 1800000.00, 0.00, NULL, 'confirmed', 'pending', 'Banking', '456 Đường Nguyễn Huệ, Quận 1, TP.HCM', '0912345678', 'Trần Thị B', 'tranthib@gmail.com', 'Gọi trước khi giao', NULL, NULL, NULL, NULL),
(49, 3, '2025-04-11 15:21:55', 3200000.00, 0.00, NULL, 'shipping', 'pending', 'COD', '789 Đường 3/2, Quận 10, TP.HCM', '0823456789', 'Lê Văn C', 'levanc@gmail.com', 'Để hàng tại quầy lễ tân', NULL, NULL, NULL, NULL),
(50, 1, '2025-04-08 15:21:55', 1500000.00, 0.00, 'INV2025040850', 'delivered', 'pending', 'Banking', '123 Đường Lê Lợi, Quận 1, TP.HCM', '0901234567', 'Nguyễn Văn A', 'nguyenvana@gmail.com', '', NULL, NULL, NULL, NULL),
(51, 2, '2025-04-10 15:21:55', 950000.00, 0.00, NULL, 'cancelled', 'pending', 'COD', '321 Đường Cách Mạng Tháng 8, Quận 3, TP.HCM', '0978123456', 'Phạm Thị D', 'phamthid@gmail.com', 'Khách hàng đổi ý', NULL, NULL, NULL, NULL),
(52, 4, '2025-04-14 11:49:19', 1000000.00, 0.00, NULL, 'processing', 'pending', 'cod', '14 tây sơn', '0112234324', 'Trần quang Thiện ', NULL, NULL, NULL, NULL, NULL, NULL),
(53, 4, '2025-04-14 12:13:46', 4990000.00, 0.00, NULL, 'processing', 'pending', NULL, 'nhà', '032435345', 'Thiện ', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(141, 47, 25, 2, 1200000.00, 2400000.00),
(142, 47, 26, 1, 650000.00, 650000.00),
(143, 48, 25, 2, 850000.00, 1700000.00),
(144, 49, 26, 2, 1200000.00, 2400000.00),
(145, 49, 25, 1, 850000.00, 850000.00),
(146, 52, 28, 1, 1000000.00, 1000000.00),
(147, 53, 26, 1, 4990000.00, 4990000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `staff_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `brand_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category_id`, `manufacturer_id`, `brand_id`, `description`, `price`, `cost_price`, `stock_quantity`, `image_path`, `created_at`, `updated_at`) VALUES
(25, 'Kính mát Oakley Holbrook', 5, 3, 2, 'Kính mát Oakley Holbrook đẹp', 20000000.00, 10000000.00, 1000, '1744479102_oakley_hoolbrook_polarized-1-1.jpg', '2025-04-12 02:39:38', '2025-04-12 17:31:42'),
(26, 'Kính mát Ray-Ban Aviator', 1, 1, 1, 'Kính mát Ray-Ban Aviator đẹp', 4990000.00, 1000000.00, 1000, '1744476094_ray-ban-3025-aviator-polarized-gold-green-rb-3025-00158-size-58-10342.jpg', '2025-04-12 03:40:24', '2025-04-12 17:31:51'),
(27, 'Tròng kính đổi màu', 2, 5, 3, 'Kính cận đổi màu', 1000000.00, 700000.00, 1000, '1744479523_kinh-can-doi-mau.jpg', '2025-04-12 17:38:43', '2025-04-12 17:38:43'),
(28, 'Kính Essilor', 5, 6, 5, 'Kính Essilor đẹp xịn', 1000000.00, 300000.00, 1000, '1744614830_essilor.jpg', '2025-04-14 07:13:50', '2025-04-14 07:13:50'),
(29, 'Kính Oakley', 5, 7, 2, 'Kính đẹp xịn', 10000000.00, 1000000.00, 1000, '1744614981_Gong_kinh_oakley_OX8053_023.jpg', '2025-04-14 07:16:21', '2025-04-14 07:16:21'),
(30, 'Gọng kính kim loại', 5, 3, 4, 'Gọng kính bền bỉ chắc chắn', 100000.00, 50000.00, 10000, '1744615045_kinhkimloai.jpg', '2025-04-14 07:17:25', '2025-04-14 07:19:40'),
(1,'Kính Ray-ban Aviator RB3025-W3277 xám trắng gương', 1, 1, 1, 'Kính Ray-ban Aviator chính hãng, màu xám trắng gương', 5440000, 4000000, 100, 'rb3025-003-32..jpg', NOW(), NOW()),
(2,'Kính Ray-Ban Aviator RB3025-003/32 xám chuyển màu', 1, 1, 1, 'Kính Ray-Ban Aviator chính hãng, xám chuyển màu', 5250000, 3800000, 100, 'rb3025-004-51.jpg', NOW(), NOW()),
(3,'Kính Ray-ban Aviator RB3025-167/4K tím trắng gương', 1, 1, 1, 'Kính Ray-ban Aviator chính hãng, tím trắng gương', 4950000, 3700000, 100, 'rb3025-167-4k.jpg', NOW(), NOW()),
(4,'Kính Ray-Ban Aviator RB3025-004/51 nâu chuyển màu', 1, 1, 1, 'Kính Ray-Ban Aviator chính hãng, nâu chuyển màu', 5080000, 3900000, 100, 'rb3025-w3277.jpg', NOW(), NOW()),
(5,'Kính Ray-Ban Aviator RB3025-001/51 Gradient Brown', 1, 1, 1, 'Kính Ray-Ban Aviator RB3025-001/51 có tròng kính Gradient Brown chuyển màu ấn tượng, “tiệp” màu với gọng kính mạ vàng, tạo dáng vẻ sang trọng, thời trang. Mẫu kính RB3025-001/51 là sự kết hợp hoàn hảo của kiểu dáng kính phi công Ray-Ban cổ điển và màu tròng kính hiện đại.

Mẫu kính RB3025-001/51 được sản xuất, gia công, lắp ráp 100% tại Italy, cho bạn chất lượng hoàn hảo nhất từ chính hãng Ray-Ban.', 5250000, 3900000, 100, 'kinh-rayban-aviator-rb3025-001-51-rayban-vietnam-01.jpg', NOW(), NOW()),
(6,'Kính Ray-Ban Aviator RB3025-002/4J xanh lá tráng gương', 1, 1, 1, 'Kính Ray-Ban Aviator chính hãng, xanh lá chuyển màu', 5280000, 3600000, 100, 'kinh-rayban-aviator-rb3025-002-4j-raybanvietnam-01.jpg', NOW(), NOW()),
(7,'Kính Ray-Ban Aviator RB3025-001/15 Polarized hồng', 1, 1, 1, 'Kính Ray-Ban Aviator chính hãng,Polarized hồng', 5950000, 390000, 100, 'kinh-rayban-aviator-rb3025-001-15-raybanvietnam-01.jpg', NOW(), NOW()),
(8,'Kính Ray-Ban Aviator RB3025-112/69 tráng gương cam đỏ', 1, 1, 1, 'Kính Ray-Ban Aviator chính hãng,tráng gương cam đỏ', 5950000, 00000, 100, 'kinh-rayban-aviator-RB3025-112-69-01-rayban-vietnam.jpg', NOW(), NOW()),
(9,'Ray-Ban RB3498-002/9A(64) Polarized', 1, 1, 1, 'Kính Ray-Ban Aviator chính hãng,Polarized', 5950000, 390000, 100, 'Kinh-mat-Polarized-RayBan-RB3498-002-9A-64-b.jpg.jpg.jpg', NOW(), NOW()),
(10,'Ray-Ban RB3689-004/S2(62) Polarized xanh dương', 1, 1, 1, 'Kính Ray-Ban Aviator chính hãng', 6730000, 5000000, 100, 'rayban-rb3689-004-s2-58.webp', NOW(), NOW()),

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_specifications`
--

CREATE TABLE `product_specifications` (
  `spec_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `frame_material` varchar(100) DEFAULT NULL,
  `lens_material` varchar(100) DEFAULT NULL,
  `lens_type` varchar(100) DEFAULT NULL,
  `frame_color` varchar(50) DEFAULT NULL,
  `lens_color` varchar(50) DEFAULT NULL,
  `frame_shape` varchar(50) DEFAULT NULL,
  `frame_size` varchar(50) DEFAULT NULL,
  `lens_width` int(11) DEFAULT NULL,
  `bridge_width` int(11) DEFAULT NULL,
  `temple_length` int(11) DEFAULT NULL,
  `uv_protection` tinyint(1) DEFAULT 0,
  `polarized` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES
(1, 'admin', 'Quản lý người dùng và phân quyền'),
(2, 'customer', 'Người mua hàng'),
(3, 'sales', 'Nhân viên bán hàng'),
(4, 'inventory', 'Quản lý kho'),
(5, 'business_manager', 'Quản lý doanh nghiệp');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'EYEGLASSES', 'company', '2025-04-13 16:23:20', NULL),
(2, 'company_address', 'Hệ thống kính mắt chất lượng cao', 'company', '2025-04-13 16:23:20', NULL),
(3, 'company_phone', '1900 1234', 'company', '2025-04-13 16:23:20', NULL),
(4, 'company_email', 'support@opticvision.com', 'company', '2025-04-13 16:23:20', NULL),
(5, 'company_logo', '', 'company', '2025-04-13 16:23:20', NULL),
(6, 'shipping_fee', '30000', 'shipping', '2025-04-13 16:23:20', NULL),
(7, 'currency_symbol', 'đ', 'general', '2025-04-13 16:23:20', NULL),
(8, 'currency_format', '0,0', 'general', '2025-04-13 16:23:20', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_name` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `staff_activity_log`
--

CREATE TABLE `staff_activity_log` (
  `log_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `activity` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `stock_receipts`
--

CREATE TABLE `stock_receipts` (
  `receipt_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `receipt_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `inventory_manager_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `stock_receipt_details`
--

CREATE TABLE `stock_receipt_details` (
  `receipt_detail_id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_name`, `email`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Luxottica Group', 'Leonardo Rossi', 'leonardo@luxottica.com', '+39 02 8633 4001', 'Milan, Italy', '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(2, 'Essilor International', 'Jean Martin', 'jean@essilor.com', '+33 1 49 77 42 24', 'Paris, France', '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(3, 'Safilo Group', 'Marco Bianchi', 'marco@safilo.com', '+39 049 698 5111', 'Padova, Italy', '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(4, 'Kering Eyewear', 'Sophie Leclerc', 'sophie@kering.com', '+33 1 45 64 61 00', 'Paris, France', '2025-04-11 14:33:04', '2025-04-11 14:33:04'),
(5, 'HOYA Vision Care', 'Tanaka Hiroshi', 'tanaka@hoya.com', '+81 3 3232 0211', 'Tokyo, Japan', '2025-04-11 14:33:04', '2025-04-11 14:33:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `full_name`, `phone`, `address`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin@opticvision.com', 'Admin User', NULL, NULL, 1, '2025-04-11 14:33:04', '2025-04-13 01:56:55'),
(2, 'sales', '0ad80eb119d9bf7775aa23786b05b391', 'sales@opticvision.com', 'Nhân viên bán hàng', NULL, NULL, 3, '2025-04-12 15:58:19', '2025-04-13 00:58:16'),
(3, 'inventory', '5d1086fbcd28c81a419e12317432251a', 'inventory@opticvision.com', 'Quản lý kho', NULL, NULL, 4, '2025-04-12 15:58:19', '2025-04-13 00:58:16'),
(4, 'quangthien26120824', '67895b54ff03cc0f16dd4bcf07ab86b8', 'quangthien26120824@gmail.com', 'Trần Quang Thiện', '01234567890', 'Ở Việt Nam', 2, '2025-04-13 00:55:19', '2025-04-13 00:55:19'),
(5, 'hathu844884', '67895b54ff03cc0f16dd4bcf07ab86b8', 'hathu844884@gmail.com', 'Hà Thu', '0345667565', '14 tây sơn', 2, '2025-04-14 10:02:40', '2025-04-14 10:02:40');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Chỉ mục cho bảng `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Chỉ mục cho bảng `manufacturers`
--
ALTER TABLE `manufacturers`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sales_employee_id` (`sales_employee_id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Chỉ mục cho bảng `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD PRIMARY KEY (`spec_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Chỉ mục cho bảng `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Chỉ mục cho bảng `staff_activity_log`
--
ALTER TABLE `staff_activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Chỉ mục cho bảng `stock_receipts`
--
ALTER TABLE `stock_receipts`
  ADD PRIMARY KEY (`receipt_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `inventory_manager_id` (`inventory_manager_id`);

--
-- Chỉ mục cho bảng `stock_receipt_details`
--
ALTER TABLE `stock_receipt_details`
  ADD PRIMARY KEY (`receipt_detail_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `manufacturers`
--
ALTER TABLE `manufacturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT cho bảng `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `product_specifications`
--
ALTER TABLE `product_specifications`
  MODIFY `spec_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `staff_activity_log`
--
ALTER TABLE `staff_activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `stock_receipts`
--
ALTER TABLE `stock_receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `stock_receipt_details`
--
ALTER TABLE `stock_receipt_details`
  MODIFY `receipt_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`sales_employee_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`);

--
-- Các ràng buộc cho bảng `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD CONSTRAINT `product_specifications_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `stock_receipts`
--
ALTER TABLE `stock_receipts`
  ADD CONSTRAINT `stock_receipts_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `stock_receipts_ibfk_2` FOREIGN KEY (`inventory_manager_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `stock_receipt_details`
--
ALTER TABLE `stock_receipt_details`
  ADD CONSTRAINT `stock_receipt_details_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `stock_receipts` (`receipt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_receipt_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
