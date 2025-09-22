<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
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
            <!-- <li><a href="admin.php?section=events" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'events' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Quản lý Sự kiện</a></li> -->
            <!-- <li><a href="admin.php?section=forum" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'forum' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Quản lý Diễn đàn</a></li> -->
            <li><a href="admin.php?section=stats" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'stats' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Thống kê</a></li>
            <!-- <li><a href="admin.php?section=settings" style="display:block; padding:10px; color:#333; text-decoration:none; <?php echo $section === 'settings' ? 'background:#f8f9fa; border-radius:6px;' : ''; ?>">Cấu hình</a></li> -->
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
                    <h4>Đặt sân hôm nay</h4>
                    <p style="color:#28a745; font-weight:bold;"><?php echo $today_bookings; ?></p>
                </div>
                <div class="stat-box" style="padding:15px; border:1px solid #eee; border-radius:8px; text-align:center;">
                    <h4>Đơn hàng hôm nay</h4>
                    <p style="color:#28a745; font-weight:bold;"><?php echo $today_orders; ?></p>
                </div>
                
                <!-- Doanh thu Đặt sân hôm nay -->
                <div class="stat-box" style="padding:15px; border:1px solid #eee; border-radius:8px; text-align:center;">
                    <h4>Doanh thu sân hôm nay</h4>
                    <?php
                    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_price),0) AS revenue_booking_today FROM bookings WHERE booking_date = CURDATE() AND status='confirmed'");
                    $stmt->execute();
                    $revenue_booking_today = $stmt->get_result()->fetch_assoc()['revenue_booking_today'] ?? 0;
                    ?>
                    <p style="color:#fd7e14; font-weight:bold;"><?php echo number_format($revenue_booking_today, 0, ',', '.'); ?> VNĐ</p>
                </div>
                
                <!-- Doanh thu Đơn hàng hôm nay -->
                <div class="stat-box" style="padding:15px; border:1px solid #eee; border-radius:8px; text-align:center;">
                    <h4>Doanh thu bán hôm nay</h4>
                    <?php
                    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount),0) AS revenue_order_today FROM orders WHERE DATE(created_at) = CURDATE() AND status='completed'");
                    $stmt->execute();
                    $revenue_order_today = $stmt->get_result()->fetch_assoc()['revenue_order_today'] ?? 0;
                    ?>
                    <p style="color:#6f42c1; font-weight:bold;"><?php echo number_format($revenue_order_today, 0, ',', '.'); ?> VNĐ</p>
                </div>
            </div>
            
            <!-- Bộ lọc thời gian cho Top Charts -->
            <div style="background:#f8f9fa; padding:15px; border-radius:8px; margin:20px 0;">
                <h4 style="margin-bottom:15px;">📊 Lọc thời gian cho biểu đồ Top</h4>
                <div style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Khoảng thời gian:</label>
                        <select id="chartsTimeFilter" onchange="changeChartsTimeFilter()" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
                            <option value="30days">30 ngày gần nhất</option>
                            <option value="7days">7 ngày gần nhất</option>
                            <option value="this_month">Tháng này</option>
                            <option value="this_week">Tuần này</option>
                            <option value="custom">Tùy chỉnh</option>
                        </select>
                    </div>
                    
                    <!-- Tùy chỉnh thời gian -->
                    <div id="chartsCustomRange" style="display:none;">
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div>
                                <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Từ:</label>
                                <input type="date" id="chartsStartDate" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Đến:</label>
                                <input type="date" id="chartsEndDate" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <button onclick="updateTopCharts()" class="filter-submit" style="background:#28a745; margin-top:20px;">
                            🔄 Cập nhật biểu đồ
                        </button>
                    </div>
                </div>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;">
                <div>
                    <h4 id="topCourtsTitle">Top 5 Sân đặt nhiều</h4>
                    <canvas id="dashboardTopCourtsChart" style="width: 100%; height: 300px; max-width: 600px;"></canvas>
                </div>
                <div>
                    <h4 id="topProductsTitle">Top 5 Sản phẩm bán chạy</h4>
                    <canvas id="dashboardTopProductsChart" style="width: 100%; height: 300px; max-width: 600px;"></canvas>
                </div>
            </div>
            <!-- Biểu đồ giờ cao điểm trong ngày -->
            <div style="margin-top:20px;">
                <h4>Giờ cao điểm đặt sân (30 ngày gần nhất)</h4>
                <canvas id="dashboardPeakHoursChart"></canvas>
            </div>
        <?php elseif ($section === 'users'): ?>
            <h3>Quản lý Người dùng</h3>
            <a href="admin_add_user.php" class="filter-submit" style="display:inline-block; margin-bottom:20px;">Thêm User mới</a>
            <?php
            $stmt = $conn->prepare("SELECT user_id, username, full_name, email, phone, role FROM users ORDER BY user_id DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                        <th style="padding:12px;">ID</th>
                        <th style="padding:12px;">Username</th>
                        <th style="padding:12px;">Tên đầy đủ</th>
                        <th style="padding:12px;">Email</th>
                        <th style="padding:12px;">SĐT</th>
                        <th style="padding:12px;">Vai trò</th>
                        <th style="padding:12px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;"><?php echo $row['user_id']; ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                            <td style="padding:12px;">
                                <span style="padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600; 
                                    <?php echo $row['role'] === 'admin' ? 'background:#dc3545; color:white;' : 'background:#28a745; color:white;'; ?>">
                                    <?php echo ucfirst($row['role']); ?>
                                </span>
                            </td>
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
            <a href="admin_add_booking.php" class="filter-submit" style="display:inline-block; margin-bottom:20px;">Thêm Đặt sân mới</a>
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
            
            <!-- Bộ lọc thời gian -->
            <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:20px;">
                <h4 style="margin-bottom:15px;">Lọc theo thời gian</h4>
                <div style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Loại lọc:</label>
                        <select id="timeFilter" onchange="changeTimeFilter()" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
                            <option value="specific">Chọn thời gian cụ thể</option>
                            <option value="day">Theo ngày</option>
                            <option value="week">Theo tuần</option>
                            <option value="month">Theo tháng</option>
                            <option value="range">Khoảng thời gian tùy chỉnh</option>
                        </select>
                    </div>
                    
                    <!-- Chọn thời gian cụ thể -->
                    <div id="specificTimeSelector" style="display:block;">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Chọn ngày:</label>
                        <input type="date" id="specificDate" onchange="updateCharts()" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    
                    <!-- Chọn tháng cụ thể -->
                    <div id="monthSelector" style="display:none;">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Chọn tháng:</label>
                        <input type="month" id="specificMonth" onchange="updateCharts()" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    
                    <!-- Chọn tuần cụ thể -->
                    <div id="weekSelector" style="display:none;">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Chọn tuần:</label>
                        <input type="week" id="specificWeek" onchange="updateCharts()" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    
                    <!-- Khoảng thời gian tùy chỉnh -->
                    <div id="rangeSelector" style="display:none;">
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Từ ngày:</label>
                            <input type="date" id="startDate" onchange="updateCharts()" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Đến ngày:</label>
                            <input type="date" id="endDate" onchange="updateCharts()" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                    </div>
                    
                    <div style="margin-top:20px;">
                        <button onclick="updateCharts()" class="filter-submit" style="background:#28a745;">Cập nhật biểu đồ</button>
                        <button onclick="resetDates()" class="filter-submit" style="background:#6c757d; margin-left:10px;">Đặt lại</button>
                    </div>
                </div>
            </div>
            
            <!-- Thống kê tổng quan -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:15px; margin-bottom:30px;">
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
                <div style="background:#f8f9fa; padding:20px; border-radius:8px; text-align:center;">
                    <h4>Tổng số người dùng</h4>
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'client'");
                    $stmt->execute();
                    $total_users = $stmt->get_result()->fetch_assoc()['total'];
                    ?>
                    <p style="font-size:24px; color:#dc3545; font-weight:bold;"><?php echo $total_users; ?></p>
                </div>
            </div>
            
            <!-- Biểu đồ giờ cao điểm đặt sân -->
            <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:20px;">
                <h4>Biểu đồ giờ cao điểm đặt sân</h4>
                <canvas id="peakHoursChart" style="max-height:300px;"></canvas>
            </div>
            
            <!-- Biểu đồ doanh thu theo thời gian -->
            <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:20px;">
                <h4>Biểu đồ doanh thu theo thời gian</h4>
                <canvas id="revenueChart" style="max-height:300px;"></canvas>
            </div>
            
            <!-- Biểu đồ sân được đặt nhiều nhất -->
            <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:20px;">
                <h4>Top sân được đặt nhiều nhất</h4>
                <canvas id="topCourtsChart" style="max-height:300px;"></canvas>
            </div>
            
            <!-- Biểu đồ sản phẩm bán chạy -->
            <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:20px;">
                <h4>Top sản phẩm bán chạy</h4>
                <canvas id="topProductsChart" style="max-height:300px;"></canvas>
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
<?php if ($section === 'dashboard'): ?>
    // Biến global để lưu các instance chart
    let dashboardTopCourtsChart, dashboardTopProductsChart, dashboardPeakHoursChart;

    // Thay đổi loại lọc thời gian cho Top Charts
    function changeChartsTimeFilter() {
        const filter = document.getElementById('chartsTimeFilter').value;
        const customRange = document.getElementById('chartsCustomRange');
        
        if (filter === 'custom') {
            customRange.style.display = 'block';
            setDefaultChartsRange();
        } else {
            customRange.style.display = 'none';
            updateTopCharts(); // Auto update khi chọn preset
        }
    }

    // Thiết lập khoảng thời gian mặc định cho tùy chỉnh
    function setDefaultChartsRange() {
        const today = new Date();
        const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
        
        document.getElementById('chartsStartDate').value = lastMonth.toISOString().split('T')[0];
        document.getElementById('chartsEndDate').value = today.toISOString().split('T')[0];
    }

    // Cập nhật chỉ 2 biểu đồ Top
    function updateTopCharts() {
        const filter = document.getElementById('chartsTimeFilter').value;
        let startDate, endDate, timeLabel;
        
        const today = new Date();
        
        switch(filter) {
            case '7days':
                const week = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                startDate = week.toISOString().split('T')[0];
                endDate = today.toISOString().split('T')[0];
                timeLabel = '7 ngày gần nhất';
                break;
            case '30days':
                const month = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                startDate = month.toISOString().split('T')[0];
                endDate = today.toISOString().split('T')[0];
                timeLabel = '30 ngày gần nhất';
                break;
            case 'this_week':
                const startOfWeek = new Date(today.getTime());
                startOfWeek.setDate(today.getDate() - today.getDay());
                startDate = startOfWeek.toISOString().split('T')[0];
                endDate = today.toISOString().split('T')[0];
                timeLabel = 'tuần này';
                break;
            case 'this_month':
                const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                startDate = startOfMonth.toISOString().split('T')[0];
                endDate = today.toISOString().split('T')[0];
                timeLabel = 'tháng này';
                break;
            case 'custom':
                startDate = document.getElementById('chartsStartDate').value;
                endDate = document.getElementById('chartsEndDate').value;
                if (!startDate || !endDate) {
                    alert('Vui lòng chọn đầy đủ ngày bắt đầu và kết thúc');
                    return;
                }
                timeLabel = `từ ${startDate} đến ${endDate}`;
                break;
            default:
                return;
        }
        
        // Cập nhật các title
        document.getElementById('topCourtsTitle').textContent = `Top 5 Sân đặt nhiều (${timeLabel})`;
        document.getElementById('topProductsTitle').textContent = `Top 5 Sản phẩm bán chạy (${timeLabel})`;
        
        // Cập nhật chỉ 2 biểu đồ
        updateSingleChart('top_courts', startDate, endDate, timeLabel);
        updateSingleChart('top_products', startDate, endDate, timeLabel);
    }

    // Cập nhật từng biểu đồ
    function updateSingleChart(type, startDate, endDate, timeLabel) {
        fetch(`ajax_dashboard_data.php?type=${type}&start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    alert('Lỗi lấy dữ liệu: ' + data.error);
                    return;
                }
                
                switch(type) {
                    case 'top_courts':
                        dashboardTopCourtsChart.data.labels = data.labels;
                        dashboardTopCourtsChart.data.datasets[0].data = data.data;
                        dashboardTopCourtsChart.update();
                        break;
                    case 'top_products':
                        dashboardTopProductsChart.data.labels = data.labels;
                        dashboardTopProductsChart.data.datasets[0].data = data.data;
                        dashboardTopProductsChart.update();
                        break;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Lỗi kết nối: ' + error.message);
            });
    }

    // Load dữ liệu thật cho biểu đồ giờ cao điểm
    function loadPeakHoursData() {
        // Lấy dữ liệu 30 ngày gần nhất mặc định
        const today = new Date();
        const month = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
        const startDate = month.toISOString().split('T')[0];
        const endDate = today.toISOString().split('T')[0];
        
        fetch(`ajax_dashboard_data.php?type=peak_hours&start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error loading peak hours:', data.error);
                    // Fallback về dữ liệu mẫu nếu có lỗi
                    dashboardPeakHoursChart.data.labels = ['6:00', '7:00', '8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'];
                    dashboardPeakHoursChart.data.datasets[0].data = [2, 3, 5, 4, 2, 1, 0, 1, 3, 6, 8, 12, 15, 18, 14, 9, 4];
                } else {
                    // Cập nhật với dữ liệu thật
                    dashboardPeakHoursChart.data.labels = data.labels;
                    dashboardPeakHoursChart.data.datasets[0].data = data.data;
                }
                dashboardPeakHoursChart.update();
            })
            .catch(error => {
                console.error('Fetch error for peak hours:', error);
                // Fallback về dữ liệu mẫu nếu có lỗi kết nối
                dashboardPeakHoursChart.data.labels = ['6:00', '7:00', '8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'];
                dashboardPeakHoursChart.data.datasets[0].data = [2, 3, 5, 4, 2, 1, 0, 1, 3, 6, 8, 12, 15, 18, 14, 9, 4];
                dashboardPeakHoursChart.update();
            });
    }
