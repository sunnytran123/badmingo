<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

$message = '';

// Lấy cấu hình hiện tại
$stmt = $conn->prepare("SELECT * FROM settings ORDER BY setting_id");
$stmt->execute();
$result = $stmt->get_result();
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;
    
    // Cập nhật từng cài đặt
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->bind_param("ss", $value, $key);
        if (!$stmt->execute()) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        $message = "✅ Cập nhật cấu hình thành công!";
        // Lấy lại cấu hình mới
        $stmt = $conn->prepare("SELECT * FROM settings ORDER BY setting_id");
        $stmt->execute();
        $result = $stmt->get_result();
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row;
        }
    } else {
        $message = "❌ Có lỗi xảy ra khi cập nhật!";
    }
}

include 'includes/header.php';
?>

<h2 class="section-title">Cấu hình Hệ thống</h2>

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
            <li><a href="admin.php?section=events" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Sự kiện</a></li>
            <li><a href="admin.php?section=forum" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Diễn đàn</a></li>
            <li><a href="admin.php?section=stats" style="display:block; padding:10px; color:#333; text-decoration:none;">Thống kê</a></li>
            <li><a href="admin.php?section=settings" style="display:block; padding:10px; color:#333; text-decoration:none; background:#f8f9fa; border-radius:6px;">Cấu hình</a></li>
        </ul>
    </div>

    <!-- Nội dung chính -->
    <div class="admin-content" style="flex:1; background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3>Cấu hình Hệ thống</h3>
            <a href="admin.php?section=settings" class="filter-submit" style="text-decoration:none;">← Quay lại</a>
        </div>

        <?php if ($message): ?>
            <div style="padding:10px; margin-bottom:20px; border-radius:6px; background:<?php echo strpos($message, '✅') !== false ? '#d4edda' : '#f8d7da'; ?>; color:<?php echo strpos($message, '✅') !== false ? '#155724' : '#721c24'; ?>; border:1px solid <?php echo strpos($message, '✅') !== false ? '#c3e6cb' : '#f5c6cb'; ?>;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" style="max-width:800px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <!-- Thông tin cơ bản -->
                <div>
                    <h4 style="margin-bottom:15px; color:#333; border-bottom:2px solid #eee; padding-bottom:5px;">Thông tin Website</h4>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Tên website:</label>
                        <input type="text" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name']['setting_value'] ?? ''); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Mô tả website:</label>
                        <textarea name="settings[site_description]" rows="3" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;"><?php echo htmlspecialchars($settings['site_description']['setting_value'] ?? ''); ?></textarea>
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Email liên hệ:</label>
                        <input type="email" name="settings[contact_email]" value="<?php echo htmlspecialchars($settings['contact_email']['setting_value'] ?? ''); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Số điện thoại liên hệ:</label>
                        <input type="tel" name="settings[contact_phone]" value="<?php echo htmlspecialchars($settings['contact_phone']['setting_value'] ?? ''); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                </div>

                <!-- Cấu hình đặt sân -->
                <div>
                    <h4 style="margin-bottom:15px; color:#333; border-bottom:2px solid #eee; padding-bottom:5px;">Cấu hình Đặt sân</h4>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Giảm giá thanh toán trước (%):</label>
                        <input type="number" name="settings[booking_discount]" value="<?php echo $settings['booking_discount']['setting_value'] ?? '10'; ?>" min="0" max="50" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Số giờ tối đa đặt sân:</label>
                        <input type="number" name="settings[max_booking_hours]" value="<?php echo $settings['max_booking_hours']['setting_value'] ?? '4'; ?>" min="1" max="8" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Giờ mở cửa:</label>
                        <input type="time" name="settings[opening_hour]" value="<?php echo $settings['opening_hour']['setting_value'] ?? '06:00'; ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Giờ đóng cửa:</label>
                        <input type="time" name="settings[closing_hour]" value="<?php echo $settings['closing_hour']['setting_value'] ?? '22:00'; ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                </div>
            </div>

            <!-- Cấu hình thanh toán -->
            <div style="margin-top:20px;">
                <h4 style="margin-bottom:15px; color:#333; border-bottom:2px solid #eee; padding-bottom:5px;">Cấu hình Thanh toán</h4>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Tài khoản ngân hàng:</label>
                        <input type="text" name="settings[bank_account]" value="<?php echo htmlspecialchars($settings['bank_account']['setting_value'] ?? ''); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Cổng thanh toán:</label>
                        <input type="text" name="settings[payment_gateway]" value="<?php echo htmlspecialchars($settings['payment_gateway']['setting_value'] ?? ''); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                </div>
            </div>

            <div style="margin-top:30px; display:flex; gap:10px;">
                <button type="submit" class="filter-submit" style="flex:1;">Cập nhật cấu hình</button>
                <a href="admin.php?section=settings" class="filter-submit" style="flex:1; text-align:center; text-decoration:none; background:#6c757d;">Hủy</a>
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
    .admin-content form > div { grid-template-columns:1fr; }
}
</style>

<?php include 'includes/footer.php'; ?> 