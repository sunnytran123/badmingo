<?php
session_start();
require_once "config/database.php";
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all';

// Phân trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Đếm tổng và lấy danh sách bằng UNION từ bookings và orders (không phụ thuộc bảng transactions)
if ($filter_type === 'booking') {
    // Count
    $countSql = "SELECT COUNT(*) AS total FROM bookings WHERE user_id = ?";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $totalTransactions = intval($row['total'] ?? 0);
    $totalPages = ceil($totalTransactions / $limit);

    // List
    $sql = "SELECT booking_id AS ref_id, NULL AS order_id, booking_date AS created_at, total_price AS amount, payment_method, status, CONCAT('BOOK-', booking_id) AS code
            FROM bookings
            WHERE user_id = ?
            ORDER BY created_at DESC, booking_id DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = [];
    while ($r = $result->fetch_assoc()) {
        $transactions[] = [
            'created_at' => $r['created_at'],
            'booking_id' => $r['ref_id'],
            'order_id' => null,
            'amount' => $r['amount'],
            'payment_method' => $r['payment_method'],
            'payment_status' => $r['status'],
            'transaction_code' => $r['code']
        ];
    }
} elseif ($filter_type === 'order') {
    // Count
    $countSql = "SELECT COUNT(*) AS total FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $totalTransactions = intval($row['total'] ?? 0);
    $totalPages = ceil($totalTransactions / $limit);

    // List
    $sql = "SELECT order_id AS ref_id, created_at, total_amount AS amount, payment_method, status, CONCAT('ORD-', order_id) AS code
            FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC, order_id DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = [];
    while ($r = $result->fetch_assoc()) {
        $transactions[] = [
            'created_at' => $r['created_at'],
            'booking_id' => null,
            'order_id' => $r['ref_id'],
            'amount' => $r['amount'],
            'payment_method' => $r['payment_method'],
            'payment_status' => $r['status'],
            'transaction_code' => $r['code']
        ];
    }
} else {
    // all: UNION bookings + orders
    // Count
    $countSql = "SELECT (
                    (SELECT COUNT(*) FROM bookings WHERE user_id = ?) +
                    (SELECT COUNT(*) FROM orders WHERE user_id = ?)
                 ) AS total";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $totalTransactions = intval($row['total'] ?? 0);
    $totalPages = ceil($totalTransactions / $limit);

    // List via UNION ALL inside subquery for ORDER BY + LIMIT
    $sql = "SELECT created_at, booking_id, order_id, amount, payment_method, status, code FROM (
                SELECT b.created_at AS created_at, b.booking_id AS booking_id, NULL AS order_id, b.total_price AS amount, b.payment_method AS payment_method, b.status AS status, CONCAT('BOOK-', b.booking_id) AS code
                FROM bookings b
                WHERE b.user_id = ?
                UNION ALL
                SELECT o.created_at AS created_at, NULL AS booking_id, o.order_id AS order_id, o.total_amount AS amount, o.payment_method AS payment_method, o.status AS status, CONCAT('ORD-', o.order_id) AS code
                FROM orders o
                WHERE o.user_id = ?
            ) x
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<h2 class="section-title">Lịch sử giao dịch</h2>

<div class="shop-intro">
    <img src="images/Olypic.png" alt="Lịch sử giao dịch">
    <div class="shop-intro-content">
        <h2>Chào mừng đến với Sunny Sport</h2>
        <p style="text-align:justify">Xem lịch sử giao dịch của bạn, bao gồm đặt sân cầu lông và mua sắm sản phẩm. Chúng tôi đảm bảo tính minh bạch và an toàn cho mọi giao dịch.</p>
    </div>
</div>