<?php endif; ?>

document.addEventListener('DOMContentLoaded', function() {
    <?php if ($section === 'dashboard'): ?>
        
        // Khởi tạo dashboard charts với dữ liệu mặc định
        function initDashboardCharts() {
            // Top Courts Chart
            const topCourtsCtx = document.getElementById('dashboardTopCourtsChart').getContext('2d');
            dashboardTopCourtsChart = new Chart(topCourtsCtx, {
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
                    responsive: true,
                    plugins: {
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Số lượt đặt'
                            }
                        },
                        x: {
                            ticks: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Top Products Chart
            const topProductsCtx = document.getElementById('dashboardTopProductsChart').getContext('2d');
            dashboardTopProductsChart = new Chart(topProductsCtx, {
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
                    responsive: true,
                    plugins: {
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Số lượng bán'
                            }
                        },
                        x: {
                            ticks: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Peak Hours Chart - Lấy dữ liệu thật từ database
            const peakHoursCtx = document.getElementById('dashboardPeakHoursChart').getContext('2d');
            
            // Tạo chart với dữ liệu rỗng trước, sẽ load sau
            dashboardPeakHoursChart = new Chart(peakHoursCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Số lượt đặt sân',
                        data: [],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffc107',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Giờ cao điểm đặt sân'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Số lượt đặt sân'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Giờ trong ngày'
                            }
                        }
                    }
                }
            });
        }


        // Khởi tạo biểu đồ khi trang load
        initDashboardCharts();
        
        // Load dữ liệu thật cho biểu đồ giờ cao điểm
        loadPeakHoursData();

    <?php endif; ?>
    
    <?php if ($section === 'stats'): ?>
        // Khởi tạo biểu đồ cho section stats
        let peakHoursChart, revenueChart, topCourtsChart, topProductsChart;
        
        // Thiết lập ngày mặc định
        function setDefaultDates() {
            const today = new Date();
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
            
            document.getElementById('startDate').value = lastMonth.toISOString().split('T')[0];
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
        }
        
        // Thay đổi loại lọc thời gian
        function changeTimeFilter() {
            const filter = document.getElementById('timeFilter').value;
            
            // Ẩn tất cả selector
            document.getElementById('specificTimeSelector').style.display = 'none';
            document.getElementById('monthSelector').style.display = 'none';
            document.getElementById('weekSelector').style.display = 'none';
            document.getElementById('rangeSelector').style.display = 'none';
            
            // Hiển thị selector tương ứng
            if (filter === 'specific') {
                document.getElementById('specificTimeSelector').style.display = 'block';
                setDefaultSpecificDate();
            } else if (filter === 'day') {
                document.getElementById('specificTimeSelector').style.display = 'block';
                setDefaultSpecificDate();
            } else if (filter === 'week') {
                document.getElementById('weekSelector').style.display = 'block';
                setDefaultWeek();
            } else if (filter === 'month') {
                document.getElementById('monthSelector').style.display = 'block';
                setDefaultMonth();
            } else if (filter === 'range') {
                document.getElementById('rangeSelector').style.display = 'block';
                setDefaultRange();
            }
            
            updateCharts();
        }
        
        // Thiết lập ngày mặc định cho specific
        function setDefaultSpecificDate() {
            const today = new Date();
            document.getElementById('specificDate').value = today.toISOString().split('T')[0];
        }
        
        // Thiết lập tuần mặc định
        function setDefaultWeek() {
            const today = new Date();
            const currentWeek = getWeekNumber(today);
            const year = today.getFullYear();
            document.getElementById('specificWeek').value = `${year}-W${currentWeek.toString().padStart(2, '0')}`;
        }
        
        // Thiết lập tháng mặc định
        function setDefaultMonth() {
            const today = new Date();
            const year = today.getFullYear();
            const month = (today.getMonth() + 1).toString().padStart(2, '0');
            document.getElementById('specificMonth').value = `${year}-${month}`;
        }
        
        // Thiết lập khoảng thời gian mặc định
        function setDefaultRange() {
            const today = new Date();
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
            document.getElementById('startDate').value = lastMonth.toISOString().split('T')[0];
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
        }
        
        // Lấy số tuần trong năm
        function getWeekNumber(date) {
            const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
            const pastDaysOfYear = (date - firstDayOfYear) / 86400000;
            return Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
        }
        
        // Đặt lại ngày
        function resetDates() {
            changeTimeFilter();
        }
        
        // Cập nhật tất cả biểu đồ
        function updateCharts() {
            const filter = document.getElementById('timeFilter').value;
            let startDate, endDate, timeLabel;
            
            if (filter === 'specific' || filter === 'day') {
                const specificDate = document.getElementById('specificDate').value;
                if (specificDate) {
                    startDate = specificDate;
                    endDate = specificDate;
                    timeLabel = `ngày ${specificDate}`;
                }
            } else if (filter === 'week') {
                const weekValue = document.getElementById('specificWeek').value;
                if (weekValue) {
                    const [year, week] = weekValue.split('-W');
                    const weekStart = getWeekStartDate(parseInt(year), parseInt(week));
                    const weekEnd = new Date(weekStart);
                    weekEnd.setDate(weekStart.getDate() + 6);
                    
                    startDate = weekStart.toISOString().split('T')[0];
                    endDate = weekEnd.toISOString().split('T')[0];
                    timeLabel = `tuần ${week} năm ${year}`;
                }
            } else if (filter === 'month') {
                const monthValue = document.getElementById('specificMonth').value;
                if (monthValue) {
                    const [year, month] = monthValue.split('-');
                    startDate = `${year}-${month}-01`;
                    const lastDay = new Date(parseInt(year), parseInt(month), 0);
                    endDate = `${year}-${month}-${lastDay.getDate()}`;
                    timeLabel = `tháng ${month}/${year}`;
                }
            } else if (filter === 'range') {
                startDate = document.getElementById('startDate').value;
                endDate = document.getElementById('endDate').value;
                if (startDate && endDate) {
                    timeLabel = `từ ${startDate} đến ${endDate}`;
                }
            }
            
            if (startDate && endDate) {
                // Cập nhật biểu đồ giờ cao điểm
                updatePeakHoursChart(startDate, endDate, timeLabel);
                
                // Cập nhật biểu đồ doanh thu
                updateRevenueChart(startDate, endDate, timeLabel);
                
                // Cập nhật biểu đồ top sân
                updateTopCourtsChart(startDate, endDate, timeLabel);
                
                // Cập nhật biểu đồ top sản phẩm
                updateTopProductsChart(startDate, endDate, timeLabel);
            }
        }
        
        // Lấy ngày đầu tuần
        function getWeekStartDate(year, week) {
            const firstDayOfYear = new Date(year, 0, 1);
            const firstWeekday = firstDayOfYear.getDay();
            const daysToAdd = (week - 1) * 7 - firstWeekday;
            const weekStart = new Date(year, 0, 1 + daysToAdd);
            return weekStart;
        }
        
        // Biểu đồ giờ cao điểm đặt sân
        function updatePeakHoursChart(startDate, endDate, timeLabel) {
            const ctx = document.getElementById('peakHoursChart').getContext('2d');
            
            if (peakHoursChart) {
                peakHoursChart.destroy();
            }
            
            // Dữ liệu mẫu - trong thực tế sẽ lấy từ AJAX
            const hours = ['6:00', '7:00', '8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'];
            const bookings = [5, 8, 12, 15, 10, 8, 6, 4, 8, 12, 18, 25, 30, 28, 22, 15, 8];
            
            peakHoursChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: hours,
                    datasets: [{
                        label: 'Số lượt đặt sân',
                        data: bookings,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#007bff',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: `Giờ cao điểm đặt sân ${timeLabel}`
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Số lượt đặt sân'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Giờ trong ngày'
                            }
                        }
                    }
                }
            });
        }
        
        // Biểu đồ doanh thu theo thời gian
        function updateRevenueChart(startDate, endDate, timeLabel) {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            if (revenueChart) {
                revenueChart.destroy();
            }
            
            // Dữ liệu mẫu - trong thực tế sẽ lấy từ AJAX
            const labels = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'];
            const revenue = [2500000, 3200000, 2800000, 3500000];
            
            revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: revenue,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: `Doanh thu ${timeLabel}`
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Doanh thu (VNĐ)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000000).toFixed(1) + 'M';
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Biểu đồ top sân được đặt nhiều nhất
        function updateTopCourtsChart(startDate, endDate, timeLabel) {
            const ctx = document.getElementById('topCourtsChart').getContext('2d');
            
            if (topCourtsChart) {
                topCourtsChart.destroy();
            }
            
            // Dữ liệu mẫu - trong thực tế sẽ lấy từ AJAX
            const courts = ['Sân 1', 'Sân 2', 'Sân 3', 'Sân 4', 'Sân 5'];
            const bookings = [45, 38, 32, 28, 25];
            
            topCourtsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: courts,
                    datasets: [{
                        label: 'Số lượt đặt',
                        data: bookings,
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#36A2EB',
                            '#4BC0C0',
                            '#9966FF'
                        ],
                        borderColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: `Top sân được đặt ${timeLabel}`
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Số lượt đặt'
                            }
                        }
                    }
                }
            });
        }
        
