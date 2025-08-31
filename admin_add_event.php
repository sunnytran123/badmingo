<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

$message = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = trim($_POST['event_name']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $location = trim($_POST['location']);
    $max_participants = intval($_POST['max_participants']);
    $registration_fee = floatval($_POST['registration_fee']);
    
    // Kiểm tra tên sự kiện đã tồn tại
    $check = $conn->prepare("SELECT event_id FROM events WHERE event_name = ?");
    $check->bind_param("s", $event_name);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $message = "⚠️ Tên sự kiện đã tồn tại!";
    } else {
        // Thêm sự kiện mới
        $stmt = $conn->prepare("INSERT INTO events (event_name, description, event_date, start_time, end_time, location, max_participants, registration_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssid", $event_name, $description, $event_date, $start_time, $end_time, $location, $max_participants, $registration_fee);
        
        if ($stmt->execute()) {
            $message = "✅ Thêm sự kiện thành công!";
            // Reset form
            $_POST = array();
        } else {
            $message = "❌ Có lỗi xảy ra!";
        }
    }
}

include 'includes/header.php';
?>

<h2 class="section-title">Thêm Sự kiện mới</h2>

<div class="shop-container" style="display:flex; gap:20px; align-items:flex-start; padding:20px;">
    <!-- Sidebar -->
    <div class="product-filter" style="background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:250px; min-height:500px;">
        <h3 style="font-size:16px; color:#333;">Menu Quản lý</h3>
        <ul style="list-style:none; padding:0;">
            <li><a href="admin.php?section=dashboard" style="display:block; padding:10px; color:#333; text-decoration:none;">Dashboard</a></li>
            <li><a href="admin.php?section=users" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Người dùng</a></li>
            <li><a href="admin.php?section=products" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Sản phẩm</a></li>
            <li><a href="admin.php?section=orders" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Đơn hàng</a></li>
            <li><a href="admin.php?section=bookings" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Đặt sân</a></li>
            <li><a href="admin.php?section=events" style="display:block; padding:10px; color:#333; text-decoration:none; background:#f8f9fa; border-radius:6px;">Quản lý Sự kiện</a></li>
            <li><a href="admin.php?section=forum" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Diễn đàn</a></li>
            <li><a href="admin.php?section=stats" style="display:block; padding:10px; color:#333; text-decoration:none;">Thống kê</a></li>
            <li><a href="admin.php?section=settings" style="display:block; padding:10px; color:#333; text-decoration:none;">Cấu hình</a></li>
        </ul>
    </div>

    <!-- Nội dung chính -->
    <div class="admin-content" style="flex:1; background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3>Thêm Sự kiện mới</h3>
            <a href="admin.php?section=events" class="filter-submit" style="text-decoration:none;">← Quay lại</a>
        </div>

        <?php if ($message): ?>
            <div style="padding:10px; margin-bottom:20px; border-radius:6px; background:<?php echo strpos($message, '✅') !== false ? '#d4edda' : '#f8d7da'; ?>; color:<?php echo strpos($message, '✅') !== false ? '#155724' : '#721c24'; ?>; border:1px solid <?php echo strpos($message, '✅') !== false ? '#c3e6cb' : '#f5c6cb'; ?>;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" style="max-width:600px;">
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Tên sự kiện: *</label>
                <input type="text" name="event_name" value="<?php echo htmlspecialchars($_POST['event_name'] ?? ''); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Mô tả: *</label>
                <textarea name="description" rows="4" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Ngày diễn ra: *</label>
                <input type="date" name="event_date" value="<?php echo $_POST['event_date'] ?? ''; ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            </div>

            <div style="display:flex; gap:15px; margin-bottom:15px;">
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Giờ bắt đầu:</label>
                    <input type="time" name="start_time" value="<?php echo $_POST['start_time'] ?? ''; ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Giờ kết thúc:</label>
                    <input type="time" name="end_time" value="<?php echo $_POST['end_time'] ?? ''; ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Địa điểm:</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            </div>

            <div style="display:flex; gap:15px; margin-bottom:15px;">
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Số người tối đa:</label>
                    <input type="number" name="max_participants" value="<?php echo $_POST['max_participants'] ?? '50'; ?>" min="1" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Phí đăng ký (VNĐ):</label>
                    <input type="number" name="registration_fee" value="<?php echo $_POST['registration_fee'] ?? '0'; ?>" min="0" step="1000" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                </div>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="filter-submit" style="flex:1;">Thêm sự kiện</button>
                <a href="admin.php?section=events" class="filter-submit" style="flex:1; text-align:center; text-decoration:none; background:#6c757d;">Hủy</a>
            </div>
        </form>
    </div>
</div>

<style>
.section-title { font-size:24px; color:#333; margin-bottom:20px; }
.product-filter h3 { font-size:16px; color:#333; margin-bottom:10px; }
.product-filter ul li a:hover { background:#f2f4f7; border-radius:6px; }
.filter-submit { background:#007bff; color:white; padding:8px 15px; border:none; border-radius:6px; font-weight:600; cursor:pointer; text-align:center; }
.filter-submit:hover { background:#0056b3; }
@media (max-width: 768px) {
    .shop-container { flex-direction:column; }
    .product-filter { width:100%; min-height:auto; }
}
</style>

<?php include 'includes/footer.php'; ?> 