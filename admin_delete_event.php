<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

$event_id = $_GET['id'] ?? 0;

if ($event_id) {
    // Kiểm tra sự kiện có tồn tại không
    $stmt = $conn->prepare("SELECT event_name FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    
    if (!$event) {
        header('Location: admin.php?section=events&error=not_found');
        exit();
    }
    
    // Kiểm tra sự kiện có người đăng ký không
    $check_registrations = $conn->prepare("SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?");
    $check_registrations->bind_param("i", $event_id);
    $check_registrations->execute();
    $registrations_count = $check_registrations->get_result()->fetch_assoc()['count'];
    
    if ($registrations_count > 0) {
        // Nếu có người đăng ký, chỉ cập nhật trạng thái thành cancelled thay vì xóa
        $stmt = $conn->prepare("UPDATE events SET status = 'cancelled' WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            header('Location: admin.php?section=events&success=cancelled');
        } else {
            header('Location: admin.php?section=events&error=update_failed');
        }
        exit();
    }
    
    // Bắt đầu transaction để xóa an toàn
    $conn->begin_transaction();
    
    try {
        // Xóa livestream liên quan
        $stmt = $conn->prepare("DELETE FROM livestreams WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        
        // Xóa sự kiện
        $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        header('Location: admin.php?section=events&success=deleted');
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $conn->rollback();
        header('Location: admin.php?section=events&error=delete_failed');
    }
} else {
    header('Location: admin.php?section=events');
}

exit();
?> 