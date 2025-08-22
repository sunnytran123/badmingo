<?php
ob_start(); // Bắt đầu output buffering để tránh lỗi header
session_start();
include 'config/database.php';

// Xử lý logic trước khi xuất bất kỳ HTML nào
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    die("Bạn chưa đăng nhập!");
}

// Xử lý success: Redirect ngay về t.php
if (isset($_GET['success'])) {
    header("Location: t.php");
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
    // Xử lý "Mua ngay": Lấy trực tiếp từ products, không dùng cart_items
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
} elseif (!empty($selected_ids)) {
    // Xử lý giỏ hàng
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $stmt = $conn->prepare("SELECT ci.product_id, p.product_name, p.price, ci.quantity, p.stock
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.user_id = ? AND ci.product_id IN ($placeholders)");
    if (!$stmt) die("Lỗi chuẩn bị truy vấn giỏ hàng: " . $conn->error);
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
        }
    }
    if (!$allSufficient || empty($cart)) {
        die("Một hoặc nhiều sản phẩm không đủ hàng!");
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
            $stmt->bind_param("issssds", $user_id, $recipient_name, $shipping_address, $phone_number, $notes, $total, $payment_method);
            $stmt->execute();
            $order_id = $conn->insert_id;

            foreach ($cart as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                if (!$stmt) die("Lỗi chuẩn bị truy vấn chi tiết đơn hàng: " . $conn->error);
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
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
        /* Đồng bộ CSS từ t.php */
        body { font-family: Arial, sans-serif; margin: 40px; background: #f8f9fa; }
        .checkout-container { display: flex; flex-direction: column; gap: 20px; max-width: 800px; margin: auto; }
        .cart-summary { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .cart-summary h2 { font-size: 20px; color: #333; margin-bottom: 15px; }
        .cart-summary ul { list-style: none; padding: 0; margin: 0; }
        .cart-summary li { margin-bottom: 10px; font-size: 14px; color: #333; }
        .cart-summary .total { font-size: 18px; font-weight: 600; color: #dc3545; margin-top: 15px; }
        .checkout-form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .form-section { margin-bottom: 20px; }
        .form-section h3 { font-size: 16px; color: #333; margin-bottom: 10px; }
        label { display: block; font-size: 14px; color: #333; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 80px; }
        button { background: #28a745; color: #fff; padding: 12px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; transition: background 0.3s ease, transform 0.2s ease; }
        button:hover { background: #1e7e34; transform: translateY(-2px); }
        .error-message { color: #dc3545; font-size: 14px; margin-top: 10px; text-align: center; }
        .payment-method { margin-bottom: 20px; }
        .payment-method label { display: inline-block; margin-right: 20px; font-size: 14px; color: #333; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        @media (max-width: 768px) {
            .checkout-container { flex-direction: column; }
            .cart-summary, .checkout-form { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="cart-summary">
            <h2>Sản phẩm</h2>
            <ul>
                <?php foreach ($cart as $item): ?>
                    <li><?php echo htmlspecialchars($item['product_name']); ?> x <?php echo $item['quantity']; ?> - <span style="color: #dc3545;"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</span></li>
                <?php endforeach; ?>
            </ul>
            <p class="total">Tổng cộng: <?php echo number_format($total, 0, ',', '.'); ?>đ</p>
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
            
            <div class="payment-method">
                <h3>Hình thức thanh toán</h3>
                <label>
                    <input type="radio" name="payment_method" value="cod" checked> Thanh toán khi nhận hàng
                </label>
                <label>
                    <input type="radio" name="payment_method" value="card"> Thanh toán bằng thẻ
                </label>
            </div>
            
            <button type="submit">Xác Nhận Và Đặt Hàng</button>
        </form>
    </div>

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

<?php include 'includes/footer.php'; ?>
<?php ob_end_flush(); // Kết thúc output buffering ?>