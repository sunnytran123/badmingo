<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

$section = $_GET['section'] ?? 'dashboard';

// Thống kê cho dashboard
if ($section === 'dashboard') {
    // Doanh thu orders
    $stmt = $conn->prepare("SELECT SUM(total_amount) AS total_orders FROM orders WHERE status = 'completed'");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_orders = $result->fetch_assoc()['total_orders'] ?? 0;

    // Doanh thu bookings
    $stmt = $conn->prepare("SELECT SUM(total_price) AS total_bookings FROM bookings WHERE status = 'confirmed'");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_bookings = $result->fetch_assoc()['total_bookings'] ?? 0;

    // User mới (tháng này)
    $stmt = $conn->prepare("SELECT COUNT(*) AS new_users FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stmt->execute();
    $result = $stmt->get_result();
    $new_users = $result->fetch_assoc()['new_users'] ?? 0;

    // Đặt sân hôm nay
    $stmt = $conn->prepare("SELECT COUNT(*) AS today_bookings FROM bookings WHERE booking_date = CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result();
    $today_bookings = $result->fetch_assoc()['today_bookings'] ?? 0;

    // Đơn hàng hôm nay
    $stmt = $conn->prepare("SELECT COUNT(*) AS today_orders FROM orders WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result();
    $today_orders = $result->fetch_assoc()['today_orders'] ?? 0;

    // Top 5 sân đặt nhiều
    $stmt = $conn->prepare("SELECT court_id, COUNT(*) AS count FROM bookings WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY court_id ORDER BY count DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    $top_courts = $result->fetch_all(MYSQLI_ASSOC);

    // Top 5 sản phẩm bán chạy
    $stmt = $conn->prepare("SELECT p.product_name, SUM(oi.quantity) AS total_quantity FROM order_items oi JOIN products p ON oi.product_id = p.product_id GROUP BY oi.product_id ORDER BY total_quantity DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    $top_products = $result->fetch_all(MYSQLI_ASSOC);
}

include 'includes/header.php';
?>

<h2 class="section-title">Quản lý Admin - Sunny Sport</h2>

<div class="shop-container" style="display:flex; gap:20px; align-items:flex-start; padding:20px;">
    <!-- Sidebar -->
    <div class="product-filter" style="background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:250px; min-height:500px;">
        <h3 style="font-size:16px; color:#333;">Menu Quản lý</h3>
        <ul style="list-style:none; padding:0;">
            <li><a href="admin.php?section=dashboard" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'dashboard' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Dashboard</a></li>
            <li><a href="admin.php?section=users" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'users' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Quản lý Người dùng</a></li>
            <li><a href="admin.php?section=products" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'products' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Quản lý Sản phẩm</a></li>
            <li><a href="admin.php?section=orders" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'orders' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Quản lý Đơn hàng</a></li>
            <li><a href="admin.php?section=bookings" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'bookings' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Quản lý Đặt sân</a></li>
            <li><a href="admin.php?section=events" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'events' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Quản lý Sự kiện</a></li>
            <li><a href="admin.php?section=forum" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'forum' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Quản lý Diễn đàn</a></li>
            <li><a href="admin.php?section=stats" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'stats' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Thống kê</a></li>
            <li><a href="admin.php?section=settings" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'settings' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Cấu hình</a></li>
        </ul>
    </div>

    <!-- Nội dung chính -->
    <div class="admin-content" style="flex:1; background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <?php if ($section === 'dashboard'): ?>
            <h3>Dashboard</h3>
            <div class="stats-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:15px; margin-bottom:20px;">
                <div class="stat-box" style="padding:15px; border:1px solid #eee; border-radius:8px; text-align:center;">
                    <h4>Doanh thu Đơn hàng</h4>
                    <p style="color:#dc3545; font-weight:bold;"><?php echo number_format($total_orders, 0, ',', '.'); ?> VNĐ</p>
                </div>
                <div class="stat-box" style="padding:15px; border:1px solid #eee; border-radius:8px; text-align:center;">
                    <h4>Doanh thu Đặt sân</h4>
                    <p style="color:#dc3545; font-weight:bold;"><?php echo number_format($total_bookings, 0, ',', '.'); ?> VNĐ</p>
                </div>
                <div class="stat-box" style="padding:15px; border:1px solid #eee; border-radius:8px; text-align:center;">
                    <h4>User mới (tháng)</h4>
                    <p style="color:#28a745; font-weight:bold;"><?php echo $new_users; ?></p>
                </div>
                <div class="stat-box" style="padding:15px; border:1px solid #eee; border-radius:8px; text-align:center;">
                    <h4>Đặt sân hôm nay</h4>
                    <p style="color:#28a745; font-weight:bold;"><?php echo $today_bookings; ?></p>
                </div>
                <div class="stat-box" style="padding:15px; border:1px solid #eee; border-radius:8px; text-align:center;">
                    <h4>Đơn hàng hôm nay</h4>
                    <p style="color:#28a745; font-weight:bold;"><?php echo $today_orders; ?></p>
                </div>
            </div>
            <div style="margin-top:20px;">
                <h4>Top 5 Sân đặt nhiều (30 ngày)</h4>
                <canvas id="topCourtsChart"></canvas>
            </div>
            <div style="margin-top:20px;">
                <h4>Top 5 Sản phẩm bán chạy</h4>
                <canvas id="topProductsChart"></canvas>
            </div>
        <?php elseif ($section === 'users'): ?>
            <h3>Quản lý Người dùng</h3>
            <?php
            $stmt = $conn->prepare("SELECT user_id, username, full_name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                        <th style="padding:12px;">ID</th>
                        <th style="padding:12px;">Tên</th>
                        <th style="padding:12px;">Email</th>
                        <th style="padding:12px;">SĐT</th>
                        <th style="padding:12px;">Vai trò</th>
                        <th style="padding:12px;">Ngày tạo</th>
                        <th style="padding:12px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;"><?php echo $row['user_id']; ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                            <td style="padding:12px;"><?php echo ucfirst($row['role']); ?></td>
                            <td style="padding:12px;"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td style="padding:12px;">
                                <a href="admin_edit_user.php?id=<?php echo $row['user_id']; ?>" style="color:#007bff; text-decoration:none;">Sửa</a> |
                                <a href="admin_delete_user.php?id=<?php echo $row['user_id']; ?>" onclick="return confirm('Xóa người dùng này?');" style="color:#dc3545; text-decoration:none;">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif ($section === 'products'): ?>
            <h3>Quản lý Sản phẩm</h3>
            <a href="admin_add_product.php" class="filter-submit" style="display:inline-block; margin-bottom:20px;">Thêm Sản phẩm</a>
            <?php
            $stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.price, p.stock, c.category_name, 
                                   (SELECT COUNT(*) FROM product_variants WHERE product_id = p.product_id) as variant_count 
                                   FROM products p LEFT JOIN product_categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                        <th style="padding:12px;">ID</th>
                        <th style="padding:12px;">Tên</th>
                        <th style="padding:12px;">Danh mục</th>
                        <th style="padding:12px;">Giá</th>
                        <th style="padding:12px;">Tồn kho</th>
                        <th style="padding:12px;">Variants</th>
                        <th style="padding:12px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;"><?php echo $row['product_id']; ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['category_name'] ?? 'Chưa có'); ?></td>
                            <td style="padding:12px;"><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td style="padding:12px;"><?php echo $row['stock']; ?></td>
                            <td style="padding:12px;">
                                <?php if ($row['variant_count'] > 0): ?>
                                    <span style="background:#28a745; color:white; padding:2px 6px; border-radius:3px; font-size:11px;">
                                        <?php echo $row['variant_count']; ?> variants
                                    </span>
                                <?php else: ?>
                                    <span style="color:#666; font-size:11px;">Không có</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px;">
                                <a href="admin_edit_product.php?id=<?php echo $row['product_id']; ?>" style="color:#007bff; text-decoration:none;">Sửa</a> |
                                <a href="admin_delete_product.php?id=<?php echo $row['product_id']; ?>" onclick="return confirm('Xóa sản phẩm này?');" style="color:#dc3545; text-decoration:none;">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif ($section === 'orders'): ?>
            <h3>Quản lý Đơn hàng</h3>
            <?php
            $stmt = $conn->prepare("SELECT o.order_id, o.created_at, o.total_amount, o.status, o.payment_method, u.full_name FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.created_at DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                        <th style="padding:12px;">ID</th>
                        <th style="padding:12px;">Người đặt</th>
                        <th style="padding:12px;">Ngày</th>
                        <th style="padding:12px;">Tổng tiền</th>
                        <th style="padding:12px;">Phương thức</th>
                        <th style="padding:12px;">Trạng thái</th>
                        <th style="padding:12px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;"><?php echo $row['order_id']; ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td style="padding:12px;"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td style="padding:12px;"><?php echo number_format($row['total_amount'], 0, ',', '.'); ?> VNĐ</td>
                            <td style="padding:12px;"><?php echo ucfirst($row['payment_method']); ?></td>
                            <td style="padding:12px;"><?php echo ucfirst($row['status']); ?></td>
                            <td style="padding:12px;">
                                <form action="admin_update_order.php" method="post" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo $row['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $row['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif ($section === 'bookings'): ?>
            <h3>Quản lý Đặt sân</h3>
            <?php
            $stmt = $conn->prepare("SELECT b.booking_id, b.booking_date, b.start_time, b.end_time, b.total_price, b.status, b.payment_method, b.fullname, c.court_id FROM bookings b JOIN courts c ON b.court_id = c.court_id ORDER BY b.booking_date DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                        <th style="padding:12px;">ID</th>
                        <th style="padding:12px;">Người đặt</th>
                        <th style="padding:12px;">Sân</th>
                        <th style="padding:12px;">Ngày</th>
                        <th style="padding:12px;">Giờ</th>
                        <th style="padding:12px;">Tổng tiền</th>
                        <th style="padding:12px;">Phương thức</th>
                        <th style="padding:12px;">Trạng thái</th>
                        <th style="padding:12px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;"><?php echo $row['booking_id']; ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td style="padding:12px;">Sân <?php echo $row['court_id']; ?></td>
                            <td style="padding:12px;"><?php echo date('d/m/Y', strtotime($row['booking_date'])); ?></td>
                            <td style="padding:12px;"><?php echo substr($row['start_time'], 0, 5) . ' - ' . substr($row['end_time'], 0, 5); ?></td>
                            <td style="padding:12px;"><?php echo number_format($row['total_price'], 0, ',', '.'); ?> VNĐ</td>
                            <td style="padding:12px;"><?php echo ucfirst($row['payment_method']); ?></td>
                            <td style="padding:12px;"><?php echo ucfirst($row['status']); ?></td>
                            <td style="padding:12px;">
                                <form action="admin_update_booking.php" method="post" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $row['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $row['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif ($section === 'events'): ?>
            <h3>Quản lý Sự kiện</h3>
            <a href="admin_add_event.php" class="filter-submit" style="display:inline-block; margin-bottom:20px;">Thêm Sự kiện</a>
            <?php
            $stmt = $conn->prepare("SELECT event_id, event_name, event_date, start_time, end_time, location, max_participants, current_participants, registration_fee, status FROM events ORDER BY event_date DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                        <th style="padding:12px;">ID</th>
                        <th style="padding:12px;">Tên sự kiện</th>
                        <th style="padding:12px;">Ngày</th>
                        <th style="padding:12px;">Giờ</th>
                        <th style="padding:12px;">Địa điểm</th>
                        <th style="padding:12px;">Số người</th>
                        <th style="padding:12px;">Phí</th>
                        <th style="padding:12px;">Trạng thái</th>
                        <th style="padding:12px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;"><?php echo $row['event_id']; ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['event_name']); ?></td>
                            <td style="padding:12px;"><?php echo date('d/m/Y', strtotime($row['event_date'])); ?></td>
                            <td style="padding:12px;"><?php echo $row['start_time'] ? substr($row['start_time'], 0, 5) : '-'; ?> - <?php echo $row['end_time'] ? substr($row['end_time'], 0, 5) : '-'; ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['location']); ?></td>
                            <td style="padding:12px;"><?php echo $row['current_participants']; ?>/<?php echo $row['max_participants']; ?></td>
                            <td style="padding:12px;"><?php echo number_format($row['registration_fee'], 0, ',', '.'); ?> VNĐ</td>
                            <td style="padding:12px;"><?php echo ucfirst($row['status']); ?></td>
                            <td style="padding:12px;">
                                <a href="admin_edit_event.php?id=<?php echo $row['event_id']; ?>" style="color:#007bff; text-decoration:none;">Sửa</a> |
                                <a href="admin_delete_event.php?id=<?php echo $row['event_id']; ?>" onclick="return confirm('Xóa sự kiện này?');" style="color:#dc3545; text-decoration:none;">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif ($section === 'forum'): ?>
            <h3>Quản lý Diễn đàn</h3>
            <div style="margin-bottom:20px;">
                <h4>Danh mục diễn đàn</h4>
                <?php
                $stmt = $conn->prepare("SELECT category_id, category_name, description FROM forum_categories ORDER BY category_id");
                $stmt->execute();
                $result = $stmt->get_result();
                ?>
                <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                    <thead>
                        <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                            <th style="padding:12px;">ID</th>
                            <th style="padding:12px;">Tên danh mục</th>
                            <th style="padding:12px;">Mô tả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:12px;"><?php echo $row['category_id']; ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($row['description']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div>
                <h4>Chủ đề diễn đàn</h4>
                <?php
                $stmt = $conn->prepare("SELECT ft.thread_id, ft.title, fc.category_name, ft.created_at FROM forum_threads ft LEFT JOIN forum_categories fc ON ft.category_id = fc.category_id ORDER BY ft.created_at DESC LIMIT 20");
                $stmt->execute();
                $result = $stmt->get_result();
                ?>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                            <th style="padding:12px;">ID</th>
                            <th style="padding:12px;">Tiêu đề</th>
                            <th style="padding:12px;">Danh mục</th>
                            <th style="padding:12px;">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:12px;"><?php echo $row['thread_id']; ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td style="padding:12px;"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($section === 'stats'): ?>
            <h3>Thống kê Chi tiết</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px,1fr)); gap:20px; margin-bottom:30px;">
                <div style="background:#f8f9fa; padding:20px; border-radius:8px; text-align:center;">
                    <h4>Thống kê theo tháng</h4>
                    <p style="font-size:24px; color:#007bff; font-weight:bold;"><?php echo date('m/Y'); ?></p>
                </div>
                <div style="background:#f8f9fa; padding:20px; border-radius:8px; text-align:center;">
                    <h4>Tổng số sản phẩm</h4>
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
                    $stmt->execute();
                    $total_products = $stmt->get_result()->fetch_assoc()['total'];
                    ?>
                    <p style="font-size:24px; color:#28a745; font-weight:bold;"><?php echo $total_products; ?></p>
                </div>
                <div style="background:#f8f9fa; padding:20px; border-radius:8px; text-align:center;">
                    <h4>Tổng số sân</h4>
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM courts");
                    $stmt->execute();
                    $total_courts = $stmt->get_result()->fetch_assoc()['total'];
                    ?>
                    <p style="font-size:24px; color:#ffc107; font-weight:bold;"><?php echo $total_courts; ?></p>
                </div>
                <div style="background:#f8f9fa; padding:20px; border-radius:8px; text-align:center;">
                    <h4>Tổng số sự kiện</h4>
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM events");
                    $stmt->execute();
                    $total_events = $stmt->get_result()->fetch_assoc()['total'];
                    ?>
                    <p style="font-size:24px; color:#17a2b8; font-weight:bold;"><?php echo $total_events; ?></p>
                </div>
            </div>
            
            <div style="background:#f8f9fa; padding:20px; border-radius:8px;">
                <h4>Biểu đồ doanh thu theo tháng</h4>
                <canvas id="monthlyRevenueChart" style="max-height:300px;"></canvas>
            </div>
        <?php elseif ($section === 'settings'): ?>
            <h3>Cấu hình Hệ thống</h3>
            <p>Quản lý cài đặt hệ thống, thông tin website và cấu hình đặt sân.</p>
            <a href="admin_edit_settings.php" class="filter-submit" style="display:inline-block; margin-top:10px;">Chỉnh sửa cấu hình</a>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($section === 'dashboard'): ?>
        const topCourtsCtx = document.getElementById('topCourtsChart').getContext('2d');
        new Chart(topCourtsCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($c) { return '"Sân ' . $c['court_id'] . '"'; }, $top_courts)); ?>],
                datasets: [{
                    label: 'Số lượt đặt',
                    data: [<?php echo implode(',', array_map(function($c) { return $c['count']; }, $top_courts)); ?>],
                    backgroundColor: '#28a745',
                    borderColor: '#1e7e34',
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });

        const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($p) { return '"' . addslashes($p['product_name']) . '"'; }, $top_products)); ?>],
                datasets: [{
                    label: 'Số lượng bán',
                    data: [<?php echo implode(',', array_map(function($p) { return $p['total_quantity']; }, $top_products)); ?>],
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });

    <?php endif; ?>
    
    <?php if ($section === 'stats'): ?>
        // Biểu đồ doanh thu theo tháng cho section stats
        const monthlyRevenueChart = document.getElementById('monthlyRevenueChart');
        if (monthlyRevenueChart) {
            const monthlyCtx = monthlyRevenueChart.getContext('2d');
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                    datasets: [{
                        label: 'Doanh thu (triệu VNĐ)',
                        data: [12, 19, 15, 25, 22, 30, 28, 35, 32, 40, 38, 45],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: { 
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + 'M';
                                }
                            }
                        } 
                    }
                }
            });
        }
    <?php endif; ?>
});
</script>

<style>
.section-title { font-size:24px; color:#333; margin-bottom:20px; }
.product-filter h3 { font-size:16px; color:#333; margin-bottom:10px; }
.product-filter ul li a:hover { background:#f2f4f7; border-radius:6px; }
.stats-grid .stat-box h4 { font-size:14px; color:#666; margin-bottom:5px; }
.stats-grid .stat-box p { font-size:18px; }
table th, table td { text-align:center; padding:12px; }
.filter-submit { background:#007bff; color:white; padding:8px 15px; border:none; border-radius:6px; font-weight:600; cursor:pointer; text-align:center; }
.filter-submit:hover { background:#0056b3; }
@media (max-width: 768px) {
    .shop-container { flex-direction:column; }
    .product-filter { width:100%; min-height:auto; }
}
</style>

<?php include 'includes/footer.php'; ?>