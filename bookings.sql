-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2025 at 02:07 PM
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
(28, 12, 1, '2025-09-24', '20:00:00', '21:30:00', 'prepaid', 225000.00, 0.00, 'cancelled', '2025-09-19 01:30:00', 'Trần Hải Yến', '0933221100');

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã đặt sân', AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`court_id`) REFERENCES `courts` (`court_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
