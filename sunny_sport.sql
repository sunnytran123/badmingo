-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2025 at 05:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sunny_sport`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL COMMENT 'Mã đặt sân',
  `user_id` int(11) DEFAULT NULL COMMENT 'Mã người dùng',
  `court_id` int(11) DEFAULT NULL COMMENT 'Mã sân',
  `booking_date` date NOT NULL COMMENT 'Ngày đặt sân',
  `start_time` time NOT NULL COMMENT 'Giờ bắt đầu',
  `end_time` time NOT NULL COMMENT 'Giờ kết thúc',
  `payment_method` enum('prepaid','ondelivery') NOT NULL COMMENT 'Phương thức thanh toán: prepaid (trước), ondelivery (sau)',
  `total_price` decimal(10,2) NOT NULL COMMENT 'Tổng giá (VNĐ)',
  `discount` decimal(5,2) DEFAULT 0.00 COMMENT 'Giảm giá (%)',
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending' COMMENT 'Trạng thái: pending (chờ), confirmed (xác nhận), cancelled (hủy)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo',
  `fullname` varchar(100) DEFAULT NULL COMMENT 'Họ và tên người đặt',
  `phone` varchar(15) DEFAULT NULL COMMENT 'Số điện thoại người đặt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin đặt sân';

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `court_id`, `booking_date`, `start_time`, `end_time`, `payment_method`, `total_price`, `discount`, `status`, `created_at`, `fullname`, `phone`) VALUES
(11, 11, 1, '2025-08-26', '18:00:00', '19:00:00', 'ondelivery', 150000.00, 0.00, 'pending', '2025-08-26 10:38:09', NULL, NULL),
(12, 11, 1, '2025-08-26', '22:00:00', '22:30:00', 'prepaid', 67500.00, 10.00, 'pending', '2025-08-26 14:57:49', NULL, NULL),
(13, 11, 4, '2025-08-28', '14:00:00', '17:00:00', 'prepaid', 405000.00, 10.00, 'pending', '2025-08-27 07:14:42', 'Nguyễn Văn P', '0914928282'),
(14, 11, 3, '2025-08-30', '06:00:00', '06:30:00', 'ondelivery', 75000.00, 0.00, 'pending', '2025-08-27 07:38:44', 'Nguyễn Văn P', '0914928282'),
(15, 11, 1, '2025-08-27', '15:00:00', '15:30:00', 'prepaid', 67500.00, 10.00, 'pending', '2025-08-27 07:45:11', 'Nguyễn Văn P', '0914928282'),
(16, 11, 1, '2025-08-29', '06:00:00', '06:30:00', 'ondelivery', 75000.00, 0.00, 'pending', '2025-08-27 07:59:15', 'Minh Hào', '0927271827'),
(17, 11, 3, '2025-08-29', '19:00:00', '20:30:00', 'ondelivery', 225000.00, 0.00, 'pending', '2025-08-29 11:57:11', 'Phan Minh Thắng', '0843029049'),
(18, 11, 4, '2025-09-01', '06:00:00', '09:00:00', 'prepaid', 405000.00, 10.00, 'confirmed', '2025-08-29 12:49:58', 'Hà Kiều', '0919156745'),
(19, 11, 2, '2025-09-02', '06:00:00', '07:30:00', 'ondelivery', 225000.00, 0.00, 'confirmed', '2025-08-29 12:58:46', 'Hà Kiều', '0919156745'),
(20, 11, 4, '2025-08-31', '10:00:00', '10:30:00', 'ondelivery', 75000.00, 0.00, 'confirmed', '2025-08-31 02:35:17', 'Thắng', '0843029049'),
(21, 12, 3, '2025-09-12', '17:30:00', '19:00:00', 'ondelivery', 225000.00, 0.00, 'confirmed', '2025-09-12 10:21:03', 'Thùy', '0926176287'),
(22, 12, 1, '2025-09-20', '06:00:00', '06:30:00', 'ondelivery', 75000.00, 0.00, 'pending', '2025-09-19 06:14:04', 'sunny1', '0914090876'),
(23, 12, 2, '2025-09-19', '13:30:00', '15:00:00', 'ondelivery', 225000.00, 0.00, 'pending', '2025-09-19 06:15:00', 'sunny1', '0914090876'),
(24, 12, 2, '2025-09-20', '08:00:00', '10:00:00', 'prepaid', 300000.00, 0.00, 'confirmed', '2025-09-18 23:45:00', 'Nguyễn Hoàng', '0912003456'),
(25, 12, 1, '2025-09-21', '18:00:00', '20:00:00', 'ondelivery', 280000.00, 0.00, 'pending', '2025-09-19 00:10:00', 'Lê Minh Anh', '0923456789'),
(26, 12, 3, '2025-09-22', '06:30:00', '08:00:00', 'prepaid', 225000.00, 10.00, 'confirmed', '2025-09-19 01:00:00', 'Phạm Thu Trang', '0976543210'),
(27, 12, 4, '2025-09-23', '15:00:00', '16:30:00', 'ondelivery', 225000.00, 0.00, 'pending', '2025-09-19 01:15:00', 'Đỗ Văn Quân', '0988111222'),
(28, 12, 1, '2025-09-24', '20:00:00', '21:30:00', 'prepaid', 225000.00, 0.00, 'cancelled', '2025-09-19 01:30:00', 'Trần Hải Yến', '0933221100'),
(100, 12, 1, '2025-09-20', '06:00:00', '07:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Nguyễn Văn A', '0911111111'),
(101, 12, 2, '2025-09-20', '06:00:00', '07:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Trần Thị B', '0922222222'),
(102, 12, 3, '2025-09-20', '06:00:00', '07:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Lê Văn C', '0933333333'),
(103, 12, 1, '2025-09-20', '07:00:00', '08:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Phạm Thị D', '0944444444'),
(104, 12, 4, '2025-09-20', '07:00:00', '08:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Hoàng Văn E', '0955555555'),
(105, 12, 1, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Vũ Thị F', '0966666666'),
(106, 12, 2, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Đặng Văn G', '0977777777'),
(107, 12, 3, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Bùi Thị H', '0988888888'),
(108, 12, 4, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Ngô Văn I', '0999999999'),
(109, 12, 5, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Dương Thị K', '0900000000'),
(110, 12, 1, '2025-09-20', '09:00:00', '10:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Lý Văn L', '0911111112'),
(111, 12, 3, '2025-09-20', '09:00:00', '10:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Tôn Thị M', '0922222223'),
(112, 12, 5, '2025-09-20', '09:00:00', '10:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Võ Văn N', '0933333334'),
(113, 12, 2, '2025-09-20', '10:00:00', '11:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Đinh Thị O', '0944444445'),
(114, 12, 4, '2025-09-20', '10:00:00', '11:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Phan Văn P', '0955555556'),
(115, 12, 3, '2025-09-20', '14:00:00', '15:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Trương Thị Q', '0966666667'),
(116, 12, 4, '2025-09-20', '14:00:00', '15:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Lâm Văn R', '0977777778'),
(117, 12, 5, '2025-09-20', '14:00:00', '15:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Hồ Thị S', '0988888889'),
(118, 12, 1, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Chu Văn T', '0999999991'),
(119, 12, 2, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Lưu Thị U', '0900000002'),
(120, 12, 3, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Thạch Văn V', '0911111113'),
(121, 12, 4, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Mai Thị W', '0922222224'),
(122, 12, 5, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Hứa Văn X', '0933333335'),
(123, 12, 1, '2025-09-20', '19:00:00', '20:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Lý Văn Y', '0944444446'),
(124, 12, 3, '2025-09-20', '19:00:00', '20:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Tôn Thị Z', '0955555557'),
(125, 12, 5, '2025-09-20', '19:00:00', '20:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Võ Văn AA', '0966666668'),
(126, 12, 2, '2025-09-20', '20:00:00', '21:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Đinh Thị BB', '0977777779'),
(127, 12, 4, '2025-09-20', '20:00:00', '21:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', '2025-09-20 01:00:00', 'Phan Văn CC', '0988888890'),
(5300, 12, 2, '2025-09-21', '18:00:00', '20:00:00', 'ondelivery', 300000.00, 0.00, 'confirmed', '2025-09-21 03:26:54', 'Bơ', '0914090892');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `variant_id` int(11) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `user_id`, `product_id`, `quantity`, `created_at`, `variant_id`, `size`, `color`) VALUES
(4, 11, 6, 3, '2025-08-29 11:57:50', NULL, '', ''),
(5, 11, 1, 3, '2025-08-29 12:06:27', NULL, '', ''),
(6, 11, 3, 1, '2025-08-29 13:00:05', NULL, '', ''),
(8, 12, 4, 3, '2025-09-19 06:21:31', 4, 'M', 'Blue');

-- --------------------------------------------------------

--
-- Table structure for table `chat_history`
--

CREATE TABLE `chat_history` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `role` enum('user','bot','admin') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_history`
--

