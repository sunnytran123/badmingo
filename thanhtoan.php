<?php
ob_start(); // Bắt đầu output buffering để tránh lỗi header
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit();
}
include 'config/database.php';

// Xử lý logic trước khi xuất bất kỳ HTML nào
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    die("Bạn chưa đăng nhập!");
}

// Xử lý success: Redirect ngay về t.php
if (isset($_GET['success'])) {
    header("Location: shop_list.php");
    exit;
}

// Kiểm tra nếu là mua ngay
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
$variant_id = isset($_GET['variant_id']) ? intval($_GET['variant_id']) : 0;

// Kiểm tra nếu là thanh toán từ giỏ hàng
$selected_ids = isset($_GET['selected_ids']) ? explode(',', $_GET['selected_ids']) : [];
$selected_ids = array_values(array_filter(array_map('intval', $selected_ids), function($v){ return $v > 0; }));

// Lấy sản phẩm
$cart = [];
$total = 0;

if ($product_id > 0) {
    // Xử lý "Mua ngay": ưu tiên biến thể nếu có
    // Kiểm tra sản phẩm có biến thể không
    $hasVariants = false;
    $checkStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM product_variants WHERE product_id = ?");
    if ($checkStmt) {
        $checkStmt->bind_param("i", $product_id);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();
        $hasVariants = ($checkRes && ($row = $checkRes->fetch_assoc()) && intval($row['cnt']) > 0);
    }

    if ($hasVariants) {
        if ($variant_id <= 0) {
            die("Vui lòng chọn màu và size trước khi thanh toán!");
        }
        // Lấy biến thể
        $vStmt = $conn->prepare("SELECT pv.variant_id, pv.size, pv.color, pv.stock, COALESCE(pv.price, p.price) AS price, pv.product_id, p.product_name
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.product_id
            WHERE pv.product_id = ? AND pv.variant_id = ?");
        if (!$vStmt) die("Lỗi chuẩn bị truy vấn biến thể: " . $conn->error);
        $vStmt->bind_param("ii", $product_id, $variant_id);
        $vStmt->execute();
        $vRes = $vStmt->get_result();
        $variant = $vRes->fetch_assoc();
        if (!$variant) {
            die("Biến thể không tồn tại!");
        }
        if (intval($variant['stock']) < $quantity) {
            die("Biến thể không đủ hàng!");
        }
        $cart[] = [
            'product_id' => $variant['product_id'],
            'product_name' => $variant['product_name'],
            'price' => floatval($variant['price']),
            'quantity' => $quantity,
            'stock' => intval($variant['stock']),
            'variant_id' => intval($variant['variant_id']),
            'size' => $variant['size'],
            'color' => $variant['color']
        ];
        $total = floatval($variant['price']) * $quantity;
    } else {
        // Không có biến thể: lấy trực tiếp từ products
        $stmt = $conn->prepare("SELECT product_id, product_name, price, stock FROM products WHERE product_id = ?");
        if (!$stmt) die("Lỗi chuẩn bị truy vấn sản phẩm: " . $conn->error);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        if ($item && $item['stock'] >= $quantity) {
            $cart[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'price' => $item['price'],
                'quantity' => $quantity,
                'stock' => $item['stock']
            ];
            $total = $item['price'] * $quantity;
        } else {
            die("Sản phẩm không đủ hàng hoặc không tồn tại!");
        }
    }
} elseif (!empty($selected_ids)) {
    // Xử lý giỏ hàng theo product_id đã chọn, kèm biến thể nếu có
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $sql = "SELECT ci.product_id, ci.variant_id, ci.size, ci.color,
                   COALESCE(pv.price, p.price) AS price,
                   COALESCE(pv.stock, p.stock) AS stock,
                   p.product_name, ci.quantity
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.variant_id AND pv.product_id = p.product_id
            WHERE ci.user_id = ? AND ci.product_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Lỗi chuẩn bị truy vấn giỏ hàng: " . $conn->error);
    $params = array_merge([$user_id], $selected_ids);
    $stmt->bind_param(str_repeat('i', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $allSufficient = true;
    while ($item = $result->fetch_assoc()) {
        if (intval($item['stock']) >= intval($item['quantity'])) {
            $cart[] = $item;
            $total += floatval($item['price']) * intval($item['quantity']);
        } else {
            $allSufficient = false;
        }
    }
    if (!$allSufficient || empty($cart)) {
        die("Một hoặc nhiều sản phẩm không đủ hàng hoặc không tìm thấy sản phẩm đã chọn!");
    }
}

if (empty($cart)) {
    die("Giỏ hàng trống hoặc không có sản phẩm nào được chọn!");
}

// Lấy thông tin user để preload
$stmt = $conn->prepare("SELECT full_name, phone FROM users WHERE user_id = ?");
if (!$stmt) die("Lỗi chuẩn bị truy vấn thông tin người dùng: " . $conn->error);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();
$preload_name = $user_info['full_name'] ?? '';
$preload_phone = $user_info['phone'] ?? '';

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_name = $_POST['recipient_name'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $province = $_POST['province_name'] ?? '';
    $district = $_POST['district_name'] ?? '';
    $ward = $_POST['ward_name'] ?? '';
    $address_detail = $_POST['address_detail'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cod';

    $shipping_address = trim("$address_detail, $ward, $district, $province");

    // Tính phí ship cho validation
    $shipping_fee = ($total >= 500000) ? 0 : 30000;
    $grand_total = $total + $shipping_fee;

    // Validate dữ liệu trước khi bind
    if (empty($recipient_name) || empty($phone_number) || empty($province) || empty($district) || empty($ward) || empty($address_detail)) {
        $error_message = "Vui lòng điền đầy đủ thông tin giao hàng!";
    } elseif (!is_numeric($total) || $total <= 0) {
        $error_message = "Tổng tiền không hợp lệ!";
    } elseif (!in_array($payment_method, ['cod', 'card'])) {
        $error_message = "Hình thức thanh toán không hợp lệ!";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, recipient_name, shipping_address, phone_number, notes, total_amount, status, payment_method) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
            if (!$stmt) die("Lỗi chuẩn bị truy vấn tạo đơn hàng: " . $conn->error);
            $stmt->bind_param("issssds", $user_id, $recipient_name, $shipping_address, $phone_number, $notes, $grand_total, $payment_method);
            $stmt->execute();
            $order_id = $conn->insert_id;

            // Kiểm tra cột mở rộng cho order_items
            $hasVariantCol = false; $hasSizeCol = false; $hasColorCol = false;
            if ($res = $conn->query("SHOW COLUMNS FROM order_items LIKE 'variant_id'")) { $hasVariantCol = $res->num_rows > 0; }
            if ($res = $conn->query("SHOW COLUMNS FROM order_items LIKE 'size'")) { $hasSizeCol = $res->num_rows > 0; }
            if ($res = $conn->query("SHOW COLUMNS FROM order_items LIKE 'color'")) { $hasColorCol = $res->num_rows > 0; }

            foreach ($cart as $item) {
                if ($hasVariantCol || $hasSizeCol || $hasColorCol) {
                    // Cố gắng chèn với các cột mở rộng nếu tồn tại
                    $columns = "order_id, product_id, quantity, price";
                    $placeholders = "?, ?, ?, ?";
                    $types = "iiid";
                    $values = [$order_id, intval($item['product_id']), intval($item['quantity']), floatval($item['price'])];
                    if ($hasVariantCol) { $columns = "order_id, product_id, variant_id, quantity, price"; $placeholders = "?, ?, ?, ?, ?"; $types = "iiiid"; array_splice($values, 2, 0, [intval($item['variant_id'] ?? 0)]); }
                    // size/color không ảnh hưởng bind khi không tồn tại; nếu muốn lưu, cần cột tồn tại
                    if ($hasSizeCol && $hasColorCol && $hasVariantCol) {
                        // order_id, product_id, variant_id, size, color, quantity, price
                        $columns = "order_id, product_id, variant_id, size, color, quantity, price";
                        $placeholders = "?, ?, ?, ?, ?, ?, ?";
                        $types = "iiissid";
                        $values = [$order_id, intval($item['product_id']), intval($item['variant_id'] ?? 0), strval($item['size'] ?? ''), strval($item['color'] ?? ''), intval($item['quantity']), floatval($item['price'])];
                    }
                    $sql = "INSERT INTO order_items ($columns) VALUES ($placeholders)";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) die("Lỗi chuẩn bị chi tiết đơn hàng (mở rộng): " . $conn->error);
                    $stmt->bind_param($types, ...$values);
                    $stmt->execute();
                } else {
                    // Schema cũ
                    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    if (!$stmt) die("Lỗi chuẩn bị truy vấn chi tiết đơn hàng: " . $conn->error);
                    $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                    $stmt->execute();
                }
            }

            // Xóa giỏ hàng chỉ nếu thanh toán từ giỏ
            if (!empty($selected_ids)) {
                $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
                $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id IN ($placeholders)");
                if (!$stmt) die("Lỗi chuẩn bị truy vấn xóa giỏ hàng: " . $conn->error);
                $params = array_merge([$user_id], $selected_ids);
                $stmt->bind_param(str_repeat('i', count($params)), ...$params);
                $stmt->execute();
            }

            // Redirect với thông báo thành công
            header("Location: thanhtoan.php?success=1&order_id=$order_id");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error_message = "Lỗi khi tạo đơn hàng: " . $e->getMessage();
        }
    }
}

// Sau khi xử lý logic, include header và xuất HTML
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán - Sunny Sport</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f8f9fa; }
        .checkout-container {
            max-width: 900px;
            margin: 40px auto;
            display: flex;
            gap: 30px;
            flex-direction: row;
            align-items: flex-start;
        }
        .cart-summary, .checkout-form {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 30px;
            width: 100%;
        }
        .cart-summary {
            max-width: 400px;
            min-width: 320px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 30px 25px;
            width: 100%;
        }
        .cart-summary h2 {
            font-size: 24px;
            color: #222;
            margin-bottom: 25px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-align: left;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        .cart-summary ul {
            list-style: none;
            padding: 0;
            margin: 0 0 25px 0;
        }
        .cart-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        .cart-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .cart-item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .cart-item-name {
            font-weight: bold;
            font-size: 18px;
            color: #222;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .cart-item-attr {
            font-size: 15px;
            color: #555;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .cart-summary-total {
            border-top: 2px solid #e3e7ed;
            padding-top: 20px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 10px;
        }
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            margin-bottom: 12px;
            color: #222;
            padding: 5px 0;
        }
        .cart-summary-row span:last-child {
            font-weight: 500;
        }
        .cart-summary-grand {
            font-size: 22px;
            font-weight: bold;
            color: #28a745;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #28a745;
        }
        .cart-summary-grand span:first-child {
            color: #28a745;
        }
        .cart-summary-grand span:last-child {
            color: #28a745;
        }
        .checkout-form {
            flex: 1;
            min-width: 320px;
        }
        .form-section h3 {
            font-size: 18px;
            color: #007bff;
            margin-bottom: 15px;
            font-weight: bold;
        }
        label {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 6px;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 15px;
            box-sizing: border-box;
            background: #f8f9fa;
            transition: border 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #007bff;
            outline: none;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button[type="submit"] {
            background: #28a745;
            color: #fff;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s, transform 0.2s;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(40,167,69,0.08);
        }
        button[type="submit"]:hover {
            background: #1e7e34;
            transform: translateY(-2px);
        }
        .error-message {
            background: #ffeaea;
            color: #dc3545;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #dc3545;
        }
        .payment-method {
            margin-bottom: 20px;
        }
        .payment-method h3 {
            font-size: 16px;
            color: #007bff;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .payment-method label {
            display: inline-flex;
            align-items: center;
            margin-right: 25px;
            font-size: 14px;
            color: #333;
            cursor: pointer;
            gap: 6px;
        }
        .payment-method input[type="radio"] {
            accent-color: #28a745;
            margin-right: 6px;
        }
        .payment-method-summary {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e3e7ed;
        }
        .payment-method-summary h3 {
            font-size: 18px;
            color: #007bff;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .payment-method-summary .payment-options {
            display: flex;
            flex-direction: row;
            gap: 15px;
            flex-wrap: wrap;
        }
        .payment-method-summary label {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            font-size: 14px;
            color: #333;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background-color 0.2s;
            white-space: nowrap;
            flex: 0 0 auto;
            line-height: 1;
        }
        .payment-method-summary label:hover {
            background-color: #f8f9fa;
        }
        .payment-method-summary input[type="radio"] {
            accent-color: #28a745;
            margin-right: 8px;
            transform: scale(1.1);
            vertical-align: middle;
            margin-top: 0;
            margin-bottom: 0;
        }
        @media (max-width: 900px) {
            .checkout-container { flex-direction: column; gap: 20px; }
            .cart-summary, .checkout-form { max-width: 100%; min-width: 0; }
        }
        @media (max-width: 600px) {
            .checkout-container { margin: 10px; }
            .cart-summary, .checkout-form { padding: 15px; }
            button[type="submit"] { font-size: 15px; padding: 12px; }
        }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
    </style>
</head>
<body>
    <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
        <div style="max-width: 900px; margin: 40px auto; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 10px; padding: 20px; text-align: center;">
            <h2 style="color: #155724; margin-bottom: 10px;">✅ Đặt hàng thành công!</h2>
            <p style="color: #155724; font-size: 16px; margin-bottom: 15px;">
                Cảm ơn bạn đã đặt hàng. Mã đơn hàng của bạn là: <strong>#<?php echo isset($_GET['order_id']) ? $_GET['order_id'] : 'N/A'; ?></strong>
            </p>
            <p style="color: #155724; font-size: 14px;">
                Chúng tôi sẽ liên hệ với bạn sớm nhất để xác nhận đơn hàng.
            </p>
            <div style="margin-top: 20px;">
                <a href="shop.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Tiếp tục mua sắm</a>
                <a href="index.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Về trang chủ</a>
            </div>
        </div>
    <?php else: ?>
    <div class="checkout-container">
        <div class="cart-summary">
            <h2>Đơn hàng của bạn</h2>
            <ul>
                <?php foreach ($cart as $item): 
                    // Lấy hình ảnh chính của sản phẩm
                    $imgStmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
                    $imgStmt->bind_param("i", $item['product_id']);
                    $imgStmt->execute();
                    $imgResult = $imgStmt->get_result();
                    $imgRow = $imgResult->fetch_assoc();
                    $imgSrc = !empty($imgRow['image_url']) ? 'images/' . $imgRow['image_url'] : 'images/sport1.webp';
                ?>
                <li class="cart-item">
                    <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <div class="cart-item-info">
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <?php if (!empty($item['color']) || !empty($item['size'])): ?>
                        <div class="cart-item-attr">Màu: <?php echo htmlspecialchars($item['color'] ?? '-'); ?> | Size: <?php echo htmlspecialchars($item['size'] ?? '-'); ?></div>
                        <?php endif; ?>
                        <div class="cart-item-attr">Số lượng: <?php echo $item['quantity']; ?></div>
                        <div class="cart-item-attr">Đơn giá: <?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php
            // Tính phí ship
            $shipping_fee = ($total >= 500000) ? 0 : 30000;
            $grand_total = $total + $shipping_fee;
            ?>
            <div class="cart-summary-total">
                <div class="cart-summary-row">
                    <span>Tạm tính</span>
                    <span><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                </div>
                <div class="cart-summary-row">
                    <span>Phí vận chuyển</span>
                    <?php if ($shipping_fee == 0): ?>
                        <span style="color: #28a745; font-weight: bold;">Miễn phí</span>
                    <?php else: ?>
                        <span><?php echo number_format($shipping_fee, 0, ',', '.'); ?> VNĐ</span>
                    <?php endif; ?>
                </div>
                <?php if ($total < 500000): ?>
                <div class="cart-summary-row" style="font-size: 14px; color: #666; font-style: italic;">
                    <span>Mua thêm <?php echo number_format(500000 - $total, 0, ',', '.'); ?> VNĐ để được miễn phí ship</span>
                </div>
                <?php endif; ?>
                <div class="cart-summary-row cart-summary-grand">
                    <span>Tổng cộng</span>
                    <span><?php echo number_format($grand_total, 0, ',', '.'); ?> VNĐ</span>
                </div>
            </div>
        </div>
        
        <form method="POST" class="checkout-form">
            <div class="form-section">
                <h3>Thông tin giao hàng</h3>
                <?php if (isset($error_message)): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
                <label for="recipient_name">Tên người nhận:</label>
                <input type="text" id="recipient_name" name="recipient_name" value="<?php echo htmlspecialchars($preload_name); ?>" required>
                
                <label for="phone_number">Số điện thoại:</label>
                <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($preload_phone); ?>" required pattern="[0-9]{10,11}">
                
                <label for="province">Tỉnh/Thành phố:</label>
                <select id="province" name="province_name" required>
                    <option value="">Chọn tỉnh/thành</option>
                </select>
                
                <label for="district">Quận/Huyện:</label>
                <select id="district" name="district_name" required disabled>
                    <option value="">Chọn quận/huyện</option>
                </select>
                
                <label for="ward">Phường/Xã:</label>
                <select id="ward" name="ward_name" required disabled>
                    <option value="">Chọn phường/xã</option>
                </select>
                
                <label for="address_detail">Địa chỉ chi tiết (số nhà, đường):</label>
                <input type="text" id="address_detail" name="address_detail" required>
                
                <label for="notes">Ghi chú (tùy chọn):</label>
                <textarea id="notes" name="notes"></textarea>
            </div>
            <div class="payment-method-summary">
                <h3>Hình thức thanh toán</h3>
                <div class="payment-options">
                    <label>
                        <input type="radio" name="payment_method" value="cod" checked> Thanh toán khi nhận hàng
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="card"> Thanh toán bằng thẻ
                    </label>
                </div>
            </div>
            <button type="submit">Xác Nhận Và Đặt Hàng</button>
        </form>
    </div>
    <?php endif; ?>

    <script>
        // Script API địa chỉ từ provinces.open-api.vn
        const provinceSelect = document.getElementById('province');
        const districtSelect = document.getElementById('district');
        const wardSelect = document.getElementById('ward');

        fetch('https://provinces.open-api.vn/api/p/')
            .then(res => res.json())
            .then(data => {
                provinceSelect.innerHTML = '<option value="">Chọn tỉnh/thành</option>' + 
                    data.map(p => `<option value="${p.name}">${p.name}</option>`).join('');
            })
            .catch(error => {
                console.error('Lỗi khi lấy tỉnh/thành:', error);
                document.querySelector('.checkout-form').insertAdjacentHTML('beforeend', '<p class="error-message">Không thể tải danh sách tỉnh/thành. Vui lòng thử lại.</p>');
            });

        provinceSelect.addEventListener('change', function() {
            const provinceName = this.value;
            districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
            wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
            districtSelect.disabled = true;
            wardSelect.disabled = true;
            if (!provinceName) return;
            fetch('https://provinces.open-api.vn/api/p/')
                .then(res => res.json())
                .then(data => {
                    const province = data.find(p => p.name === provinceName);
                    if (!province) return;
                    return fetch(`https://provinces.open-api.vn/api/p/${province.code}?depth=2`);
                })
                .then(res => res.json())
                .then(data => {
                    districtSelect.disabled = false;
                    districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>' + 
                        data.districts.map(d => `<option value="${d.name}">${d.name}</option>`).join('');
                })
                .catch(error => {
                    console.error('Lỗi khi lấy quận/huyện:', error);
                    document.querySelector('.checkout-form').insertAdjacentHTML('beforeend', '<p class="error-message">Không thể tải danh sách quận/huyện. Vui lòng thử lại.</p>');
                });
        });

        districtSelect.addEventListener('change', function() {
            const districtName = this.value;
            wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
            wardSelect.disabled = true;
            if (!districtName) return;
            fetch('https://provinces.open-api.vn/api/d/')
                .then(res => res.json())
                .then(data => {
                    const district = data.find(d => d.name === districtName);
                    if (!district) return;
                    return fetch(`https://provinces.open-api.vn/api/d/${district.code}?depth=2`);
                })
                .then(res => res.json())
                .then(data => {
                    wardSelect.disabled = false;
                    wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>' + 
                        data.wards.map(w => `<option value="${w.name}">${w.name}</option>`).join('');
                })
                .catch(error => {
                    console.error('Lỗi khi lấy phường/xã:', error);
                    document.querySelector('.checkout-form').insertAdjacentHTML('beforeend', '<p class="error-message">Không thể tải danh sách phường/xã. Vui lòng thử lại.</p>');
                });
        });
    </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>
<?php ob_end_flush(); // Kết thúc output buffering ?>