<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];
    
    // Validation
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } else {
        // Kiểm tra username và email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username hoặc email đã tồn tại.';
        } else {
            // Tạo user mới
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, phone, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssss", $username, $email, $full_name, $phone, $hashed_password, $role);
            
            if ($stmt->execute()) {
                $success = 'Tạo user mới thành công!';
                // Reset form
                $username = $email = $full_name = $phone = '';
            } else {
                $error = 'Có lỗi xảy ra khi tạo user: ' . $conn->error;
            }
        }
    }
}

include 'includes/header.php';
?>

<h2 class="section-title">Thêm User Mới</h2>

<div class="shop-container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div class="admin-content" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        
        <div style="margin-bottom: 20px;">
            <a href="admin.php?section=users" style="color: #007bff; text-decoration: none;">
                ← Quay lại Quản lý User
            </a>
        </div>

        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_add_user.php" style="display: grid; gap: 20px;">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Username <span style="color: red;">*</span>
                    </label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                           required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Vai trò <span style="color: red;">*</span>
                    </label>
                    <select name="role" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="client" <?php echo (isset($role) && $role === 'client') ? 'selected' : ''; ?>>Client</option>
                        <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Tên đầy đủ <span style="color: red;">*</span>
                </label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" 
                       required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Email <span style="color: red;">*</span>
                    </label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                           required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Số điện thoại
                    </label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Mật khẩu <span style="color: red;">*</span>
                    </label>
                    <input type="password" name="password" required minlength="6"
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <small style="color: #666; font-size: 12px;">Tối thiểu 6 ký tự</small>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Xác nhận mật khẩu <span style="color: red;">*</span>
                    </label>
                    <input type="password" name="confirm_password" required minlength="6"
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #eee;">
                <a href="admin.php?section=users" 
                   style="padding: 12px 24px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: 600;">
                    Hủy
                </a>
                <button type="submit" 
                        style="padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">
                    Tạo User
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.section-title { 
    font-size: 24px; 
    color: #333; 
    margin-bottom: 20px; 
    text-align: center;
}

button:hover {
    opacity: 0.9;
}

input:focus, select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

@media (max-width: 768px) {
    .shop-container {
        padding: 10px !important;
    }
    
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
