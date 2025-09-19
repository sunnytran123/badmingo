<?php
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

$success_message = '';
$error_message = '';

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $date = $_POST['date'] ?? '';
    $court = intval($_POST['court'] ?? 0);
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'ondelivery';

    if (!$user_id || !$date || !$court || !$start_time || !$end_time || !$fullname || !$phone) {
        $error_message = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        $now = new DateTime('now');
        $bookingDateTime = new DateTime("$date $start_time");
        if ($bookingDateTime < $now) {
            $error_message = 'Không thể đặt sân cho thời gian đã qua!';
        } else {
            // Ràng buộc tối thiểu
            $today = date('Y-m-d');
            if ($date === $today) {
                $nowMinutes = intval(date('H')) * 60 + intval(date('i'));
                $minStartMinutes = (int)(ceil($nowMinutes / 30) * 30);
                $minStartHour = floor($minStartMinutes / 60);
                $minStartMin = $minStartMinutes % 60;
                $minStartStr = sprintf('%02d:%02d', $minStartHour, $minStartMin);
                if (strtotime($start_time) < strtotime($minStartStr)) {
                    $error_message = 'Giờ bắt đầu phải từ khung tiếp theo trong hôm nay!';
                }
            }
            if (!$error_message) {
                $minDurationMinutes = 30;
                $actualDurationMinutes = (strtotime($end_time) - strtotime($start_time)) / 60;
                if ($actualDurationMinutes < $minDurationMinutes) {
                    $error_message = 'Thời lượng tối thiểu là 30 phút!';
                }
            }
            if (!$error_message) {
                // Kiểm tra trùng giờ đã đặt
                $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE booking_date = ? AND court_id = ? AND status != 'cancelled' AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))");
                $stmt->bind_param("sissss", $date, $court, $end_time, $start_time, $start_time, $end_time);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();

                if ($count > 0) {
                    $error_message = 'Khung giờ này đã có người đặt!';
                } else {
                    // Lấy giá sân
                    $stmt = $conn->prepare("SELECT price_per_hour FROM courts WHERE court_id = ?");
                    $stmt->bind_param("i", $court);
                    $stmt->execute();
                    $stmt->bind_result($price_per_hour);
                    $stmt->fetch();
                    $stmt->close();

                    $duration = (strtotime($end_time) - strtotime($start_time)) / 3600;
                    $total_price = $price_per_hour * $duration;
                    $discount = ($payment_method == 'prepaid') ? 10 : 0;
                    if ($discount) $total_price = $total_price * (1 - $discount / 100);

                    // Lưu vào bookings
                    $stmt = $conn->prepare("INSERT INTO bookings (user_id, court_id, booking_date, start_time, end_time, payment_method, total_price, discount, status, fullname, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
                    $stmt->bind_param("iissssddss", $user_id, $court, $date, $start_time, $end_time, $payment_method, $total_price, $discount, $fullname, $phone);
                    $stmt->execute();

                                    if ($stmt->affected_rows > 0) {
                    header('Location: booking.php');
                    exit();
                } else {
                    $error_message = 'Lỗi đặt sân!';
                }
                }
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<h2 class="section-title">Đặt sân cầu lông</h2>

<div class="shop-intro">
    <img src="images/Olypic.png" alt="Đặt sân cầu lông">
    <div class="shop-intro-content">
        <h2>Chào mừng đến với Sunny Sport</h2>
        <p style="text-align:justify">Hệ thống sân cầu lông hiện đại với 10 sân tiêu chuẩn, trang thiết bị chuyên nghiệp. Đặt sân nhanh chóng, thanh toán linh hoạt và được giảm giá 10% khi thanh toán trước!</p>
    </div>
</div>

<div class="shop-container">
    <form action="" method="POST" class="product-filter" id="bookingForm">
        <!-- Modal container -->
        <div id="modal-overlay" class="modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
            <div id="modal" class="modal" role="document">
                <div class="modal-header">
                    <div id="modal-icon" class="modal-icon" aria-hidden="true">!</div>
                    <div id="modal-title" class="modal-title">Thông báo</div>
                </div>
                <div id="modal-body" class="modal-body"></div>
                <div class="modal-actions">
                    <button id="modal-ok" class="btn btn-primary">OK</button>
                </div>
            </div>
        </div>
        <?php if ($success_message || $error_message): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const msg = <?php echo json_encode($success_message ?: $error_message); ?>;
                    const type = <?php echo json_encode($success_message ? 'success' : 'error'); ?>;
                    if (msg) openModal(msg, type);
                });
            </script>
        <?php endif; ?>
        <div class="filter-section">
            <h3>Chọn ngày</h3>
            <div class="filter-group">
                <input type="date" id="date" name="date" required 
                       min="<?php echo date('Y-m-d'); ?>" 
                       max="<?php echo date('Y-m-d', strtotime('+2 months')); ?>"
                       class="mt-1 block w-full">
            </div>
        </div>

        <div class="filter-section">
            <h3>Chọn sân</h3>
            <div class="filter-group">
                <select id="court" name="court" required class="mt-1 block w-full">
                    <option value="1">Sân 1</option>
                    <option value="2">Sân 2</option>
                    <option value="3">Sân 3</option>
                    <option value="4">Sân 4</option>
                    <option value="5">Sân 5</option>
                </select>
            </div>
        </div>

        <div class="filter-section">
            <h3>Chọn khung giờ</h3>
            <div class="filter-group grid">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-600">Giờ bắt đầu</label>
                    <select id="start_time" name="start_time" required class="mt-1 block w-full">
                        <?php
                        $start = strtotime("06:00");
                        $end = strtotime("22:00");
                        for ($time = $start; $time <= $end; $time += 1800) {
                            echo '<option value="' . date("H:i", $time) . '">' . date("H:i", $time) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-600">Giờ kết thúc</label>
                    <select id="end_time" name="end_time" required class="mt-1 block w-full">
                        <?php
                        for ($time = $start + 1800; $time <= $end + 1800; $time += 1800) {
                            echo '<option value="' . date("H:i", $time) . '">' . date("H:i", $time) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="filter-section">
            <h3>Họ và tên</h3>
            <div class="filter-group">
                <input type="text" id="fullname" name="fullname" required class="mt-1 block w-full">
            </div>
        </div>

        <div class="filter-section">
            <h3>Số điện thoại</h3>
            <div class="filter-group">
                <input type="tel" id="phone" name="phone" required class="mt-1 block w-full">
            </div>
        </div>

        <div class="filter-section">
            <h3>Phương thức thanh toán</h3>
            <div class="filter-group">
                <label class="filter-checkbox">
                    <input type="radio" name="payment_method" value="prepaid" required>
                    Thanh toán trước (Giảm 10%)
                </label>
                <label class="filter-checkbox">
                    <input type="radio" name="payment_method" value="ondelivery">
                    Thanh toán sau khi đánh
                </label>
            </div>
        </div>

        <div class="filter-section">
            <p class="note">*Lưu ý: Chọn thanh toán trước để được giảm 10% chi phí đặt sân. Thanh toán sau khi đánh sẽ áp dụng giá tiêu chuẩn.</p>
        </div>

        <button type="submit" class="filter-submit">Đặt Sân</button>
    </form>
</div>

<style>

.section-title { 
    font-size: 24px; 
    font-weight: 700; 
    color: #007bff; 
    margin-bottom: 20px; 
    display: flex; 
    align-items: center; 
    gap: 10px; 
}
.shop-intro { 
    display: flex; 
    align-items: center; 
    gap: 20px; 
    margin-bottom: 40px; 
    max-height: 250px; 
    overflow: hidden; 
}
.shop-intro img { 
    width: 250px; 
    object-fit: cover; 
    border-radius: 8px; 
}
.shop-intro-content { 
    flex: 1; 
}
.shop-container { 
    display: flex; 
    flex-direction: row; 
    gap: 20px; 
    align-items: flex-start; 
    max-width: 100%;
}
.product-filter { 
    background: white; 
    padding: 20px; 
    border-radius: 8px; 
    box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
    width: 100%; 
    min-height: 300px; 
    overflow-y: auto; 
}
.filter-section { 
    display: flex; 
    flex-direction: column; 
    gap: 10px; 
    margin-bottom: 20px; 
}
.filter-section h3 { 
    font-size: 16px; 
    margin-bottom: 5px; 
    color: #333; 
}
.filter-group { 
    display: flex; 
    flex-direction: column; 
    gap: 8px; 
}
.filter-group.grid { 
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 15px; 
}
.filter-checkbox { 
    display: flex; 
    align-items: center; 
    gap: 8px; 
    font-size: 14px; 
    color: #333; 
}
.filter-checkbox input { 
    width: 16px; 
    height: 16px; 
    cursor: pointer; 
}
.filter-group input, .filter-group select { 
    padding: 8px; 
    border: 1px solid #D1D5DB; 
    border-radius: 6px; 
    font-size: 14px; 
    transition: border-color 0.3s ease, box-shadow 0.3s ease; 
}
.filter-group input:focus, .filter-group select:focus { 
    outline: none; 
    border-color: #4F46E5; 
    box-shadow: 0 0 0 3px rgba(79,70,229,0.25); 
}
.filter-submit { 
    background: linear-gradient(135deg, #4F46E5 0%, #5B21B6 100%); 
    color: white; 
    padding: 8px 15px; 
    border: none; 
    border-radius: 6px; 
    font-weight: 600; 
    cursor: pointer; 
    transition: background 0.3s ease, transform 0.3s ease; 
    width: 100%; 
}
.filter-submit:hover { 
    background: linear-gradient(135deg, #4338CA 0%, #4C1D95 100%); 
    transform: translateY(-1px); 
}
.note { 
    font-size: 12px; 
    color: #333; 
    font-style: italic; 
}
.modal-overlay { 
    position: fixed; 
    top: 0; 
    left: 0; 
    right: 0; 
    bottom: 0; 
    background: transparent; 
    display: none; 
    align-items: center; 
    justify-content: center; 
    z-index: 10000; 
}
.modal { 
    width: calc(100% - 32px); 
    max-width: 420px; 
    background: white; 
    border-radius: 8px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
    padding: 20px; 
    animation: modalIn 0.22s ease-out; 
}
.modal-header { 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    gap: 10px; 
    padding-bottom: 10px; 
    border-bottom: 1px solid #f8f9fa; 
}
.modal-title { 
    font-size: 18px; 
    font-weight: 700; 
    color: #333; 
}
.modal-icon { 
    width: 28px; 
    height: 28px; 
    border-radius: 999px; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 16px; 
}
.modal-body { 
    margin-top: 10px; 
    font-size: 14px; 
    color: #333; 
    line-height: 1.6; 
    text-align: center; 
}
.modal-actions { 
    margin-top: 16px; 
    display: flex; 
    justify-content: center; 
    gap: 10px; 
}
.btn { 
    padding: 8px 15px; 
    border-radius: 6px; 
    font-size: 14px; 
    cursor: pointer; 
    border: none; 
    transition: background 0.3s ease, transform 0.3s ease; 
}
.btn-primary { 
    background: linear-gradient(135deg, #4F46E5 0%, #5B21B6 100%); 
    color: #fff; 
}
.btn-primary:hover { 
    background: linear-gradient(135deg, #4338CA 0%, #4C1D95 100%); 
    transform: translateY(-1px); 
}
.modal-success .modal-icon { background: #28a745; color: #fff; }
.modal-error .modal-icon { background: #dc3545; color: #fff; }
.modal-success .modal-title { color: #28a745; }
.modal-error .modal-title { color: #dc3545; }
@keyframes modalIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 768px) {
    .shop-container { flex-direction: column; }
    .product-filter { width: 100%; min-height: auto; }
    .filter-group.grid { grid-template-columns: 1fr; }
}
</style>

<script>
const overlay = document.getElementById('modal-overlay');
const modal = document.getElementById('modal');
const modalTitle = document.getElementById('modal-title');
const modalBody = document.getElementById('modal-body');
const modalIcon = document.getElementById('modal-icon');
const modalOk = document.getElementById('modal-ok');

function openModal(message, type = 'info') {
    modal.classList.remove('modal-success', 'modal-error');
    if (type === 'success') { modal.classList.add('modal-success'); modalIcon.textContent = '✔'; }
    else if (type === 'error') { modal.classList.add('modal-error'); modalIcon.textContent = '!'; }
    else { modalIcon.textContent = 'i'; }
    modalTitle.textContent = type === 'success' ? 'Thành công' : (type === 'error' ? 'Thông báo' : 'Thông báo');
    modalBody.textContent = message;
    overlay.style.display = 'flex';
    overlay.setAttribute('aria-hidden', 'false');
}
function closeModal() {
    overlay.style.display = 'none';
    overlay.setAttribute('aria-hidden', 'true');
}
modalOk.addEventListener('click', closeModal);
overlay.addEventListener('click', function(e){ if (e.target === overlay) closeModal(); });
document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeModal(); });

const startSelect = document.getElementById('start_time');
const endSelect = document.getElementById('end_time');
const dateInput = document.getElementById('date');

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
function enforceEndMin() {
    const startVal = startSelect.value;
    if (!startVal) return;
    const minEndMin = toMinutes(startVal) + 30;
    for (let opt of endSelect.options) {
        opt.disabled = toMinutes(opt.value) < minEndMin;
    }
    if (toMinutes(endSelect.value) < minEndMin) {
        for (let opt of endSelect.options) {
            if (!opt.disabled) { endSelect.value = opt.value; break; }
        }
    }
}

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const startTime = startSelect.value;
    const endTime = endSelect.value;
    const selectedDate = dateInput.value;
    const todayISO = new Date().toISOString().slice(0,10);
    if (toMinutes(endTime) - toMinutes(startTime) < 30) {
        e.preventDefault();
        openModal('Thời lượng tối thiểu là 30 phút!', 'error');
        return;
    }
    if (selectedDate === todayISO) {
        const minStart = next30SlotNow();
        if (toMinutes(startTime) < toMinutes(minStart)) {
            e.preventDefault();
            openModal('Giờ bắt đầu phải từ khung 30 phút kế tiếp hôm nay!', 'error');
            return;
        }
    }
});

dateInput.addEventListener('change', fetchBookedSlots);
document.getElementById('court').addEventListener('change', fetchBookedSlots);
startSelect.addEventListener('change', function(){ enforceEndMin(); });

function fetchBookedSlots() {
    const date = dateInput.value;
    const court = document.getElementById('court').value;
    if (!date || !court) return;

    fetch(`get_booked_slots.php?date=${date}&court=${court}`)
        .then(res => res.json())
        .then(data => {
            for (let opt of startSelect.options) opt.disabled = false;
            for (let opt of endSelect.options) opt.disabled = false;

            data.forEach(slot => {
                for (let opt of startSelect.options) {
                    if (opt.value >= slot.start_time && opt.value < slot.end_time) {
                        opt.disabled = true;
                    }
                }
                for (let opt of endSelect.options) {
                    if (opt.value > slot.start_time && opt.value <= slot.end_time) {
                        opt.disabled = true;
                    }
                }
            });

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

            enforceEndMin();
        })
        .catch(() => {
            openModal('Không thể tải khung giờ đã đặt. Vui lòng thử lại!', 'error');
        });
}
</script>

<?php include 'includes/footer.php'; ?>