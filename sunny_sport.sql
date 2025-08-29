-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 29, 2025 at 12:18 PM
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
(16, 11, 1, '2025-08-29', '06:00:00', '06:30:00', 'ondelivery', 75000.00, 0.00, 'pending', '2025-08-27 07:59:15', 'Minh Hào', '0927271827');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_history`
--

CREATE TABLE `chat_history` (
  `id` int(11) NOT NULL COMMENT 'Mã tin nhắn',
  `user_id` int(11) DEFAULT NULL COMMENT 'Mã người dùng (NULL nếu là bot)',
  `conversation_id` int(11) DEFAULT NULL COMMENT 'Mã cuộc trò chuyện',
  `role` enum('bot','client','admin') DEFAULT NULL COMMENT 'Vai trò: bot, client, admin',
  `content` text DEFAULT NULL COMMENT 'Nội dung tin nhắn',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Thời gian gửi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu lịch sử trò chuyện của chatbot';

--
-- Dumping data for table `chat_history`
--

INSERT INTO `chat_history` (`id`, `user_id`, `conversation_id`, `role`, `content`, `created_at`) VALUES
(1, NULL, 1, 'client', 'Chào bot, sân nào còn trống chiều nay?', '2025-08-16 09:00:00'),
(2, NULL, 1, 'bot', 'Chào bạn! Sân 2 và Sân 4 còn trống từ 15:00-17:00. Bạn muốn đặt không?', '2025-08-16 09:00:05'),
(3, NULL, 2, 'client', 'Tôi muốn mua vợt Yonex, còn hàng không?', '2025-08-16 09:05:00'),
(4, NULL, 2, 'bot', 'Vợt Yonex Astrox 99 Pro còn 30 cây. Bạn muốn đặt hàng không?', '2025-08-16 09:05:05'),
(5, NULL, 3, 'client', 'Giải đấu tháng 9 đăng ký ở đâu?', '2025-08-16 09:10:00'),
(6, NULL, 3, 'bot', 'Bạn có thể đăng ký tại trang Sự kiện trên website. Phí 200,000 VNĐ.', '2025-08-16 09:10:05'),
(7, NULL, 4, 'client', 'Kiểm tra trạng thái đặt sân ngày 20/8 của tôi.', '2025-08-16 09:15:00'),
(8, NULL, 4, 'bot', 'Đặt sân Sân 1 ngày 20/8 từ 6:00-7:00 đang chờ xác nhận thanh toán.', '2025-08-16 09:15:05'),
(9, NULL, 5, 'admin', 'Kiểm tra danh sách đặt sân hôm nay.', '2025-08-16 09:20:00'),
(10, NULL, 5, 'bot', 'Hôm nay có 3 lượt đặt sân: Sân 1 (6:00-7:00), Sân 2 (7:00-8:00), Sân 3 (8:00-9:00).', '2025-08-16 09:20:05');

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
(5, 'Sân 5', 'Sàn đa năng, có thể sử dụng cho nhiều hoạt động thể thao', 140000.00),
(6, 'Sân 6', 'Mới nâng cấp với trang thiết bị hiện đại', 160000.00),
(7, 'Sân 7', 'Vị trí thuận tiện gần khu vực nghỉ ngơi', 150000.00),
(8, 'Sân 8', 'Tiêu chuẩn thi đấu chuyên nghiệp, phù hợp tổ chức giải', 180000.00),
(9, 'Sân 9', 'Có khu vực ghế chờ và tiện nghi đi kèm', 150000.00),
(10, 'Sân 10', 'Phù hợp cho luyện tập hằng ngày', 150000.00);

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
(12, 11, NULL, NULL, NULL, NULL, 7000000.00, 'pending', 'cod', '2025-08-20 01:35:31'),
(13, 11, NULL, NULL, NULL, NULL, 2500000.00, 'pending', 'cod', '2025-08-20 02:08:29'),
(14, 11, NULL, NULL, NULL, NULL, 2500000.00, 'pending', 'cod', '2025-08-22 04:38:30'),
(15, 11, 'Trần Phương Thùyyy', '12345 nguyen van thiet, 13753, 359, 36', '09140908700', '', 2500000.00, 'pending', 'cod', '2025-08-22 05:48:53'),
(16, 11, 'Trần Phương Thùy', '54332, 1339, 43, 4', '0914090876', 'aa', 1500000.00, 'pending', 'cod', '2025-08-22 09:40:12'),
(17, 11, 'Trần Phương Thùy', '54332, 1339, 43, 4', '0914090876', 'aa', 1500000.00, 'pending', 'cod', '2025-08-22 09:42:22'),
(18, 11, 'Trần Phương Thùy', 'aaa, 1, 1, 1', '0914090876', '', 100000.00, 'pending', 'cod', '2025-08-22 09:45:37'),
(19, 11, 'Trần Phương Thùy', 'tằn tân, Xã Mường Bằng, Huyện Mai Sơn, Tỉnh Sơn La', '0914090876', '', 1500000.00, 'pending', 'cod', '2025-08-22 10:09:50'),
(20, 11, 'Trần Phương Thùy', '2818 hjo gom, Phường Ngọc Châu, Thành phố Hải Dương, Tỉnh Hải Dương', '0914090876', '1111', 1500000.00, 'pending', 'cod', '2025-08-23 15:18:40'),
(21, 11, 'Trần Phương Thùy', '111 dsfa áaa, Phường Quang Trung, Thành phố Hà Giang, Tỉnh Hà Giang', '0914090876', '', 2200000.00, 'pending', 'cod', '2025-08-23 15:23:27'),
(22, 11, 'Trần Phương Thùy', '12345 nguyen van thiet, Phường Tân Tiến, Thành phố Bắc Giang, Tỉnh Bắc Giang', '0914090876', '', 300000.00, 'pending', 'cod', '2025-08-23 15:29:51'),
(23, 11, 'Trần Phương Thùy', 'tran van on, Xã Vĩnh Phương, Thành phố Nha Trang, Tỉnh Khánh Hòa', '0914090876', '', 800000.00, 'pending', 'card', '2025-08-23 15:31:25'),
(24, 11, 'Phan Minh Thắng', 'đối diện cà phê lê vy 2, Phường 9, Thành phố Vĩnh Long, Tỉnh Vĩnh Long', '0834029049', '', 700000.00, 'pending', 'cod', '2025-08-29 09:08:26');

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
(12, 12, 1, 1, 2500000.00, NULL, NULL, NULL),
(13, 12, 2, 3, 1500000.00, NULL, NULL, NULL),
(14, 13, 1, 1, 2500000.00, NULL, NULL, NULL),
(15, 14, 1, 1, 2500000.00, NULL, NULL, NULL),
(16, 15, 1, 1, 2500000.00, NULL, NULL, NULL),
(17, 16, 2, 1, 1500000.00, NULL, NULL, NULL),
(18, 17, 2, 1, 1500000.00, NULL, NULL, NULL),
(19, 18, 5, 1, 100000.00, NULL, NULL, NULL),
(20, 19, 2, 1, 1500000.00, NULL, NULL, NULL),
(21, 20, 2, 1, 1500000.00, NULL, NULL, NULL),
(22, 21, 6, 1, 2200000.00, NULL, NULL, NULL),
(23, 22, 3, 1, 300000.00, NULL, NULL, NULL),
(24, 23, 9, 1, 800000.00, NULL, NULL, NULL),
(25, 24, 8, 2, 350000.00, NULL, NULL, NULL);

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
(10, 'Dây đan lưới Yonex BG65', 7, 'Dây đan lưới bền, độ căng tốt', 150000.00, 60, '2025-08-16 01:45:00');

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
(18, 7, 'asics-skyelite-side.webp', 'Giày Asics Sky Elite FF', 0, '2025-08-15 18:30:00');

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
(12, 8, 'XL', 'Grey', 10, 350000.00, '2025-08-29 07:53:02');

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
(11, 'sunny', '$2y$10$fsdqxx5ZQTpWEhyzkej0z.YxC9X0j.6cFR3Ou03G1Dduqx6oxoo2O', 'Trần Phương Thùy', '0914090876', 'phuongthuy091203@gmail.com', '2025-08-18 11:31:41', 'client'),
(12, 'sunny1', '$2y$10$A16Ghm4FAg1JP5tzew2FZ.8zK.8oqNCkV54NgKuxBeAIuDxPeB/52', 'Trần Phương Thùy', '0914090876', 'phuongthuy091209@gmail.com', '2025-08-18 11:33:54', 'client');

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_conversation_id` (`conversation_id`);

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
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã đặt sân', AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chat_history`
--
ALTER TABLE `chat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã tin nhắn', AUTO_INCREMENT=11;

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
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã đơn hàng', AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã chi tiết đơn hàng', AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã sản phẩm', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã danh mục sản phẩm', AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã hình ảnh', AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã biến thể', AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã cấu hình', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã giao dịch', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã người dùng', AUTO_INCREMENT=13;

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
-- Constraints for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD CONSTRAINT `chat_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

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
