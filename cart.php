<?php
session_start();
include 'config/database.php';

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập']);
    exit;
}

$action = $_POST['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($action == 'add') {
    $stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE user_id=? AND product_id=?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    if ($item) {
        $stmt2 = $conn->prepare("UPDATE cart_items SET quantity=quantity+? WHERE cart_item_id=?");
        $stmt2->bind_param("ii", $quantity, $item['cart_item_id']);
        $stmt2->execute();
    } else {
        $stmt2 = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt2->bind_param("iii", $user_id, $product_id, $quantity);
        $stmt2->execute();
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action == 'remove') {
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id=? AND product_id=?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

if ($action == 'update') {
    $stmt = $conn->prepare("UPDATE cart_items SET quantity=? WHERE user_id=? AND product_id=?");
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

if ($action == 'get') {
    $stmt = $conn->prepare("SELECT ci.product_id, p.product_name, p.price, ci.quantity
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);