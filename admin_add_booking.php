<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'config/database.php';

$error = '';
$success = '';

// Lấy danh sách courts
$courts_stmt = $conn->prepare("SELECT court_id, court_name, description, price_per_hour FROM courts ORDER BY court_id");
$courts_stmt->execute();
$courts = $courts_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $court_id = $_POST['court_id'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $discount = floatval($_POST['discount'] ?? 0);
    
    // Validation
    if (empty($court_id) || empty($booking_date) || empty($start_time) || empty($end_time) || empty($payment_method) || empty($fullname) || empty($phone)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif ($start_time >= $end_time) {
        $error = 'Giờ kết thúc phải sau giờ bắt đầu.';
    } elseif ($booking_date < date('Y-m-d')) {
        $error = 'Không thể đặt sân cho ngày trong quá khứ.';
    } else {
        // Kiểm tra trùng lịch
        $conflict_stmt = $conn->prepare("
            SELECT booking_id FROM bookings 
            WHERE court_id = ? AND booking_date = ? 
            AND status IN ('pending', 'confirmed')
            AND NOT (end_time <= ? OR start_time >= ?)
        ");
        $conflict_stmt->bind_param("isss", $court_id, $booking_date, $start_time, $end_time);
        $conflict_stmt->execute();
        $conflict = $conflict_stmt->get_result();
        
        if ($conflict->num_rows > 0) {
            $error = 'Sân đã có người đặt trong khung giờ này.';
        } else {
            // Tính tổng tiền
            $court_stmt = $conn->prepare("SELECT price_per_hour FROM courts WHERE court_id = ?");
            $court_stmt->bind_param("i", $court_id);
            $court_stmt->execute();
            $court = $court_stmt->get_result()->fetch_assoc();
            
            if (!$court) {
                $error = 'Sân không tồn tại.';
            } else {
                // Tính số giờ
                $start_dt = new DateTime($booking_date . ' ' . $start_time);
                $end_dt = new DateTime($booking_date . ' ' . $end_time);
                $hours = ($end_dt->getTimestamp() - $start_dt->getTimestamp()) / 3600;
                
                $base_price = $court['price_per_hour'] * $hours;
                $discount_amount = $base_price * ($discount / 100);
                $total_price = $base_price - $discount_amount;
                
                // Tạo booking (user_id = admin_id để biết admin nào tạo)
                $admin_id = $_SESSION['user_id'];
                $booking_stmt = $conn->prepare("
                    INSERT INTO bookings (user_id, court_id, booking_date, start_time, end_time, 
                                        total_price, status, payment_method, fullname, phone, 
                                        discount, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $booking_stmt->bind_param("iisssdssssd", $admin_id, $court_id, $booking_date, 
                                        $start_time, $end_time, $total_price, $status, 
                                        $payment_method, $fullname, $phone, $discount);
                
                if ($booking_stmt->execute()) {
                    $booking_id = $conn->insert_id;
                    $success = "Tạo đặt sân #{$booking_id} thành công! Tổng tiền: " . number_format($total_price, 0, ',', '.') . " VNĐ";
                    
                    // Reset form
                    $court_id = $booking_date = $start_time = $end_time = $payment_method = $fullname = $phone = '';
                    $discount = 0;
                } else {
                    $error = 'Có lỗi xảy ra khi tạo đặt sân: ' . $conn->error;
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<h2 class="section-title">Thêm Đặt Sân Mới</h2>

<div class="shop-container" style="max-width: 700px; margin: 0 auto; padding: 20px;">
    <div class="admin-content" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        
        <div style="margin-bottom: 20px;">
            <a href="admin.php?section=bookings" style="color: #007bff; text-decoration: none;">
                ← Quay lại Quản lý Đặt sân
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

        <form method="POST" action="admin_add_booking.php" id="bookingForm">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Chọn sân <span style="color: red;">*</span>
                </label>
                <select name="court_id" id="courtSelect" onchange="fetchBookedSlots(); calculatePrice()" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="">-- Chọn sân --</option>
                    <?php foreach ($courts as $court): ?>
                        <option value="<?php echo $court['court_id']; ?>" 
                                data-price="<?php echo $court['price_per_hour']; ?>"
                                <?php echo (isset($court_id) && $court_id == $court['court_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($court['court_name']) . ' - ' . number_format($court['price_per_hour'], 0, ',', '.') . ' VNĐ/giờ'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Ngày đặt <span style="color: red;">*</span>
                    </label>
                    <input type="date" name="booking_date" id="booking_date" value="<?php echo htmlspecialchars($booking_date ?? ''); ?>" 
                           onchange="fetchBookedSlots(); calculatePrice()" required min="<?php echo date('Y-m-d'); ?>"
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Giờ bắt đầu <span style="color: red;">*</span>
                    </label>
                    <select name="start_time" id="start_time" onchange="enforceEndMin(); calculatePrice()" required 
                            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <?php
                        $start = strtotime("06:00");
                        $end = strtotime("22:00");
                        for ($time = $start; $time <= $end; $time += 1800) {
                            $time_str = date("H:i", $time);
                            $selected = (isset($start_time) && $start_time == $time_str) ? 'selected' : '';
                            echo '<option value="' . $time_str . '" ' . $selected . '>' . $time_str . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Giờ kết thúc <span style="color: red;">*</span>
                    </label>
                    <select name="end_time" id="end_time" onchange="calculatePrice()" required 
                            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <?php
                        for ($time = $start + 1800; $time <= $end + 1800; $time += 1800) {
                            $time_str = date("H:i", $time);
                            $selected = (isset($end_time) && $end_time == $time_str) ? 'selected' : '';
                            echo '<option value="' . $time_str . '" ' . $selected . '>' . $time_str . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Họ tên <span style="color: red;">*</span>
                    </label>
                    <input type="text" name="fullname" id="fullnameInput" value="<?php echo htmlspecialchars($fullname ?? ''); ?>" 
                           required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Số điện thoại <span style="color: red;">*</span>
                    </label>
                    <input type="tel" name="phone" id="phoneInput" value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                           required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Phương thức thanh toán <span style="color: red;">*</span>
                    </label>
                    <select name="payment_method" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">-- Chọn phương thức --</option>
                        <option value="ondelivery" <?php echo (isset($payment_method) && $payment_method === 'ondelivery') ? 'selected' : ''; ?>>Thanh toán sau (ondelivery)</option>
                        <option value="prepaid" <?php echo (isset($payment_method) && $payment_method === 'prepaid') ? 'selected' : ''; ?>>Chuyển khoản trước (prepaid)</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Trạng thái <span style="color: red;">*</span>
                    </label>
                    <select name="status" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="pending" <?php echo (isset($status) && $status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo (isset($status) && $status === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Giảm giá (%)
                    </label>
                    <input type="number" name="discount" value="<?php echo htmlspecialchars($discount ?? 0); ?>" 
                           min="0" max="100" step="0.1" onchange="calculatePrice()"
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                <div id="priceCalculation" style="color: #333; font-size: 16px;">
                    <div>Giá gốc: <span id="basePrice">0</span> VNĐ</div>
                    <div>Giảm giá: <span id="discountAmount">0</span> VNĐ</div>
                    <h4 style="margin: 10px 0 0 0; color: #dc3545;">Tổng tiền: <span id="totalPrice">0</span> VNĐ</h4>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #eee;">
                <a href="admin.php?section=bookings" 
                   style="padding: 12px 24px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: 600;">
                    Hủy
                </a>
                <button type="submit" 
                        style="padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">
                    Tạo Đặt Sân
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Utility functions
function toMinutes(hhmm) {
    const [h, m] = hhmm.split(':').map(Number);
    return h * 60 + m;
}

function toHHMM(minutes) {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
}

function next30SlotNow() {
    const now = new Date();
    const minutes = now.getHours() * 60 + now.getMinutes();
    const rounded = Math.ceil(minutes / 30) * 30;
    return toHHMM(rounded);
}

// Enforce minimum 30 minutes duration
function enforceEndMin() {
    const startSelect = document.getElementById('start_time');
    const endSelect = document.getElementById('end_time');
    const startVal = startSelect.value;
    
    if (!startVal) return;
    
    const minEndMin = toMinutes(startVal) + 30;
    
    // Enable/disable end time options
    for (let opt of endSelect.options) {
        opt.disabled = toMinutes(opt.value) < minEndMin;
    }
    
    // Auto-select first valid end time if current is invalid
    if (toMinutes(endSelect.value) < minEndMin) {
        for (let opt of endSelect.options) {
            if (!opt.disabled) { 
                endSelect.value = opt.value; 
                break; 
            }
        }
    }
}

// Fetch booked slots and disable them
function fetchBookedSlots() {
    const dateInput = document.getElementById('booking_date');
    const courtSelect = document.getElementById('courtSelect');
    const startSelect = document.getElementById('start_time');
    const endSelect = document.getElementById('end_time');
    
    const date = dateInput.value;
    const court = courtSelect.value;
    
    if (!date || !court) return;

    fetch(`get_booked_slots.php?date=${date}&court=${court}`)
        .then(res => res.json())
        .then(data => {
            // Reset all options
            for (let opt of startSelect.options) opt.disabled = false;
            for (let opt of endSelect.options) opt.disabled = false;

            // Disable booked slots
            data.forEach(slot => {
                const slotStartMin = toMinutes(slot.start_time);
                const slotEndMin = toMinutes(slot.end_time);
                
                // Disable start times that fall within booked slots
                for (let opt of startSelect.options) {
                    const optMin = toMinutes(opt.value);
                    if (optMin >= slotStartMin && optMin < slotEndMin) {
                        opt.disabled = true;
                    }
                }
                
                // Disable end times that fall within booked slots
                for (let opt of endSelect.options) {
                    const optMin = toMinutes(opt.value);
                    if (optMin > slotStartMin && optMin < slotEndMin) {
                        opt.disabled = true;
                    }
                }
            });

            // Disable past times for today
            const today = new Date();
            const todayISO = today.toISOString().slice(0,10);
            if (date === todayISO) {
                const minStart = next30SlotNow();
                for (let opt of startSelect.options) {
                    if (opt.value < minStart) opt.disabled = true;
                }
                for (let opt of endSelect.options) {
                    if (opt.value <= minStart) opt.disabled = true;
                }
            }

            // Re-apply end time validation
            enforceEndMin();
        })
        .catch(() => {
            console.error('Không thể tải khung giờ đã đặt');
        });
}

// Calculate price
function calculatePrice() {
    const courtSelect = document.getElementById('courtSelect');
    const startSelect = document.getElementById('start_time');
    const endSelect = document.getElementById('end_time');
    const discount = parseFloat(document.querySelector('input[name="discount"]').value) || 0;
    
    const selectedCourt = courtSelect.selectedOptions[0];
    const startTime = startSelect.value;
    const endTime = endSelect.value;
    
    if (selectedCourt && selectedCourt.value && startTime && endTime) {
        const pricePerHour = parseFloat(selectedCourt.getAttribute('data-price'));
        
        // Calculate hours
        const startMin = toMinutes(startTime);
        const endMin = toMinutes(endTime);
        
        if (endMin > startMin) {
            const hours = (endMin - startMin) / 60;
            const basePrice = pricePerHour * hours;
            const discountAmount = basePrice * (discount / 100);
            const totalPrice = basePrice - discountAmount;
            
            document.getElementById('basePrice').textContent = basePrice.toLocaleString('vi-VN');
            document.getElementById('discountAmount').textContent = discountAmount.toLocaleString('vi-VN');
            document.getElementById('totalPrice').textContent = totalPrice.toLocaleString('vi-VN');
        } else {
            resetPriceDisplay();
        }
    } else {
        resetPriceDisplay();
    }
}

function resetPriceDisplay() {
    document.getElementById('basePrice').textContent = '0';
    document.getElementById('discountAmount').textContent = '0';
    document.getElementById('totalPrice').textContent = '0';
}

// Form validation
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const startSelect = document.getElementById('start_time');
    const endSelect = document.getElementById('end_time');
    const dateInput = document.getElementById('booking_date');
    
    const startTime = startSelect.value;
    const endTime = endSelect.value;
    const selectedDate = dateInput.value;
    const todayISO = new Date().toISOString().slice(0,10);
    
    // Check minimum duration
    if (toMinutes(endTime) - toMinutes(startTime) < 30) {
        e.preventDefault();
        alert('Thời lượng tối thiểu là 30 phút!');
        return;
    }
    
    // Check if booking today and time is valid
    if (selectedDate === todayISO) {
        const minStart = next30SlotNow();
        if (toMinutes(startTime) < toMinutes(minStart)) {
            e.preventDefault();
            alert('Giờ bắt đầu phải từ khung 30 phút kế tiếp hôm nay!');
            return;
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('booking_date');
    
    // Set default date to today if empty
    if (!dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Load booked slots if date and court are already selected
    if (dateInput.value && document.getElementById('courtSelect').value) {
        fetchBookedSlots();
    }
});
</script>

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
    
    div[style*="grid-template-columns: 1fr 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
