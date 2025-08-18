<?php
$servername = "localhost";   // nếu chạy XAMPP thì giữ nguyên
$username   = "root";        // user mặc định của XAMPP
$password   = "";            // thường để trống
$dbname     = "sunny_sport";   // đổi thành tên CSDL bạn đã tạo

// Kết nối MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
