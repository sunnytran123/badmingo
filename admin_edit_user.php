<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

$user_id = $_GET['id'] ?? 0;
$message = '';

// Lấy thông tin người dùng
if ($user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        header('Location: admin.php?section=users');
        exit();
    }
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $new_password = trim($_POST['new_password']);
    
    // Kiểm tra email đã tồn tại (trừ user hiện tại)
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $check->bind_param("si", $email, $user_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $message = "⚠️ Email đã tồn tại!";
    } else {
        if (!empty($new_password)) {
            // Cập nhật cả mật khẩu
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("sssssi", $full_name, $email, $phone, $role, $hashedPassword, $user_id);
        } else {
            // Chỉ cập nhật thông tin cơ bản
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $role, $user_id);
        }
        
        if ($stmt->execute()) {
            $message = "✅ Cập nhật thành công!";
            // Lấy lại thông tin mới
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $message = "❌ Có lỗi xảy ra!";
        }
    }
}

include 'includes/header.php';
?>

<h2 class="section-title">Chỉnh sửa Người dùng</h2>

<div class="shop-container" style="display:flex; gap:20px; align-items:flex-start; padding:20px;">
    <!-- Sidebar -->
    <div class="product-filter" style="background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:250px; min-height:500px;">
        <h3 style="font-size:16px; color:#333;">Menu Quản lý</h3>
        <ul style="list-style:none; padding:0;">
            <li><a href="admin.php?section=dashboard" style="display:block; padding:10px; color:#333; text-decoration:none;">Dashboard</a></li>
            <li><a href="admin.php?section=users" style="display:block; padding:10px; color:#333; text-decoration:none; background:#f8f9fa; border-radius:6px;">Quản lý Người dùng</a></li>
            <li><a href="admin.php?section=products" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Sản phẩm</a></li>
            <li><a href="admin.php?section=orders" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Đơn hàng</a></li>
            <li><a href="admin.php?section=bookings" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Đặt sân</a></li>
            <li><a href="admin.php?section=events" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Sự kiện</a></li>
            <li><a href="admin.php?section=forum" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Diễn đàn</a></li>
            <li><a href="admin.php?section=stats" style="display:block; padding:10px; color:#333; text-decoration:none;">Thống kê</a></li>
            <li><a href="admin.php?section=settings" style="display:block; padding:10px; color:#333; text-decoration:none;">Cấu hình</a></li>
        </ul>
    </div>

    <!-- Nội dung chính -->
    <div class="admin-content" style="flex:1; background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3>Chỉnh sửa Người dùng: <?php echo htmlspecialchars($user['full_name']); ?></h3>
            <a href="admin.php?section=users" class="filter-submit" style="text-decoration:none;">← Quay lại</a>
        </div>

        <?php if ($message): ?>
            <div style="padding:10px; margin-bottom:20px; border-radius:6px; background:<?php echo strpos($message, '✅') !== false ? '#d4edda' : '#f8d7da'; ?>; color:<?php echo strpos($message, '✅') !== false ? '#155724' : '#721c24'; ?>; border:1px solid <?php echo strpos($message, '✅') !== false ? '#c3e6cb' : '#f5c6cb'; ?>;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" style="max-width:600px;">
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Tên đăng nhập:</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" readonly>
                <small style="color:#666;">Không thể thay đổi tên đăng nhập</small>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Họ và tên: *</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Email: *</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Số điện thoại: *</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Vai trò: *</label>
                <select name="role" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Mật khẩu mới:</label>
                <input type="password" name="new_password" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                <small style="color:#666;">Để trống nếu không muốn thay đổi mật khẩu</small>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Ngày tạo:</label>
                <input type="text" value="<?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" readonly>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="filter-submit" style="flex:1;">Cập nhật</button>
                <a href="admin.php?section=users" class="filter-submit" style="flex:1; text-align:center; text-decoration:none; background:#6c757d;">Hủy</a>
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