<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if ($order_id && in_array($status, ['pending', 'completed', 'cancelled'])) {
        // Cập nhật trạng thái đơn hàng
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            // Nếu đơn hàng được hoàn thành, có thể gửi email thông báo
            if ($status === 'completed') {
                // Lấy thông tin đơn hàng để gửi thông báo
                $stmt = $conn->prepare("SELECT o.*, u.email, u.full_name FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $order = $stmt->get_result()->fetch_assoc();
                
                // TODO: Gửi email thông báo đơn hàng hoàn thành
                // sendOrderCompletionEmail($order['email'], $order['full_name'], $order_id);
            }
            
            header('Location: admin.php?section=orders&success=updated');
        } else {
            header('Location: admin.php?section=orders&error=update_failed');
        }
    } else {
        header('Location: admin.php?section=orders&error=invalid_data');
    }
} else {
    header('Location: admin.php?section=orders');
}

exit();
?> 