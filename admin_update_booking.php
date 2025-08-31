<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if ($booking_id && in_array($status, ['pending', 'confirmed', 'cancelled'])) {
        // Cập nhật trạng thái đặt sân
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $status, $booking_id);
        
        if ($stmt->execute()) {
            // Nếu đặt sân được xác nhận, có thể gửi email thông báo
            if ($status === 'confirmed') {
                // Lấy thông tin đặt sân để gửi thông báo
                $stmt = $conn->prepare("SELECT b.*, u.email, u.full_name, c.court_name FROM bookings b JOIN users u ON b.user_id = u.user_id JOIN courts c ON b.court_id = c.court_id WHERE b.booking_id = ?");
                $stmt->bind_param("i", $booking_id);
                $stmt->execute();
                $booking = $stmt->get_result()->fetch_assoc();
                
                // TODO: Gửi email thông báo xác nhận đặt sân
                // sendBookingConfirmationEmail($booking['email'], $booking['full_name'], $booking);
            }
            
            header('Location: admin.php?section=bookings&success=updated');
        } else {
            header('Location: admin.php?section=bookings&error=update_failed');
        }
    } else {
        header('Location: admin.php?section=bookings&error=invalid_data');
    }
} else {
    header('Location: admin.php?section=bookings');
}

exit();
?> 