INSERT INTO `chat_history` (`id`, `user_id`, `role`, `message`, `created_at`) VALUES
(194, '12', 'user', 'chào bạn , toi muốn tim giày yonex', '2025-09-21 02:16:33'),
(195, '12', 'bot', 'Chào bạn, hiện tại chúng tôi không có sản phẩm giày Yonex nào trong danh sách. Tuy nhiên, dưới đây là các sản phẩm giày cầu lông khác mà bạn có thể tham khảo:\n\n```html\n<div class=\"product-list\" style=\"display:flex;flex-wrap:wrap;gap:15px;margin-top:10px;\">\n    <div class=\"product-card\" onclick=\"window.location.href=\'t.php?product_id=2\'\" style=\"width:200px;padding:15px;border:1px solid #ddd;border-radius:10px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s;\">\n        <img src=\"images/lining-attack.jpg\" class=\"product-image\" style=\"width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:10px;\">\n        <div class=\"product-name\" style=\"font-size:16px;color:#333;margin-bottom:8px;font-weight:bold;\">Giày Lining Attack 2025</div>\n        <div class=\"product-price\" style=\"color:#e74c3c;font-weight:bold;font-size:18px;\">1,500,000 VNĐ</div>\n    </div>\n    <div class=\"product-card\" onclick=\"window.location.href=\'t.php?product_id=7\'\" style=\"width:200px;padding:15px;border:1px solid #ddd;border-radius:10px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s;\">\n        <img src=\"images/asics-skyelite.jpg\" class=\"product-image\" style=\"width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:10px;\">\n        <div class=\"product-name\" style=\"font-size:16px;color:#333;margin-bottom:8px;font-weight:bold;\">Giày Asics Sky Elite FF</div>\n        <div class=\"product-price\" style=\"color:#e74c3c;font-weight:bold;font-size:18px;\">1,800,000 VNĐ</div>\n    </div>\n</div>\n```\n\nNếu bạn cần thêm thông tin hoặc tìm kiếm sản phẩm khác, hãy cho tôi biết!', '2025-09-21 02:16:45'),
(196, '12', 'user', 'tôi muốn tìm áo để thi đấu', '2025-09-21 02:18:28'),
(197, '12', 'bot', 'Tìm thấy 2 sản phẩm phù hợp: \n<div class=\"product-list\" style=\"display:flex;flex-wrap:wrap;gap:15px;margin-top:10px;\">\n    <div class=\"product-card\" onclick=\"window.location.href=\'t.php?product_id=4\'\" style=\"width:200px;padding:15px;border:1px solid #ddd;border-radius:10px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s;\">\n        <img src=\"images/lining-shirt.jpg\" class=\"product-image\" style=\"width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:10px;\">\n        <div class=\"product-name\" style=\"font-size:16px;color:#333;margin-bottom:8px;font-weight:bold;\">Áo thi đấu Lining Pro</div>\n        <div class=\"product-price\" style=\"color:#e74c3c;font-weight:bold;font-size:18px;\">450,000 VNĐ</div>\n    </div>\n    <div class=\"product-card\" onclick=\"window.location.href=\'t.php?product_id=12\'\" style=\"width:200px;padding:15px;border:1px solid #ddd;border-radius:10px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s;\">\n        <img src=\"images/Áo-đấu.jpg\" class=\"product-image\" style=\"width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:10px;\">\n        <div class=\"product-name\" style=\"font-size:16px;color:#333;margin-bottom:8px;font-weight:bold;\">Áo đấu</div>\n        <div class=\"product-price\" style=\"color:#e74c3c;font-weight:bold;font-size:18px;\">150,000 VNĐ</div>\n    </div>\n</div>', '2025-09-21 02:18:41'),
(198, '12', 'user', 'hôm nay lúc 18 giời đến 20 giờ có sân trống không', '2025-09-21 02:20:01'),
(199, '12', 'bot', '✅ Có 4 sân trống:\n- Sân 2\n- Sân 3\n- Sân 4\n- Sân 5', '2025-09-21 02:20:05'),
(200, '12', 'user', 'ngày mai từ 6h30 tới 8h có sân nào trống không', '2025-09-21 02:20:54'),
(201, '12', 'bot', '✅ Có 4 sân trống:\n- Sân 1\n- Sân 2\n- Sân 4\n- Sân 5', '2025-09-21 02:21:00'),
(202, '12', 'user', 'ngày mai còn sân trống từ 7 giờ đến 8 giờ sáng không', '2025-09-21 03:00:57'),
(203, '12', 'bot', '✅ Tìm thấy các sân trống:\n1. Sân 1 - 150,000 VNĐ/giờ\n2. Sân 2 - 150,000 VNĐ/giờ\n3. Sân 4 - 150,000 VNĐ/giờ\n4. Sân 5 - 150,000 VNĐ/giờ\n\n🎯 **Bạn có muốn đặt một trong những sân này ngay bây giờ không?**\nTrả lời \'có\' hoặc \'đặt sân\' để tiếp tục đặt sân.', '2025-09-21 03:01:02'),
(204, '12', 'user', 'ngày mai còn sân trống từ 7 giờ đến 8 giờ sáng không', '2025-09-21 03:06:26'),
(205, '12', 'bot', '✅ Tìm thấy các sân trống:\n1. Sân 1 - 150,000 VNĐ/giờ\n2. Sân 2 - 150,000 VNĐ/giờ\n3. Sân 4 - 150,000 VNĐ/giờ\n4. Sân 5 - 150,000 VNĐ/giờ\n\n🎯 **Bạn có muốn đặt một trong những sân này ngay bây giờ không?**\nTrả lời \'có\' hoặc \'không\'.', '2025-09-21 03:06:32'),
(206, '12', 'user', 'có', '2025-09-21 03:07:23'),
(207, '12', 'bot', '⏰ **Bước 2: Chọn giờ bắt đầu**\n\nVui lòng cho biết giờ bắt đầu đặt sân:\n• Ví dụ: \'18h\', \'18:00\', \'6 giờ tối\'', '2025-09-21 03:07:23'),
(208, '12', 'user', '7h sáng', '2025-09-21 03:07:44'),
(209, '12', 'bot', '✅ Đã chọn giờ bắt đầu: 07:00\n\n⏰ **Bước 3/5: Chọn giờ kết thúc**\n\nVui lòng cho biết giờ kết thúc:\n• Ví dụ: \'20h\', \'20:00\', \'8 giờ tối\'', '2025-09-21 03:07:44'),
(210, '12', 'user', '8h sáng', '2025-09-21 03:07:53'),
(211, '12', 'bot', '✅ Đã chọn giờ kết thúc: 08:00\n\n🏸 **Bước 4/5: Chọn sân**\n\nVui lòng chọn sân bạn muốn đặt:\n1. Sân 1 - 150,000 VNĐ/giờ\n2. Sân 2 - 150,000 VNĐ/giờ\n3. Sân 4 - 150,000 VNĐ/giờ\n4. Sân 5 - 150,000 VNĐ/giờ\n\nVui lòng trả lời số thứ tự sân (ví dụ: \'1\').', '2025-09-21 03:07:53'),
(212, '12', 'user', '3', '2025-09-21 03:08:05'),
(213, '12', 'bot', '❌ Có lỗi tạo tóm tắt. Vui lòng thử lại từ đầu.', '2025-09-21 03:08:05'),
(214, '12', 'user', 'ngày mai còn sân trống từ 7 giờ đến 8 giờ sáng không', '2025-09-21 03:14:59'),
(215, '12', 'bot', '✅ Tìm thấy các sân trống:\n1. Sân 1 - 150,000 VNĐ/giờ\n2. Sân 2 - 150,000 VNĐ/giờ\n3. Sân 4 - 150,000 VNĐ/giờ\n4. Sân 5 - 150,000 VNĐ/giờ\n\n🎯 **Bạn có muốn đặt một trong những sân này ngay bây giờ không?**\nTrả lời \'có\' hoặc \'không\'.', '2025-09-21 03:15:09'),
(216, '12', 'user', 'có', '2025-09-21 03:15:15'),
(217, '12', 'bot', '⏰ **Bước 2: Chọn giờ bắt đầu**\n\nVui lòng cho biết giờ bắt đầu đặt sân:\n• Ví dụ: \'18h\', \'18:00\', \'6 giờ tối\'', '2025-09-21 03:15:15'),
(218, '12', 'user', '7 giờ sáng', '2025-09-21 03:15:27'),
(219, '12', 'bot', '✅ Đã chọn giờ bắt đầu: 07:00\n\n⏰ **Bước 3/5: Chọn giờ kết thúc**\n\nVui lòng cho biết giờ kết thúc:\n• Ví dụ: \'20h\', \'20:00\', \'8 giờ tối\'', '2025-09-21 03:15:27'),
(220, '12', 'user', '8h sáng', '2025-09-21 03:15:32'),
(221, '12', 'bot', '✅ Đã chọn giờ kết thúc: 08:00\n\n🏸 **Bước 4/5: Chọn sân**\n\nVui lòng chọn sân bạn muốn đặt:\n1. Sân 1 - 150,000 VNĐ/giờ\n2. Sân 2 - 150,000 VNĐ/giờ\n3. Sân 4 - 150,000 VNĐ/giờ\n4. Sân 5 - 150,000 VNĐ/giờ\n\nVui lòng trả lời số thứ tự sân (ví dụ: \'1\').', '2025-09-21 03:15:32'),
(222, '12', 'user', 'sân 3', '2025-09-21 03:15:35'),
(223, '12', 'bot', '📋 **Tóm tắt thông tin đặt sân:**\n\n🏸 Sân: Sân 4\n📅 Ngày: 22/09/2025\n⏰ Thời gian: 07:00 - 08:00 (1.0 giờ)\n💰 Giá gốc: 150,000 VNĐ\n\n✅ **Thông tin đúng chưa?**\nTrả lời \'đúng\' hoặc \'ok\' để tiếp tục chọn phương thức thanh toán.', '2025-09-21 03:15:35'),
(224, '12', 'user', 'đúng', '2025-09-21 03:16:02'),
(225, '12', 'bot', '💳 **Chọn phương thức thanh toán:**\n\n1️⃣ **Thanh toán khi đến sân (ondelivery)**\n   • Thanh toán sau khi chơi xong\n   • Giá gốc không đổi\n\n2️⃣ **Chuyển khoản trước (prepaid) - GIẢM 10%**\n   • Chuyển khoản ngay bây giờ\n   • Được giảm 10% tổng tiền\n\nVui lòng trả lời:\n• \'1\' hoặc \'ondelivery\' để thanh toán sau\n• \'2\' hoặc \'prepaid\' để chuyển khoản trước', '2025-09-21 03:16:02'),
(226, '12', 'user', '1', '2025-09-21 03:16:18'),
(227, '12', 'bot', '✅ **Phương thức: Thanh toán khi đến sân**\n\n💰 Tổng tiền: 150,000 VNĐ\n🏸 Sân: Sân 4\n📅 Ngày: 2025-09-22\n⏰ Thời gian: 07:00 - 08:00\n\n🎯 **Xác nhận đặt sân này không?**\nTrả lời \'xác nhận\' để hoàn tất đặt sân.', '2025-09-21 03:16:18'),
(228, '12', 'user', 'xác nhận', '2025-09-21 03:16:32'),
(229, '12', 'bot', '🎉 **ĐẶT SÂN THÀNH CÔNG!**\n\n📝 Mã đặt sân: cf6dcfd1...\n🏸 Sân: Sân 4\n📅 Ngày: 2025-09-22\n⏰ Thời gian: 07:00 - 08:00\n💰 Tổng tiền: 150,000 VNĐ\n💳 Phương thức: Thanh toán khi đến sân\n📋 Trạng thái: Đã xác nhận\n\n✅ Bạn có thể đến sân theo giờ đã đặt. Vui lòng thanh toán tại quầy sau khi chơi xong!\n🙏 Cảm ơn bạn đã sử dụng dịch vụ Sunny Sport!', '2025-09-21 03:16:33'),
(230, '12', 'user', 'hôm nay từ 18 giờ đến 20 giờ có sân trống không', '2025-09-21 03:24:38'),
(231, '12', 'bot', '✅ Tìm thấy các sân trống:\n1. Sân 2 - 150,000 VNĐ/giờ\n2. Sân 3 - 150,000 VNĐ/giờ\n3. Sân 4 - 150,000 VNĐ/giờ\n4. Sân 5 - 150,000 VNĐ/giờ\n\n🎯 **Bạn có muốn đặt một trong những sân này ngay bây giờ không?**\nTrả lời \'có\' hoặc \'không\'.', '2025-09-21 03:24:43'),
(232, '12', 'user', 'có', '2025-09-21 03:25:06'),
(233, '12', 'bot', '⏰ **Bước 2: Chọn giờ bắt đầu**\n\nVui lòng cho biết giờ bắt đầu đặt sân:\n• Ví dụ: \'18h\', \'18:00\', \'6 giờ tối\'', '2025-09-21 03:25:07'),
(234, '12', 'user', '18h', '2025-09-21 03:25:21'),
(235, '12', 'bot', '✅ Đã chọn giờ bắt đầu: 18:00\n\n⏰ **Bước 3/5: Chọn giờ kết thúc**\n\nVui lòng cho biết giờ kết thúc:\n• Ví dụ: \'20h\', \'20:00\', \'8 giờ tối\'', '2025-09-21 03:25:21'),
(236, '12', 'user', '20h', '2025-09-21 03:25:24'),
(237, '12', 'bot', '✅ Đã chọn giờ kết thúc: 20:00\n\n🏸 **Bước 4/5: Chọn sân**\n\nCác sân trống trong khung giờ này:\n• Sân 2 - 150,000 VNĐ/giờ\n• Sân 3 - 150,000 VNĐ/giờ\n• Sân 4 - 150,000 VNĐ/giờ\n• Sân 5 - 150,000 VNĐ/giờ\n\nVui lòng trả lời tên sân bạn muốn đặt (ví dụ: \'Sân 1\').', '2025-09-21 03:25:24'),
(238, '12', 'user', 'sân 2', '2025-09-21 03:25:37'),
(239, '12', 'bot', '✅ Đã chọn Sân 2\n\n👤 **Thông tin liên hệ**\n\nVui lòng cho biết họ tên của bạn:', '2025-09-21 03:25:37'),
(240, '12', 'user', 'Bơ', '2025-09-21 03:25:53'),
(241, '12', 'bot', '✅ Đã lưu tên: Bơ\n\n📱 **Bước 5b/5: Số điện thoại**\n\nVui lòng cho biết số điện thoại liên hệ:', '2025-09-21 03:25:53'),
(242, '12', 'user', '0914090892', '2025-09-21 03:26:10'),
(243, '12', 'bot', '📋 **Tóm tắt thông tin đặt sân:**\n\n🏸 Sân: Sân 2\n📅 Ngày: 21/09/2025\n⏰ Thời gian: 18:00 - 20:00 (2.0 giờ)\n💰 Giá gốc: 300,000 VNĐ\n👤 Tên: Bơ\n📱 SĐT: 0914090892\n\n✅ **Thông tin đúng chưa?**\nTrả lời \'đúng\' hoặc \'chưa\' để tiếp tục chọn phương thức thanh toán.', '2025-09-21 03:26:10'),
(244, '12', 'user', 'đúng', '2025-09-21 03:26:21'),
(245, '12', 'bot', '💳 **Chọn phương thức thanh toán:**\n\n1️⃣ **Thanh toán khi đến sân (ondelivery)**\n   • Thanh toán sau khi chơi xong\n   • Giá gốc không đổi\n\n2️⃣ **Chuyển khoản trước (prepaid) - GIẢM 10%**\n   • Chuyển khoản ngay bây giờ\n   • Được giảm 10% tổng tiền\n\nVui lòng trả lời:\n• \'ondelivery\' để thanh toán sau\n• \'prepaid\' để chuyển khoản trước', '2025-09-21 03:26:21'),
(246, '12', 'user', 'ondelivery', '2025-09-21 03:26:45'),
(247, '12', 'bot', '✅ **Phương thức: Thanh toán khi đến sân**\n\n💰 Tổng tiền: 300,000 VNĐ\n🏸 Sân: Sân 2\n📅 Ngày: 2025-09-21\n⏰ Thời gian: 18:00 - 20:00\n\n🎯 **Xác nhận đặt sân này không?**\nTrả lời \'xác nhận\' để hoàn tất đặt sân.', '2025-09-21 03:26:45'),
(248, '12', 'user', 'xác nhận', '2025-09-21 03:26:54'),
(249, '12', 'bot', '🎉 **ĐẶT SÂN THÀNH CÔNG!**\n\n📝 Mã đặt sân: 53e2eb66...\n🏸 Sân: Sân 2\n📅 Ngày: 2025-09-21\n⏰ Thời gian: 18:00 - 20:00\n💰 Tổng tiền: 300,000 VNĐ\n💳 Phương thức: Thanh toán khi đến sân\n📋 Trạng thái: Đã xác nhận\n\n✅ Bạn có thể đến sân theo giờ đã đặt. Vui lòng thanh toán tại quầy sau khi chơi xong!\n🙏 Cảm ơn bạn đã sử dụng dịch vụ Sunny Sport!', '2025-09-21 03:26:54'),
(250, '12', 'user', 'tôi muốn đặt sân 12h đến 1 giờ trưa nay', '2025-09-21 03:30:20'),
(251, '12', 'bot', '✅ Tìm thấy các sân trống:\n1. Sân 3 - 150,000 VNĐ/giờ\n2. Sân 4 - 150,000 VNĐ/giờ\n3. Sân 5 - 150,000 VNĐ/giờ\n\n🎯 **Bạn có muốn đặt một trong những sân này ngay bây giờ không?**\nTrả lời \'có\' hoặc \'không\'.', '2025-09-21 03:30:24'),
(252, '12', 'user', 'tôi muốn đặt sân 12h đến 1 giờ hôm nay', '2025-09-21 03:31:20'),
(253, '12', 'bot', '🏸 **Chọn sân bạn muốn đặt:**\n\nCác sân trống trong khung giờ này:\n• Sân 3 - 150,000 VNĐ/giờ\n• Sân 4 - 150,000 VNĐ/giờ\n• Sân 5 - 150,000 VNĐ/giờ\n\nVui lòng trả lời tên sân bạn muốn đặt (ví dụ: \'Sân 1\').', '2025-09-21 03:31:20'),
(254, '12', 'user', 'tôi muốn đặt sân 12h đến 1 giờ hôm nay', '2025-09-21 03:39:11'),
(255, '12', 'bot', '✅ Tìm thấy các sân trống:\n1. Sân 3 - 150,000 VNĐ/giờ\n2. Sân 4 - 150,000 VNĐ/giờ\n3. Sân 5 - 150,000 VNĐ/giờ\n\n🎯 **Bạn có muốn đặt một trong những sân này ngay bây giờ không?**\nTrả lời \'có\' hoặc \'không\'.', '2025-09-21 03:39:15'),
(256, '12', 'user', 'tôi muốn đặt sân 12h đến 1 giờ hôm nay', '2025-09-21 03:42:56'),
(257, '12', 'bot', '✅ Tìm thấy các sân trống:\n1. Sân 3 - 150,000 VNĐ/giờ\n2. Sân 4 - 150,000 VNĐ/giờ\n3. Sân 5 - 150,000 VNĐ/giờ\n\n🎯 **Bạn có muốn đặt một trong những sân này ngay bây giờ không?**\nTrả lời \'có\' hoặc \'không\'.', '2025-09-21 03:43:01'),
(258, '12', 'user', 'tôi muốn đặt sân 12h đến 1 giờ hôm nay', '2025-09-21 03:47:03'),
(259, '12', 'bot', '✅ Tìm thấy các sân trống:\n1. Sân 3 - 150,000 VNĐ/giờ\n2. Sân 4 - 150,000 VNĐ/giờ\n3. Sân 5 - 150,000 VNĐ/giờ\n\n🎯 **Bạn có muốn đặt một trong những sân này ngay bây giờ không?**\nTrả lời \'có\' hoặc \'không\'.', '2025-09-21 03:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `courts`
--

CREATE TABLE `courts` (
  `court_id` int(11) NOT NULL COMMENT 'Mã sân',
  `court_name` varchar(50) NOT NULL COMMENT 'Tên sân (Sân 1, Sân 2,...)',
  `description` text DEFAULT NULL COMMENT 'Mô tả sân',
  `price_per_hour` decimal(10,2) NOT NULL DEFAULT 150000.00 COMMENT 'Giá thuê mỗi giờ (VNĐ)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin sân cầu lông';

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`court_id`, `court_name`, `description`, `price_per_hour`) VALUES
(1, 'Sân 1', 'Sàn gỗ tiêu chuẩn, phù hợp tập luyện và thi đấu', 150000.00),
(2, 'Sân 2', 'Trang bị điều hòa, tạo không gian thoải mái', 150000.00),
(3, 'Sân 3', 'Hệ thống chiếu sáng hiện đại, đảm bảo chất lượng trận đấu', 150000.00),
(4, 'Sân 4', 'Ánh sáng tốt và không gian thoáng mát', 150000.00),
(5, 'Sân 5', 'Sàn đa năng, có thể sử dụng cho nhiều hoạt động thể thao', 150000.00);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL COMMENT 'Mã sự kiện',
  `event_name` varchar(200) NOT NULL COMMENT 'Tên sự kiện',
  `description` text NOT NULL COMMENT 'Mô tả sự kiện',
  `event_date` date NOT NULL COMMENT 'Ngày diễn ra',
  `start_time` time DEFAULT NULL COMMENT 'Giờ bắt đầu',
  `end_time` time DEFAULT NULL COMMENT 'Giờ kết thúc',
  `location` varchar(200) DEFAULT NULL COMMENT 'Địa điểm',
  `max_participants` int(11) DEFAULT NULL COMMENT 'Số người tham gia tối đa',
  `current_participants` int(11) DEFAULT 0 COMMENT 'Số người đã đăng ký',
  `registration_fee` decimal(8,2) DEFAULT 0.00 COMMENT 'Phí đăng ký (VNĐ)',
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming' COMMENT 'Trạng thái: upcoming (sắp tới), ongoing (đang diễn ra), completed (hoàn thành), cancelled (hủy)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin sự kiện';

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `description`, `event_date`, `start_time`, `end_time`, `location`, `max_participants`, `current_participants`, `registration_fee`, `status`, `created_at`) VALUES
(1, 'Giải cầu lông Sunny Sport 2025', 'Giải đấu thường niên với các nội dung đơn nam, đơn nữ, đôi nam, đôi nữ.', '2025-09-01', '08:00:00', '17:00:00', 'Trung tâm thể thao Sunny Sport, Hà Nội', 100, 10, 200000.00, 'upcoming', '2025-08-16 01:00:00'),
(2, 'Giao lưu cầu lông tháng 8', 'Buổi giao lưu dành cho người chơi mới', '2025-08-25', '14:00:00', '17:00:00', 'Sân 3, Sunny Sport', 50, 5, 100000.00, 'upcoming', '2025-08-16 01:05:00'),
(3, 'Lớp học cầu lông cơ bản', 'Khóa học dành cho người mới bắt đầu', '2025-09-10', '18:00:00', '20:00:00', 'Sân 4, Sunny Sport', 20, 8, 500000.00, 'upcoming', '2025-08-16 01:10:00'),
(4, 'Giải đôi nam Sunny Sport', 'Giải đấu đôi nam cấp câu lạc bộ', '2025-09-15', '09:00:00', '16:00:00', 'Trung tâm thể thao Sunny Sport', 60, 12, 150000.00, 'upcoming', '2025-08-16 01:15:00'),
(5, 'Hội thảo kỹ thuật cầu lông', 'Hội thảo chia sẻ kinh nghiệm từ VĐV chuyên nghiệp', '2025-09-20', '10:00:00', '12:00:00', 'Hội trường Sunny Sport', 30, 5, 300000.00, 'upcoming', '2025-08-16 01:20:00'),
(6, 'Giao lưu cầu lông trẻ em', 'Buổi giao lưu cho trẻ từ 8-14 tuổi', '2025-08-30', '08:00:00', '11:00:00', 'Sân 5, Sunny Sport', 40, 10, 80000.00, 'upcoming', '2025-08-16 01:25:00'),
(7, 'Giải đôi nữ Sunny Sport', 'Giải đấu đôi nữ cấp câu lạc bộ', '2025-09-25', '09:00:00', '16:00:00', 'Trung tâm thể thao Sunny Sport', 50, 8, 150000.00, 'upcoming', '2025-08-16 01:30:00'),
(8, 'Lớp học nâng cao cầu lông', 'Khóa học cho người chơi trình độ trung bình', '2025-10-01', '18:00:00', '20:00:00', 'Sân 6, Sunny Sport', 15, 3, 600000.00, 'upcoming', '2025-08-16 01:35:00'),
(9, 'Ngày hội thể thao Sunny Sport', 'Sự kiện giao lưu thể thao đa môn', '2025-10-05', '07:00:00', '17:00:00', 'Trung tâm thể thao Sunny Sport', 200, 20, 50000.00, 'upcoming', '2025-08-16 01:40:00'),
(10, 'Giải đơn nam Sunny Sport', 'Giải đấu đơn nam cấp câu lạc bộ', '2025-10-10', '08:00:00', '16:00:00', 'Trung tâm thể thao Sunny Sport', 80, 15, 150000.00, 'upcoming', '2025-08-16 01:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `registration_id` int(11) NOT NULL COMMENT 'Mã đăng ký',
  `event_id` int(11) DEFAULT NULL COMMENT 'Mã sự kiện',
  `user_id` int(11) DEFAULT NULL COMMENT 'Mã người dùng',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Ngày đăng ký',
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending' COMMENT 'Trạng thái: pending (chờ), confirmed (xác nhận), cancelled (hủy)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin đăng ký sự kiện';

-- --------------------------------------------------------

--
-- Table structure for table `forum_categories`
--

CREATE TABLE `forum_categories` (
  `category_id` int(11) NOT NULL COMMENT 'Mã danh mục',
  `category_name` varchar(100) NOT NULL COMMENT 'Tên danh mục',
  `description` text DEFAULT NULL COMMENT 'Mô tả danh mục'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu danh mục diễn đàn';

--
-- Dumping data for table `forum_categories`
--

INSERT INTO `forum_categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Thảo luận chung', 'Nơi thảo luận về kỹ thuật, chiến thuật cầu lông'),
(2, 'Sự kiện & Giải đấu', 'Thông tin về các giải đấu và sự kiện thể thao'),
(3, 'Thị trường & Sản phẩm', 'Chia sẻ về dụng cụ, thiết bị thể thao'),
(4, 'Hỏi đáp kỹ thuật', 'Giải đáp thắc mắc về cách chơi cầu lông'),
(5, 'Giao lưu cầu lông', 'Kết nối, tìm bạn đánh cầu lông'),
(6, 'Tin tức thể thao', 'Cập nhật tin tức thể thao mới nhất'),
(7, 'Chia sẻ kinh nghiệm', 'Kinh nghiệm thi đấu và luyện tập'),
(8, 'Mua bán đồ cũ', 'Rao vặt thiết bị thể thao đã qua sử dụng'),
(9, 'Huấn luyện viên', 'Tìm kiếm HLV và lớp học cầu lông'),
(10, 'Phản hồi & Góp ý', 'Góp ý cho câu lạc bộ Sunny Sport');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL COMMENT 'Mã bài đăng',
  `thread_id` int(11) DEFAULT NULL COMMENT 'Mã chủ đề',
  `user_id` int(11) DEFAULT NULL COMMENT 'Mã người dùng',
  `content` text NOT NULL COMMENT 'Nội dung bài đăng',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu các bài đăng trong diễn đàn';

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`post_id`, `thread_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, NULL, 'Tập trung vào lực cổ tay và góc đánh, thử bài tập plank để tăng sức mạnh!', '2025-08-16 01:00:00'),
(2, 2, NULL, 'Giải năm nay có nội dung đôi nam nữ, rất đáng mong chờ!', '2025-08-16 01:05:00'),
(3, 3, NULL, 'Vợt Astrox 99 Pro rất hợp đánh tấn công, nhưng hơi nặng.', '2025-08-16 01:10:00'),
(4, 4, NULL, 'Nên tập bài tập bước chân chéo để cải thiện tốc độ.', '2025-08-16 01:15:00'),
(5, 5, NULL, 'Mình ở Cầu Giấy, ai muốn đánh chung inbox nhé!', '2025-08-16 01:20:00'),
(6, 6, NULL, 'Chúc mừng đội tuyển Việt Nam, hy vọng tiếp tục tỏa sáng!', '2025-08-16 01:25:00'),
(7, 7, NULL, 'Chọn giày có đế chống trượt và hỗ trợ mắt cá chân là tốt nhất.', '2025-08-16 01:30:00'),
(8, 8, NULL, 'Vợt còn mới 90%, giá 1,5 triệu, liên hệ mình nhé.', '2025-08-16 01:35:00'),
(9, 9, NULL, 'Mình cần HLV dạy cho con 10 tuổi, ai biết giới thiệu giúp!', '2025-08-16 01:40:00'),
(10, 10, NULL, 'Thêm khung 20:00-22:00 sẽ tiện cho dân văn phòng.', '2025-08-16 01:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `forum_threads`
--

CREATE TABLE `forum_threads` (
  `thread_id` int(11) NOT NULL COMMENT 'Mã chủ đề',
  `category_id` int(11) DEFAULT NULL COMMENT 'Mã danh mục',
  `user_id` int(11) DEFAULT NULL COMMENT 'Mã người dùng',
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề chủ đề',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian cập nhật cuối'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu các chủ đề trong diễn đàn';

--
-- Dumping data for table `forum_threads`
--

INSERT INTO `forum_threads` (`thread_id`, `category_id`, `user_id`, `title`, `created_at`, `last_updated`) VALUES
(1, 1, NULL, 'Cách cải thiện cú đập cầu mạnh', '2025-08-16 01:00:00', '2025-08-16 01:00:00'),
(2, 2, NULL, 'Giải đấu Sunny Sport 2025 có gì hot?', '2025-08-16 01:05:00', '2025-08-16 01:05:00'),
(3, 3, NULL, 'Review vợt Yonex Astrox 99 Pro', '2025-08-16 01:10:00', '2025-08-16 01:10:00'),
(4, 4, NULL, 'Làm sao để di chuyển nhanh hơn?', '2025-08-16 01:15:00', '2025-08-16 01:15:00'),
(5, 5, NULL, 'Tìm bạn đánh cầu lông tại Hà Nội', '2025-08-16 01:20:00', '2025-08-16 01:20:00'),
(6, 6, NULL, 'VĐV cầu lông Việt Nam giành huy chương', '2025-08-16 01:25:00', '2025-08-16 01:25:00'),
(7, 7, NULL, 'Kinh nghiệm chọn giày cầu lông', '2025-08-16 01:30:00', '2025-08-16 01:30:00'),
(8, 8, NULL, 'Bán vợt Yonex cũ, giá tốt', '2025-08-16 01:35:00', '2025-08-16 01:35:00'),
(9, 9, NULL, 'Tìm HLV dạy cầu lông cho trẻ em', '2025-08-16 01:40:00', '2025-08-16 01:40:00'),
(10, 10, NULL, 'Góp ý thêm khung giờ đặt sân buổi tối', '2025-08-16 01:45:00', '2025-08-16 01:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `livestreams`
--

CREATE TABLE `livestreams` (
  `livestream_id` int(11) NOT NULL COMMENT 'Mã livestream',
  `event_id` int(11) DEFAULT NULL COMMENT 'Mã sự kiện',
  `livestream_url` varchar(255) NOT NULL COMMENT 'Đường dẫn livestream',
  `title` varchar(200) NOT NULL COMMENT 'Tiêu đề livestream',
  `description` text DEFAULT NULL COMMENT 'Mô tả livestream',
  `start_time` datetime DEFAULT NULL COMMENT 'Thời gian bắt đầu',
  `status` enum('scheduled','live','ended') DEFAULT 'scheduled' COMMENT 'Trạng thái: scheduled (lên lịch), live (đang phát), ended (kết thúc)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin livestream';

--
-- Dumping data for table `livestreams`
--

INSERT INTO `livestreams` (`livestream_id`, `event_id`, `livestream_url`, `title`, `description`, `start_time`, `status`, `created_at`) VALUES
(1, 1, 'https://youtube.com/live/sunnysport2025', 'Livestream Giải cầu lông Sunny Sport 2025', 'Trực tiếp các trận đấu cầu lông', '2025-09-01 08:00:00', 'scheduled', '2025-08-16 01:00:00'),
(2, 2, 'https://youtube.com/live/sunnysport-aug', 'Livestream Giao lưu cầu lông tháng 8', 'Trực tiếp buổi giao lưu', '2025-08-25 14:00:00', 'scheduled', '2025-08-16 01:05:00'),
(3, 3, 'https://youtube.com/live/sunnysport-class', 'Livestream Lớp học cầu lông cơ bản', 'Trực tiếp lớp học', '2025-09-10 18:00:00', 'scheduled', '2025-08-16 01:10:00'),
(4, 4, 'https://youtube.com/live/sunnysport-men', 'Livestream Giải đôi nam Sunny Sport', 'Trực tiếp các trận đấu đôi nam', '2025-09-15 09:00:00', 'scheduled', '2025-08-16 01:15:00'),
(5, 5, 'https://youtube.com/live/sunnysport-workshop', 'Livestream Hội thảo kỹ thuật cầu lông', 'Trực tiếp hội thảo', '2025-09-20 10:00:00', 'scheduled', '2025-08-16 01:20:00'),
(6, 6, 'https://youtube.com/live/sunnysport-kids', 'Livestream Giao lưu cầu lông trẻ em', 'Trực tiếp buổi giao lưu trẻ em', '2025-08-30 08:00:00', 'scheduled', '2025-08-16 01:25:00'),
(7, 7, 'https://youtube.com/live/sunnysport-women', 'Livestream Giải đôi nữ Sunny Sport', 'Trực tiếp các trận đấu đôi nữ', '2025-09-25 09:00:00', 'scheduled', '2025-08-16 01:30:00'),
(8, 8, 'https://youtube.com/live/sunnysport-advanced', 'Livestream Lớp học nâng cao cầu lông', 'Trực tiếp lớp học nâng cao', '2025-10-01 18:00:00', 'scheduled', '2025-08-16 01:35:00'),
(9, 9, 'https://youtube.com/live/sunnysport-festival', 'Livestream Ngày hội thể thao Sunny Sport', 'Trực tiếp ngày hội thể thao', '2025-10-05 07:00:00', 'scheduled', '2025-08-16 01:40:00'),
(10, 10, 'https://youtube.com/live/sunnysport-singles', 'Livestream Giải đơn nam Sunny Sport', 'Trực tiếp các trận đấu đơn nam', '2025-10-10 08:00:00', 'scheduled', '2025-08-16 01:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL COMMENT 'Mã thông báo',
  `user_id` int(11) DEFAULT NULL COMMENT 'Mã người dùng',
  `title` varchar(200) NOT NULL COMMENT 'Tiêu đề thông báo',
  `message` text NOT NULL COMMENT 'Nội dung thông báo',
  `type` enum('booking','event','forum','system') DEFAULT 'system' COMMENT 'Loại thông báo: booking (đặt sân), event (sự kiện), forum (diễn đàn), system (hệ thống)',
  `is_read` tinyint(1) DEFAULT 0 COMMENT 'Đã đọc: 1 (đã đọc), 0 (chưa đọc)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông báo cho người dùng';

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL COMMENT 'Mã đơn hàng',
  `user_id` int(11) DEFAULT NULL COMMENT 'Mã người dùng',
  `recipient_name` varchar(255) DEFAULT NULL COMMENT 'Tên người nhận',
  `shipping_address` text DEFAULT NULL COMMENT 'Địa chỉ giao hàng',
  `phone_number` varchar(20) DEFAULT NULL COMMENT 'Số điện thoại nhận hàng',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú đơn hàng',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Tổng tiền (VNĐ)',
  `status` enum('pending','completed','cancelled') DEFAULT 'pending' COMMENT 'Trạng thái: pending (chờ), completed (hoàn thành), cancelled (hủy)',
  `payment_method` enum('cod','card') DEFAULT 'cod' COMMENT 'Phương thức thanh toán: cod (khi nhận hàng), card (bằng thẻ)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin đơn hàng';

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `recipient_name`, `shipping_address`, `phone_number`, `notes`, `total_amount`, `status`, `payment_method`, `created_at`) VALUES
(15, 11, 'Trần Phương Thùyyy', '12345 nguyen van thiet, 13753, 359, 36', '09140908700', '', 2500000.00, 'completed', 'cod', '2025-08-22 05:48:53'),
(16, 11, 'Trần Phương Thùy', '54332, 1339, 43, 4', '0914090876', 'aa', 1500000.00, 'pending', 'cod', '2025-08-22 09:40:12'),
(17, 11, 'Trần Phương Thùy', '54332, 1339, 43, 4', '0914090876', 'aa', 1500000.00, 'pending', 'cod', '2025-08-22 09:42:22'),
(18, 11, 'Trần Phương Thùy', 'aaa, 1, 1, 1', '0914090876', '', 100000.00, 'pending', 'cod', '2025-08-22 09:45:37'),
(19, 11, 'Trần Phương Thùy', 'tằn tân, Xã Mường Bằng, Huyện Mai Sơn, Tỉnh Sơn La', '0914090876', '', 1500000.00, 'pending', 'cod', '2025-08-22 10:09:50'),
(20, 11, 'Trần Phương Thùy', '2818 hjo gom, Phường Ngọc Châu, Thành phố Hải Dương, Tỉnh Hải Dương', '0914090876', '1111', 1500000.00, 'pending', 'cod', '2025-08-23 15:18:40'),
(21, 11, 'Trần Phương Thùy', '111 dsfa áaa, Phường Quang Trung, Thành phố Hà Giang, Tỉnh Hà Giang', '0914090876', '', 2200000.00, 'pending', 'cod', '2025-08-23 15:23:27'),
(22, 11, 'Trần Phương Thùy', '12345 nguyen van thiet, Phường Tân Tiến, Thành phố Bắc Giang, Tỉnh Bắc Giang', '0914090876', '', 300000.00, 'pending', 'cod', '2025-08-23 15:29:51'),
(23, 11, 'Trần Phương Thùy', 'tran van on, Xã Vĩnh Phương, Thành phố Nha Trang, Tỉnh Khánh Hòa', '0914090876', '', 800000.00, 'pending', 'card', '2025-08-23 15:31:25'),
(24, 11, 'Phan Minh Thắng', 'đối diện cà phê lê vy 2, Phường 9, Thành phố Vĩnh Long, Tỉnh Vĩnh Long', '0834029049', '', 700000.00, 'pending', 'cod', '2025-08-29 09:08:26'),
(25, 11, 'Trần Văn Tèo', 'ql1z, Xã Quảng Sơn, Huyện Đăk Glong, Tỉnh Đắk Nông', '0914090142', '', 1500000.00, 'pending', 'cod', '2025-08-29 13:14:53'),
(26, 11, 'Út mén', 'ql54, Xã Đồn Đạc, Huyện Ba Chẽ, Tỉnh Quảng Ninh', '0914090842', '', 480000.00, 'completed', 'cod', '2025-08-29 13:18:09');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL COMMENT 'Mã chi tiết đơn hàng',
  `order_id` int(11) DEFAULT NULL COMMENT 'Mã đơn hàng',
  `product_id` int(11) DEFAULT NULL COMMENT 'Mã sản phẩm',
  `quantity` int(11) NOT NULL COMMENT 'Số lượng',
  `price` decimal(10,2) NOT NULL COMMENT 'Giá mỗi sản phẩm (VNĐ)',
  `variant_id` int(11) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu chi tiết sản phẩm trong đơn hàng';

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`, `variant_id`, `size`, `color`) VALUES
(16, 15, 1, 1, 2500000.00, NULL, NULL, NULL),
(17, 16, 2, 1, 1500000.00, NULL, NULL, NULL),
(18, 17, 2, 1, 1500000.00, NULL, NULL, NULL),
(19, 18, 5, 1, 100000.00, NULL, NULL, NULL),
(20, 19, 2, 1, 1500000.00, NULL, NULL, NULL),
(21, 20, 2, 1, 1500000.00, NULL, NULL, NULL),
(22, 21, 6, 1, 2200000.00, NULL, NULL, NULL),
(23, 22, 3, 1, 300000.00, NULL, NULL, NULL),
(24, 23, 9, 1, 800000.00, NULL, NULL, NULL),
(25, 24, 8, 2, 350000.00, NULL, NULL, NULL),
(26, 25, 2, 1, 1500000.00, 2, '40', 'Black'),
(27, 26, 4, 1, 450000.00, 4, 'M', 'Blue');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL COMMENT 'Mã sản phẩm',
  `product_name` varchar(100) NOT NULL COMMENT 'Tên sản phẩm',
  `category_id` int(11) DEFAULT NULL COMMENT 'Mã danh mục sản phẩm',
  `description` text DEFAULT NULL COMMENT 'Mô tả sản phẩm',
  `price` decimal(10,2) NOT NULL COMMENT 'Giá sản phẩm (VNĐ)',
  `stock` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho tổng (tính từ variants nếu có)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin sản phẩm';

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category_id`, `description`, `price`, `stock`, `created_at`) VALUES
(1, 'Vợt Yonex Astrox 99 Pro', 1, 'Vợt cầu lông cao cấp, phù hợp đánh tấn công', 2500000.00, 30, '2025-08-16 01:00:00'),
(2, 'Giày Lining Attack 2025', 2, 'Giày cầu lông chuyên nghiệp, nhẹ và bền', 1500000.00, 20, '2025-08-16 01:05:00'),
(3, 'Quả cầu lông Yonex Aerosensa', 3, 'Quả cầu lông thi đấu tiêu chuẩn', 300000.00, 100, '2025-08-16 01:10:00'),
(4, 'Áo thi đấu Lining Pro', 4, 'Áo cầu lông thoáng khí, thấm hút mồ hôi', 450000.00, 50, '2025-08-16 01:15:00'),
(5, 'Băng cuốn cổ tay Victor', 7, 'Băng cuốn cổ tay hỗ trợ thi đấu', 100000.00, 80, '2025-08-16 01:20:00'),
(6, 'Vợt Yonex Nanoflare 800', 1, 'Vợt cầu lông nhẹ, phù hợp phòng thủ', 2200000.00, 25, '2025-08-16 01:25:00'),
(7, 'Giày Asics Sky Elite FF', 2, 'Giày cầu lông chống trượt, độ bám tốt', 1800000.00, 15, '2025-08-16 01:30:00'),
(8, 'Quần cầu lông Yonex', 5, 'Quần ngắn thoải mái, thiết kế thể thao', 350000.00, 40, '2025-08-16 01:35:00'),
(9, 'Túi đựng vợt Lining 6 cây', 6, 'Túi đựng vợt cao cấp, sức chứa 6 vợt', 800000.00, 10, '2025-08-16 01:40:00'),
(10, 'Dây đan lưới Yonex BG65', 7, 'Dây đan lưới bền, độ căng tốt', 150000.00, 60, '2025-08-16 01:45:00'),
(12, 'Áo đấu', 4, 'Áo thiết kế riêng, vải mè thoáng mát, công nghệ in Korea.', 150000.00, 20, '2025-08-31 10:12:40');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `category_id` int(11) NOT NULL COMMENT 'Mã danh mục sản phẩm',
  `category_name` varchar(100) NOT NULL COMMENT 'Tên danh mục',
  `description` text DEFAULT NULL COMMENT 'Mô tả danh mục'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu danh mục sản phẩm';

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Vợt', 'Vợt cầu lông'),
(2, 'Giày', 'Giày cầu lông'),
(3, 'Quả cầu', 'Quả cầu lông'),
(4, 'Áo', 'Áo thi đấu cầu lông'),
(5, 'Quần', 'Quần thi đấu cầu lông'),
(6, 'Túi', 'Túi đựng đồ thể thao'),
(7, 'Phụ kiện', 'Phụ kiện cầu lông như băng cuốn, dây đan');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL COMMENT 'Mã hình ảnh',
  `product_id` int(11) DEFAULT NULL COMMENT 'Mã sản phẩm',
  `image_url` varchar(255) NOT NULL COMMENT 'Đường dẫn hình ảnh',
  `alt_text` varchar(255) DEFAULT NULL COMMENT 'Mô tả hình ảnh (SEO)',
  `is_primary` tinyint(1) DEFAULT 0 COMMENT 'Hình ảnh chính: 1 (chính), 0 (phụ)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu hình ảnh sản phẩm';

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_url`, `alt_text`, `is_primary`, `created_at`) VALUES
(1, 1, 'vot-cau-long-yonex-astrox-99-pro-trang-chinh-hang.webp', 'Vợt Yonex Astrox 99 Pro', 1, '2025-08-16 01:00:00'),
(2, 2, 'lining-attack.jpg', 'Giày Lining Attack 2025', 1, '2025-08-16 01:05:00'),
(3, 3, 'yonex-aerosensa.webp', 'Quả cầu lông Yonex Aerosensa', 1, '2025-08-16 01:10:00'),
(4, 4, 'lining-shirt.jpg', 'Áo thi đấu Lining Pro', 1, '2025-08-16 01:15:00'),
(5, 5, 'victor-wristband.jpg', 'Băng cuốn cổ tay Victor', 1, '2025-08-16 01:20:00'),
(6, 6, 'yonex-nanoflare.webp', 'Vợt Yonex Nanoflare 800', 1, '2025-08-16 01:25:00'),
(7, 7, 'asics-skyelite.jpg', 'Giày Asics Sky Elite FF', 1, '2025-08-16 01:30:00'),
(8, 8, 'yonex-shorts.jpg', 'Quần cầu lông Yonex', 1, '2025-08-16 01:35:00'),
(9, 9, 'lining-bag.jpg', 'Túi đựng vợt Lining 6 cây', 1, '2025-08-16 01:40:00'),
(10, 10, 'yonex-bg65.jpg', 'Dây đan lưới Yonex BG65', 1, '2025-08-16 01:45:00'),
(11, 1, 'YonexAstrox99Pro.jpg', 'Vợt Yonex Astrox 99 Pro', 0, '2025-08-15 18:00:00'),
(12, 1, 'Astrox_99_Pro_Cherry', 'Vợt Yonex Astrox 99 Pro', 0, '2025-08-15 18:00:00'),
(13, 2, 'lining-attack-side.jpg', 'Giày Lining Attack 2025', 0, '2025-08-15 18:05:00'),
(14, 2, 'lining-attack.webp', 'Giày Lining Attack 2025', 0, '2025-08-15 18:05:00'),
(15, 4, 'lining-shirt-front.jpg', 'Áo thi đấu Lining Pro', 0, '2025-08-15 18:15:00'),
(16, 4, 'lining-shirt-back.jpg', 'Áo thi đấu Lining Pro', 0, '2025-08-15 18:15:00'),
(17, 6, 'yonex-nanoflare-side.jpg', 'Vợt Yonex Nanoflare 800', 0, '2025-08-15 18:25:00'),
(18, 7, 'asics-skyelite-side.webp', 'Giày Asics Sky Elite FF', 0, '2025-08-15 18:30:00'),
(20, 12, 'Áo-đấu.jpg', 'Áo đấu', 1, '2025-08-31 10:12:40');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL COMMENT 'Mã biến thể',
  `product_id` int(11) DEFAULT NULL COMMENT 'Mã sản phẩm',
  `size` varchar(50) DEFAULT NULL COMMENT 'Kích thước (e.g., S, M, L, 39, 40)',
  `color` varchar(50) DEFAULT NULL COMMENT 'Màu sắc (e.g., Red, Blue, Black)',
  `stock` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho cho biến thể',
  `price` decimal(10,2) DEFAULT NULL COMMENT 'Giá cho biến thể (nếu khác giá gốc)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu biến thể sản phẩm (kích thước, màu sắc)';

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `size`, `color`, `stock`, `price`, `created_at`) VALUES
(1, 2, '39', 'Black', 5, 1500000.00, '2025-08-29 07:53:02'),
(2, 2, '40', 'Black', 10, 1500000.00, '2025-08-29 07:53:02'),
(3, 2, '41', 'Red', 5, 1500000.00, '2025-08-29 07:53:02'),
(4, 4, 'M', 'Blue', 20, 450000.00, '2025-08-29 07:53:02'),
(5, 4, 'L', 'Blue', 15, 450000.00, '2025-08-29 07:53:02'),
(6, 4, 'XL', 'White', 15, 450000.00, '2025-08-29 07:53:02'),
(7, 7, '39', 'White', 5, 1800000.00, '2025-08-29 07:53:02'),
(8, 7, '40', 'White', 5, 1800000.00, '2025-08-29 07:53:02'),
(9, 7, '41', 'Black', 5, 1800000.00, '2025-08-29 07:53:02'),
(10, 8, 'M', 'Black', 20, 350000.00, '2025-08-29 07:53:02'),
(11, 8, 'L', 'Black', 10, 350000.00, '2025-08-29 07:53:02'),
(12, 8, 'XL', 'Grey', 10, 350000.00, '2025-08-29 07:53:02'),
(13, 12, 'S', 'Đen , Trắng', 12, 150000.00, '2025-08-31 10:12:40'),
(14, 12, 'M', 'Đen Trắng , Hồng', 8, 150000.00, '2025-08-31 10:12:40');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL COMMENT 'Mã cấu hình',
  `setting_key` varchar(100) NOT NULL COMMENT 'Khóa cấu hình',
  `setting_value` text DEFAULT NULL COMMENT 'Giá trị cấu hình',
  `description` text DEFAULT NULL COMMENT 'Mô tả cấu hình',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Thời gian cập nhật'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu các thiết lập hệ thống';

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Sunny Sport', 'Tên website', '2025-08-16 01:00:00'),
(2, 'site_description', 'Câu lạc bộ thể thao Sunny Sport', 'Mô tả website', '2025-08-16 01:05:00'),
(3, 'contact_email', 'info@sunnysport.com', 'Email liên hệ', '2025-08-16 01:10:00'),
(4, 'contact_phone', '0123456789', 'Số điện thoại liên hệ', '2025-08-16 01:15:00'),
(5, 'booking_discount', '10', 'Phần trăm giảm giá khi thanh toán trước (%)', '2025-08-16 01:20:00'),
(6, 'max_booking_hours', '4', 'Số giờ tối đa có thể đặt sân', '2025-08-16 01:25:00'),
(7, 'opening_hour', '06:00', 'Giờ mở cửa', '2025-08-16 01:30:00'),
(8, 'closing_hour', '22:00', 'Giờ đóng cửa', '2025-08-16 01:35:00'),
(9, 'bank_account', 'Vietcombank 1234567890', 'Tài khoản ngân hàng nhận thanh toán trước', '2025-08-16 01:40:00'),
(10, 'payment_gateway', 'VNPay', 'Cổng thanh toán online được sử dụng', '2025-08-16 01:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `shop_info`
--

CREATE TABLE `shop_info` (
  `shop_id` int(11) NOT NULL COMMENT 'Mã shop',
  `shop_name` varchar(255) NOT NULL COMMENT 'Tên shop/câu lạc bộ',
  `description` text DEFAULT NULL COMMENT 'Giới thiệu chung về shop',
  `address` varchar(255) DEFAULT NULL COMMENT 'Địa chỉ',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Số điện thoại liên hệ',
  `email` varchar(100) DEFAULT NULL COMMENT 'Email liên hệ',
  `website` varchar(255) DEFAULT NULL COMMENT 'Website chính thức',
  `facebook` varchar(255) DEFAULT NULL COMMENT 'Fanpage Facebook',
  `instagram` varchar(255) DEFAULT NULL COMMENT 'Instagram',
  `opening_hours` varchar(100) DEFAULT NULL COMMENT 'Giờ mở cửa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Ngày tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông tin giới thiệu về shop Sunny Sport';

--
-- Dumping data for table `shop_info`
--

INSERT INTO `shop_info` (`shop_id`, `shop_name`, `description`, `address`, `phone`, `email`, `website`, `facebook`, `instagram`, `opening_hours`, `created_at`) VALUES
(1, 'Sunny Sport', 'Sunny Sport là trung tâm thể thao hiện đại chuyên về cầu lông và các sản phẩm thể thao. Chúng tôi cung cấp sân bãi, dụng cụ chính hãng, tổ chức sự kiện và lớp học cầu lông cho mọi lứa tuổi.', '123 Đường Nguyễn Văn Thể Thao, Quận Cầu Giấy, Hà Nội', '0914 123 456', 'support@sunnysport.vn', 'https://sunnysport.vn', 'https://facebook.com/sunnysport.vn', 'https://instagram.com/sunnysport.vn', '06:00 - 22:00 hàng ngày', '2025-09-19 02:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL COMMENT 'Mã giao dịch',
  `user_id` int(11) DEFAULT NULL COMMENT 'Mã người dùng',
  `booking_id` int(11) DEFAULT NULL COMMENT 'Mã đặt sân (NULL nếu không liên quan)',
  `order_id` int(11) DEFAULT NULL COMMENT 'Mã đơn hàng (NULL nếu không liên quan)',
  `amount` decimal(10,2) NOT NULL COMMENT 'Số tiền (VNĐ)',
  `transaction_type` enum('payment','refund') NOT NULL COMMENT 'Loại giao dịch: payment (thanh toán), refund (hoàn tiền)',
  `payment_method` enum('bank_transfer','cash','online') NOT NULL COMMENT 'Phương thức thanh toán: bank_transfer (chuyển khoản), cash (tiền mặt), online (cổng thanh toán)',
  `payment_status` enum('pending','received','failed') DEFAULT 'pending' COMMENT 'Trạng thái nhận tiền: pending (chờ), received (đã nhận), failed (thất bại)',
  `status` enum('pending','completed','failed') DEFAULT 'pending' COMMENT 'Trạng thái giao dịch: pending (chờ), completed (hoàn thành), failed (thất bại)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo',
  `transaction_code` varchar(50) DEFAULT NULL COMMENT 'Mã giao dịch (duy nhất)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin giao dịch';

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `booking_id`, `order_id`, `amount`, `transaction_type`, `payment_method`, `payment_status`, `status`, `created_at`, `transaction_code`) VALUES
(1, NULL, NULL, NULL, 135000.00, 'payment', 'bank_transfer', 'pending', 'pending', '2025-08-16 01:00:00', 'TXN_202508160001'),
(2, NULL, NULL, NULL, 150000.00, 'payment', 'cash', 'received', 'completed', '2025-08-16 01:05:00', 'TXN_202508160002'),
(3, NULL, NULL, NULL, 108000.00, 'payment', 'online', 'received', 'pending', '2025-08-16 01:10:00', 'TXN_202508160003'),
(4, NULL, NULL, NULL, 150000.00, 'payment', 'cash', 'received', 'completed', '2025-08-16 01:15:00', 'TXN_202508160004'),
(5, NULL, NULL, NULL, 2800000.00, 'payment', 'bank_transfer', 'pending', 'pending', '2025-08-16 01:20:00', 'TXN_202508160005'),
(6, NULL, NULL, NULL, 1500000.00, 'payment', 'online', 'received', 'completed', '2025-08-16 01:25:00', 'TXN_202508160006'),
(7, NULL, NULL, NULL, 600000.00, 'payment', 'bank_transfer', 'pending', 'pending', '2025-08-16 01:30:00', 'TXN_202508160007'),
(8, NULL, NULL, NULL, 450000.00, 'payment', 'cash', 'received', 'completed', '2025-08-16 01:35:00', 'TXN_202508160008'),
(9, NULL, NULL, NULL, 252000.00, 'payment', 'online', 'received', 'pending', '2025-08-16 01:40:00', 'TXN_202508160009'),
(10, NULL, NULL, NULL, 160000.00, 'payment', 'cash', 'received', 'completed', '2025-08-16 01:45:00', 'TXN_202508160010');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL COMMENT 'Mã người dùng',
  `username` varchar(50) NOT NULL COMMENT 'Tên đăng nhập (duy nhất)',
  `password` varchar(255) NOT NULL COMMENT 'Mật khẩu (mã hóa)',
  `full_name` varchar(100) NOT NULL COMMENT 'Họ và tên',
  `phone` varchar(15) NOT NULL COMMENT 'Số điện thoại',
  `email` varchar(100) DEFAULT NULL COMMENT 'Email',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo',
  `role` enum('client','admin') DEFAULT 'client' COMMENT 'Vai trò: client (khách hàng), admin (quản trị viên)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin người dùng';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `phone`, `email`, `created_at`, `role`) VALUES
(11, 'sunny', '$2y$10$fsdqxx5ZQTpWEhyzkej0z.YxC9X0j.6cFR3Ou03G1Dduqx6oxoo2O', 'Trần Phương Thùy', '0914090876', 'phuongthuy091203@gmail.com', '2025-08-18 11:31:41', 'admin'),
(12, 'sunny1', '$2y$10$A16Ghm4FAg1JP5tzew2FZ.8zK.8oqNCkV54NgKuxBeAIuDxPeB/52', 'Trần Phương Thùy', '0914090876', 'phuongthuy091209@gmail.com', '2025-08-18 11:33:54', 'client'),
(13, 'sunny2', '$2y$10$rHEKvhtIq.dpe1I1RGeDluiXXPaQMNzOuQezqqriWfiEQttNaptlG', 'sunny2', '0914090876', 'phuongthuy091206@gmail.com', '2025-09-20 14:56:49', 'client');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `court_id` (`court_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courts`
--
ALTER TABLE `courts`
  ADD PRIMARY KEY (`court_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `forum_categories`
--
ALTER TABLE `forum_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `forum_threads`
--
ALTER TABLE `forum_threads`
  ADD PRIMARY KEY (`thread_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `livestreams`
--
ALTER TABLE `livestreams`
  ADD PRIMARY KEY (`livestream_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `shop_info`
--
ALTER TABLE `shop_info`
  ADD PRIMARY KEY (`shop_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã đặt sân', AUTO_INCREMENT=5301;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `chat_history`
--
ALTER TABLE `chat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `courts`
--
ALTER TABLE `courts`
  MODIFY `court_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã sân', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã sự kiện', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã đăng ký', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `forum_categories`
--
ALTER TABLE `forum_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã danh mục', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã bài đăng', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `forum_threads`
--
ALTER TABLE `forum_threads`
  MODIFY `thread_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã chủ đề', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `livestreams`
--
ALTER TABLE `livestreams`
  MODIFY `livestream_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã livestream', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã thông báo', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã đơn hàng', AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã chi tiết đơn hàng', AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã sản phẩm', AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã danh mục sản phẩm', AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã hình ảnh', AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã biến thể', AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã cấu hình', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `shop_info`
--
ALTER TABLE `shop_info`
  MODIFY `shop_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã shop', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã giao dịch', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã người dùng', AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`court_id`) REFERENCES `courts` (`court_id`) ON DELETE SET NULL;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `forum_threads` (`thread_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `forum_threads`
--
ALTER TABLE `forum_threads`
  ADD CONSTRAINT `forum_threads_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `forum_categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `forum_threads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `livestreams`
--
ALTER TABLE `livestreams`
  ADD CONSTRAINT `livestreams_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