<div class="shop-container">
    <form method="get" class="product-filter">
        <div class="filter-section">
            <h3>Loại giao dịch</h3>
            <div class="filter-group">
                <select name="filter_type" class="mt-1 block w-full">
                    <option value="all" <?php if($filter_type == 'all') echo 'selected'; ?>>Tất cả</option>
                    <option value="booking" <?php if($filter_type == 'booking') echo 'selected'; ?>>Đặt sân cầu lông</option>
                    <option value="order" <?php if($filter_type == 'order') echo 'selected'; ?>>Mua hàng</option>
                </select>
            </div>
        </div>
        <button type="submit" class="filter-submit">Lọc</button>
    </form>

    <div class="transactions" style="flex:1; background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <?php if (empty($transactions)): ?>
            <p style="text-align:center; color:#666;">Không có giao dịch nào phù hợp.</p>
        <?php else: ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                        <th style="padding:12px; text-align:left;">Ngày</th>
                        <th style="padding:12px; text-align:left;">Loại</th>
                        <th style="padding:12px; text-align:left;">Số tiền</th>
                        <th style="padding:12px; text-align:left;">Phương thức</th>
                        <th style="padding:12px; text-align:left;">Trạng thái</th>
                        <th style="padding:12px; text-align:left;">Mã</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transactions as $trans): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;"><?php echo date('d/m/Y H:i', strtotime($trans['created_at'])); ?></td>
                            <td style="padding:12px;"><?php echo !empty($trans['booking_id']) ? 'Đặt sân' : (!empty($trans['order_id']) ? 'Mua hàng' : 'Khác'); ?></td>
                            <td style="padding:12px; color:#dc3545; font-weight:bold;"><?php echo number_format($trans['amount'], 0, ',', '.') . 'đ'; ?></td>
                            <td style="padding:12px;"><?php echo ucfirst($trans['payment_method']); ?></td>
                            <td style="padding:12px; color:<?php echo ($trans['payment_status'] ?? $trans['status']) == 'completed' ? '#28a745' : (($trans['payment_status'] ?? $trans['status']) == 'pending' ? '#ffc107' : '#dc3545'); ?>;">
                                <?php echo ucfirst($trans['payment_status'] ?? $trans['status']); ?>
                            </td>
                            <td style="padding:12px;"><?php echo $trans['transaction_code'] ?? $trans['code'] ?? ''; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Phân trang -->
<div class="pagination" style="text-align:center; margin-bottom:40px; display:flex; justify-content:center; align-items:center;">
    <?php if ($totalPages > 1): ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?filter_type=<?php echo $filter_type; ?>&page=<?php echo $i; ?>"
               style="display:inline-block;padding:8px 16px;margin:0 2px;border-radius:5px;
               background:<?php echo $i==$page?'#007bff':'#f8f9fa'; ?>;color:<?php echo $i==$page?'#fff':'#333'; ?>;text-decoration:none;">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<style>
.shop-intro { display:flex; align-items:center; gap:20px; margin-bottom:40px; max-height:250px; overflow:hidden; }
.shop-intro img { width:250px; object-fit:cover; border-radius:8px; }
.shop-intro-content { flex:1; }
.shop-container { display:flex; flex-direction:row; gap:20px; align-items:flex-start; }
.product-filter { background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:250px; min-height:300px; overflow-y:auto; }
.filter-section { display:flex; flex-direction:column; gap:10px; margin-bottom:20px; }
.filter-section h3 { font-size:16px; margin-bottom:5px; color:#333; }
.filter-group { display:flex; flex-direction:column; gap:8px; }
.filter-group select { padding:8px; border:1px solid #d1d5db; border-radius:6px; }
.filter-submit { background:#007bff; color:white; padding:8px 15px; border:none; border-radius:6px; font-weight:600; cursor:pointer; transition:background 0.3s ease; width:100%; }
.filter-submit:hover { background:#0056b3; }
@media (max-width: 768px) {
    .shop-container { flex-direction:column; }
    .product-filter { width:100%; min-height:auto; }
}
</style>

<?php include 'includes/footer.php'; ?>