<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

$user_id = $_GET['id'] ?? 0;

if ($user_id) {
    // Kiểm tra không xóa chính mình
    if ($user_id == $_SESSION['user_id']) {
        header('Location: admin.php?section=users&error=self_delete');
        exit();
    }
    
    // Kiểm tra user có tồn tại không
    $stmt = $conn->prepare("SELECT username, full_name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        header('Location: admin.php?section=users&error=not_found');
        exit();
    }
    
    // Kiểm tra user có đơn hàng hoặc đặt sân không
    $check_orders = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
    $check_orders->bind_param("i", $user_id);
    $check_orders->execute();
    $orders_count = $check_orders->get_result()->fetch_assoc()['count'];
    
    $check_bookings = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
    $check_bookings->bind_param("i", $user_id);
    $check_bookings->execute();
    $bookings_count = $check_bookings->get_result()->fetch_assoc()['count'];
    
    if ($orders_count > 0 || $bookings_count > 0) {
        // Nếu có dữ liệu liên quan, chỉ cập nhật role thành inactive thay vì xóa
        $stmt = $conn->prepare("UPDATE users SET role = 'inactive' WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            header('Location: admin.php?section=users&success=deactivated');
        } else {
            header('Location: admin.php?section=users&error=update_failed');
        }
        exit();
    }
    
    // Xóa user nếu không có dữ liệu liên quan
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        header('Location: admin.php?section=users&success=deleted');
    } else {
        header('Location: admin.php?section=users&error=delete_failed');
    }
} else {
    header('Location: admin.php?section=users');
}

exit();
?> 