// Biểu đồ top sản phẩm bán chạy
function updateTopProductsChart(startDate, endDate, timeLabel) {
    const ctx = document.getElementById('topProductsChart').getContext('2d');
    
    if (!ctx) {
        console.error('Canvas "topProductsChart" not found');
        return;
    }
    
    if (topProductsChart) {
        topProductsChart.destroy();
    }
    
    // Sử dụng dữ liệu thực từ PHP
    const products = <?php echo json_encode(array_column($top_products, 'product_name')); ?> || [];
    const sales = <?php echo json_encode(array_column($top_products, 'total_quantity')); ?> || [];
    
    if (products.length === 0 || sales.length === 0) {
        console.warn('No data available for Top Products chart');
        return;
    }
    topProductsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: products,  // Giữ labels để tooltip hoạt động
        datasets: [{
            label: 'Số lượng bán',
            data: sales,
            backgroundColor: '#28a745',
            borderColor: '#28a745',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: `Top sản phẩm bán chạy ${timeLabel}`
            },
            legend: {
                display: false
            },
            tooltip: {
                enabled: true,
                callbacks: {
                    label: function(tooltipItem) {
                        return `${products[tooltipItem.dataIndex]}: ${tooltipItem.raw}`;
                    }
                }
            },
            datalabels: {             // ✅ Thêm cấu hình hiển thị số trên cột
                anchor: 'end',
                align: 'top',
                formatter: function(value) {
                    return value;
                },
                font: {
                    weight: 'bold'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Số lượng bán'
                }
            },
            x: {
                ticks: {
                    display: false  // Ẩn tên sản phẩm dưới cột
                }
            }
        }
    },
    plugins: [ChartDataLabels]   // ✅ Kích hoạt plugin datalabels
});

}
        
        // Khởi tạo biểu đồ khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            setDefaultSpecificDate();
            updateCharts();
        });
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