<?php
session_start();
include 'config/database.php';

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    echo "Bạn chưa đăng nhập!";
    exit;
}

// Lấy sản phẩm trong giỏ
$stmt = $conn->prepare("SELECT ci.product_id, p.product_name, p.price, ci.quantity
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart = [];
$total = 0;
while ($item = $result->fetch_assoc()) {
    $cart[] = $item;
    $total += $item['price'] * $item['quantity'];
}

if (empty($cart)) {
    echo "Giỏ hàng trống!";
    exit;
}

// Tạo đơn hàng
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("id", $user_id, $total);
$stmt->execute();
$order_id = $conn->insert_id;

// Thêm chi tiết đơn hàng
foreach ($cart as $item) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
    $stmt->execute();
}

// Xóa giỏ hàng
$stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo "Đặt hàng thành công! Mã đơn hàng: #" . $order_id;