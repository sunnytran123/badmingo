<?php include 'includes/header.php'; ?>

<h2 class="section-title">Đặt sân cầu lông</h2>
<div class="booking-form">
    <form action="process_booking.php" method="POST">
        <div class="form-group">
            <label>Chọn ngày:</label>
            <input type="date" name="date" required>
        </div>
        <div class="form-group">
            <label>Chọn sân:</label>
            <select name="court">
                <option>Sân 1</option>
                <option>Sân 2</option>
            </select>
        </div>
        <div class="form-group">
            <label>Chọn khung giờ:</label>
            <select name="time">
                <option>06:00 - 08:00</option>
                <option>08:00 - 10:00</option>
            </select>
        </div>
        <div class="form-group">
            <label>Họ và tên:</label>
            <input type="text" name="fullname" required>
        </div>
        <div class="form-group">
            <label>Số điện thoại:</label>
            <input type="tel" name="phone" required>
        </div>
        <button type="submit">Đặt sân</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
