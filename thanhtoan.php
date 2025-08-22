<?php
session_start();
include 'config/database.php';

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    echo "Bạn chưa đăng nhập!";
    exit;
}

// Kiểm tra nếu là mua ngay
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

// Kiểm tra nếu là thanh toán từ giỏ hàng
$selected_ids = isset($_GET['selected_ids']) ? explode(',', $_GET['selected_ids']) : [];
$selected_ids = array_map('intval', $selected_ids);

// Lấy sản phẩm
$cart = [];
$total = 0;

if ($product_id > 0) {
    // Mua ngay: lấy thông tin sản phẩm từ product_id
    $stmt = $conn->prepare("SELECT product_id, product_name, price, stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    if ($item && $item['stock'] >= $quantity) {
        $cart[] = [
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'price' => $item['price'],
            'quantity' => $quantity
        ];
        $total = $item['price'] * $quantity;
    } else {
        echo "Sản phẩm không đủ hàng hoặc không tồn tại!";
        exit;
    }
} elseif (!empty($selected_ids)) {
    // Thanh toán từ giỏ hàng: lấy các sản phẩm được chọn
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $stmt = $conn->prepare("SELECT ci.product_id, p.product_name, p.price, ci.quantity, p.stock
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.user_id = ? AND ci.product_id IN ($placeholders)");
    $params = array_merge([$user_id], $selected_ids);
    $stmt->bind_param(str_repeat('i', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $allSufficient = true;
    while ($item = $result->fetch_assoc()) {
        if ($item['stock'] >= $item['quantity']) {
            $cart[] = $item;
            $total += $item['price'] * $item['quantity'];
        } else {
            $allSufficient = false;
            // Có thể thu thập lỗi cho từng sản phẩm, nhưng đơn giản thì dừng
        }
    }
    if (!$allSufficient || empty($cart)) {
        echo "Một hoặc nhiều sản phẩm không đủ hàng!";
        exit;
    }
}

if (empty($cart)) {
    echo "Giỏ hàng trống hoặc không có sản phẩm nào được chọn!";
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

// Xóa các sản phẩm đã chọn khỏi giỏ hàng (nếu không phải mua ngay)
if (!empty($selected_ids)) {
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id IN ($placeholders)");
    $params = array_merge([$user_id], $selected_ids);
    $stmt->bind_param(str_repeat('i', count($params)), ...$params);
    $stmt->execute();
}

echo "Đặt hàng thành công! Mã đơn hàng: #" . $order_id;
?>