<?php include 'includes/header.php'; ?>

<section class="min-h-screen bg-gray-100 py-12">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Đặt Sân Cầu Lông</h2>
        <form action="process_booking.php" method="POST" class="space-y-6" id="bookingForm">
            <!-- Chọn ngày -->
            <div class="form-group">
                <label for="date" class="block text-sm font-medium text-gray-700">Chọn ngày</label>
                <input type="date" id="date" name="date" required 
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

        <!-- Script kiểm tra thời gian -->
        <script>
            document.getElementById('bookingForm').addEventListener('submit', function(e) {
                const startTime = document.getElementById('start_time').value;
                const endTime = document.getElementById('end_time').value;
                if (startTime >= endTime) {
                    e.preventDefault();
                    alert('Giờ kết thúc phải sau giờ bắt đầu!');
                }
            });
        </script>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
