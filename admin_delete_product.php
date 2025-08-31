<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

$product_id = $_GET['id'] ?? 0;

if ($product_id) {
    // Kiểm tra sản phẩm có tồn tại không
    $stmt = $conn->prepare("SELECT product_name FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        header('Location: admin.php?section=products&error=not_found');
        exit();
    }
    
    // Kiểm tra sản phẩm có trong đơn hàng không
    $check_orders = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
    $check_orders->bind_param("i", $product_id);
    $check_orders->execute();
    $orders_count = $check_orders->get_result()->fetch_assoc()['count'];
    
    if ($orders_count > 0) {
        // Nếu có trong đơn hàng, chỉ cập nhật stock = 0 thay vì xóa
        $stmt = $conn->prepare("UPDATE products SET stock = 0 WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            header('Location: admin.php?section=products&success=stock_zero');
        } else {
            header('Location: admin.php?section=products&error=update_failed');
        }
        exit();
    }
    
    // Bắt đầu transaction để xóa an toàn
    $conn->begin_transaction();
    
    try {
        // Xóa hình ảnh sản phẩm
        $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Xóa biến thể sản phẩm
        $stmt = $conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Xóa sản phẩm
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        header('Location: admin.php?section=products&success=deleted');
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $conn->rollback();
        header('Location: admin.php?section=products&error=delete_failed');
    }
} else {
    header('Location: admin.php?section=products');
}

exit();
?> 