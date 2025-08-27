<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
include 'includes/header.php';
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
                    $success_message = 'Đặt sân thành công! Cảm ơn bạn đã sử dụng dịch vụ Sunny Sport.';
                } else {
                    $error_message = 'Lỗi đặt sân!';
                }
            }
        }
    }
}
?>

<style>
.alert {
    padding: 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 16px;
}
.alert-success {
    background: #e6ffed;
    color: #256029;
    border: 1px solid #b7eb8f;
}
.alert-danger {
    background: #fff1f0;
    color: #a8071a;
    border: 1px solid #ffa39e;
}
/* Modal popup */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.45);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}
.modal {
    width: calc(100% - 32px);
    max-width: 420px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
    padding: 18px 18px 16px 18px;
    animation: modalIn 0.22s ease-out;
}
.modal-header { display: flex; align-items: center; justify-content: center; gap: 10px; }
.modal-title { font-size: 18px; font-weight: 700; color: #111827; text-align: center; }
.modal-close { margin-left: auto; background: transparent; border: none; font-size: 20px; cursor: pointer; color: #6B7280; }
.modal-body { margin-top: 6px; font-size: 14px; color: #374151; }
.modal-actions { margin-top: 14px; display: flex; justify-content: flex-end; gap: 10px; }
.btn { padding: 8px 12px; border-radius: 8px; font-size: 14px; cursor: pointer; border: 1px solid transparent; }
.btn-primary { background: #4F46E5; color: #fff; }
.btn-primary:hover { background: #4338CA; }
.btn-outline { background: #fff; color: #374151; border-color: #D1D5DB; }
.btn-outline:hover { background: #F9FAFB; }
.modal-success .modal-header { color: #065F46; }
.modal-error .modal-header { color: #991B1B; }
@keyframes modalIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
</style>

<section class="min-h-screen bg-gray-100 py-12">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Đặt Sân Cầu Lông</h2>
        <!-- Modal container -->
        <div id="modal-overlay" class="modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
            <div id="modal" class="modal" role="document">
                <div class="modal-header">
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
        <form action="" method="POST" class="space-y-6" id="bookingForm">
            <!-- Chọn ngày -->
            <div class="form-group">
                <label for="date" class="block text-sm font-medium text-gray-700">Chọn ngày</label>
                <input type="date" id="date" name="date" required 
                       min="<?php echo date('Y-m-d'); ?>" 
                       max="<?php echo date('Y-m-d', strtotime('+2 months')); ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
            </div>

            <!-- Chọn sân -->
            <div class="form-group">
                <label for="court" class="block text-sm font-medium text-gray-700">Chọn sân</label>
                <select id="court" name="court" required 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                    <option value="1">Sân 1</option>
                    <option value="2">Sân 2</option>
                    <option value="3">Sân 3</option>
                    <option value="4">Sân 4</option>
                </select>
            </div>

            <!-- Chọn khung giờ -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700">Chọn khung giờ</label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-600">Giờ bắt đầu</label>
                        <select id="start_time" name="start_time" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                            <?php
                            $start = strtotime("06:00");
                            $end = strtotime("22:00");
                            for ($time = $start; $time <= $end; $time += 1800) { // 30 phút
                                echo '<option value="' . date("H:i", $time) . '">' . date("H:i", $time) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-600">Giờ kết thúc</label>
                        <select id="end_time" name="end_time" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                            <?php
                            for ($time = $start + 1800; $time <= $end + 1800; $time += 1800) { // Bắt đầu từ 30 phút sau giờ bắt đầu
                                echo '<option value="' . date("H:i", $time) . '">' . date("H:i", $time) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Họ và tên -->
            <div class="form-group">
                <label for="fullname" class="block text-sm font-medium text-gray-700">Họ và tên</label>
                <input type="text" id="fullname" name="fullname" required 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
            </div>

            <!-- Số điện thoại -->
            <div class="form-group">
                <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                <input type="tel" id="phone" name="phone" required 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
            </div>

            <!-- Phương thức thanh toán -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700">Phương thức thanh toán</label>
                <div class="mt-2 space-y-2">
                    <div class="flex items-center">
                        <input id="payment_prepaid" name="payment_method" type="radio" value="prepaid" required 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        <label for="payment_prepaid" class="ml-2 block text-sm text-gray-900">
                            Thanh toán trước (Giảm 10%)
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input id="payment_ondelivery" name="payment_method" type="radio" value="ondelivery" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        <label for="payment_ondelivery" class="ml-2 block text-sm text-gray-900">
                            Thanh toán sau khi đánh
                        </label>
                    </div>
                </div>
            </div>

            <!-- Ghi chú về giảm giá -->
            <div class="form-group">
                <p class="text-sm text-gray-600 italic">
                    *Lưu ý: Chọn thanh toán trước để được giảm 10% chi phí đặt sân. Thanh toán sau khi đánh sẽ áp dụng giá tiêu chuẩn.
                </p>
            </div>

            <!-- Nút đặt sân -->
            <div class="text-center">
                <button type="submit" 
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Đặt Sân
                </button>
            </div>
        </form>

        <!-- Script kiểm tra thời gian & modal popup -->
        <script>
            const overlay = document.getElementById('modal-overlay');
            const modal = document.getElementById('modal');
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');
            // const modalClose = document.getElementById('modal-close');
            const modalOk = document.getElementById('modal-ok');

            function openModal(message, type = 'info') {
                modal.classList.remove('modal-success', 'modal-error');
                if (type === 'success') modal.classList.add('modal-success');
                if (type === 'error') modal.classList.add('modal-error');
                modalTitle.textContent = type === 'success' ? 'Thành công' : (type === 'error' ? 'Thông báo' : 'Thông báo');
                modalBody.textContent = message;
                overlay.style.display = 'flex';
                overlay.style.alignItems = 'center';
                overlay.style.justifyContent = 'center';
                overlay.setAttribute('aria-hidden', 'false');
            }
            function closeModal() {
                overlay.style.display = 'none';
                overlay.setAttribute('aria-hidden', 'true');
            }
            // modalClose.addEventListener('click', closeModal);
            modalOk.addEventListener('click', closeModal);
            overlay.addEventListener('click', function(e){ if (e.target === overlay) closeModal(); });
            document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeModal(); });

            document.getElementById('bookingForm').addEventListener('submit', function(e) {
                const startTime = document.getElementById('start_time').value;
                const endTime = document.getElementById('end_time').value;
                if (startTime >= endTime) {
                    e.preventDefault();
                    openModal('Giờ kết thúc phải sau giờ bắt đầu!', 'error');
                }
            });

            document.getElementById('date').addEventListener('change', fetchBookedSlots);
            document.getElementById('court').addEventListener('change', fetchBookedSlots);

            function fetchBookedSlots() {
                const date = document.getElementById('date').value;
                const court = document.getElementById('court').value;
                if (!date || !court) return;

                fetch(`get_booked_slots.php?date=${date}&court=${court}`)
                    .then(res => res.json())
                    .then(data => {
                        const startSelect = document.getElementById('start_time');
                        const endSelect = document.getElementById('end_time');
                        // Enable all first
                        for (let opt of startSelect.options) opt.disabled = false;
                        for (let opt of endSelect.options) opt.disabled = false;
                        // Disable booked slots
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

                        // Disable giờ đã qua nếu là hôm nay
                        const selectedDate = document.getElementById('date').value;
                        const today = new Date();
                        const nowHour = today.getHours();
                        const nowMinute = today.getMinutes();
                        const nowTime = ("0" + nowHour).slice(-2) + ":" + ("0" + nowMinute).slice(-2);

                        if (selectedDate === today.toISOString().slice(0,10)) {
                            for (let opt of startSelect.options) {
                                if (opt.value < nowTime) opt.disabled = true;
                            }
                            for (let opt of endSelect.options) {
                                if (opt.value <= nowTime) opt.disabled = true;
                            }
                        }
                    })
                    .catch(() => {
                        openModal('Không thể tải khung giờ đã đặt. Vui lòng thử lại!', 'error');
                    });
            }
        </script>
    </div>
</section>

<?php include 'includes/footer.php'; ?>