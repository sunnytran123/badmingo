<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<!-- Font Awesome 5 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sân Cầu Lông SportPro</title>
    <style>
    * {margin:0; padding:0; box-sizing: border-box;}
    body {font-family: "Segoe UI", sans-serif; background: #f8f9fa; color: #333;}
    .sidebar {
        background: linear-gradient(90deg, #007bff, #ff6200); /* Giữ nguyên gradient */
        padding: 15px; 
        display: flex;
        align-items: center;
        color: white;
        font-size: 24px; 
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .sidebar-content {
        display: flex;
        align-items: center;
        gap: 20px; /* Tăng khoảng cách giữa các phần tử */
        justify-content: space-between; /* Phân bố đều các phần tử */
        padding-left: 20px;
        padding-right: 20px; /* Thêm padding bên phải */
        width: 100%; /* Sử dụng toàn bộ độ rộng */
        max-width: 1200px; /* Tăng max-width để phù hợp với nội dung */
        margin: 0 auto;
    }
    .sidebar-content a {
        text-decoration: none;
    }
    .header-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .header-content img {
        max-width: 50px;
        height: auto;
    }
    .header-content span {
        font-size: 24px;
        font-weight: bold;
        color: white;
    }
    .sidebar-menu {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-left: auto;
    }
    .sidebar-menu a {
        padding: 10px 20px;
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: 0.3s;
        border-radius: 5px;
    }
    .sidebar-menu a:hover {
        background: #ff8c00; /* Cam nhạt khi hover */
        color: white;
    }
    .user-icon {
        padding: 10px 20px; /* Giống padding của .sidebar-menu a */
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: 0.3s;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .user-icon:hover {
        background: #ff8c00; /* Giống hover của .sidebar-menu a */
        color: white;
    }
    .container {
        max-width: 1200px;
        margin: auto;
        padding: 20px;
    }
    .section-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #007bff; /* Xanh dương đậm */
        border-left: 5px solid #007bff; /* Xanh dương đậm */
        padding-left: 10px;
    }
    .news {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    .news-item {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .news-item img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .news-content {
        padding: 15px;
    }
    .booking-form {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    input, select, button {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }
    button {
        background: #007bff; /* Xanh dương đậm */
        color: white;
        border: none;
        cursor: pointer;
    }
    button:hover {
        background: #0056b3; /* Xanh dương tối hơn khi hover */
    }
    .products {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    .product {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        text-align: center;
    }
    .product img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .product-info {
        padding: 15px;
    }
    .price {
        color: #dc3545; /* Giữ màu đỏ cho giá */
        font-weight: bold;
    }
    .chatbox {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        height: 400px;
    }
    .chat-messages {
        border: 1px solid #ddd;
        height: 300px;
        padding: 10px;
        overflow-y: auto;
        margin-bottom: 10px;
        border-radius: 6px;
        font-size: 14px;
    }
    .chat-input {
        display: flex;
        gap: 10px;
    }
    .chat-input input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }
    .chat-input button {
        padding: 10px 15px;
        background: #007bff; /* Xanh dương đậm */
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .chat-input button:hover {
        background: #0056b3; /* Xanh dương tối hơn */
    }
    .shop-intro {
        background: linear-gradient(135deg, #007bff, #ff6200); /* Gradient xanh dương và cam */
        color: #fff;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        animation: fadeIn 1s ease-in-out;
    }
    .shop-intro img {
        max-width: 300px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        animation: slideIn 1s ease-in-out;
    }
    .shop-intro-content {
        flex: 1;
        text-align: center;
    }
    .shop-intro h2 {
        font-size: 2em;
        margin-bottom: 15px;
    }
    .shop-intro p {
        font-size: 1.1em;
        line-height: 1.6;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideIn {
        from { transform: translateX(-50px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @media (max-width: 768px) {
        .category-card {
            flex: 1 1 100%;
        }
        .shop-intro {
            flex-direction: column;
            text-align: center;
        }
        .shop-intro img {
            max-width: 100%;
        }
        .sidebar-content {
            flex-direction: column;
            align-items: center;
        }
        .sidebar-menu {
            flex-direction: column;
            gap: 10px;
        }
    }

    /* Bubble Chat */
    #bubble-chat-btn {
        position: fixed;
        bottom: 32px;
        right: 32px;
        z-index: 9999;
        background: linear-gradient(135deg, #6366F1 0%, #5B21B6 100%);
        color: #fff;
        border-radius: 50%;
        width: 62px;
        height: 62px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(99,102,241,0.18);
        cursor: pointer;
        font-size: 28px;
        border: none;
        transition: box-shadow 0.2s;
    }
    #bubble-chat-btn:hover { box-shadow: 0 12px 32px rgba(99,102,241,0.28); }

    #bubble-chat-window {
        position: fixed;
        bottom: 110px;
        right: 32px;
        z-index: 9999;
        width: 370px;
        max-width: 95vw;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(99,102,241,0.18);
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: bubbleIn 0.22s;
    }
    @keyframes bubbleIn { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .bubble-chat-header {
        background: linear-gradient(135deg, #6366F1 0%, #5B21B6 100%);
        color: #fff;
        padding: 14px 18px;
        font-size: 18px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: space-between;
        white-space: nowrap;
        gap: 8px;
    }
    .bubble-chat-title {
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .bubble-chat-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 16px;
        width: 28px;
        height: 28px;
        line-height: 1;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .bubble-chat-body {
        padding: 16px;
        background: #F3F4F6;
        min-height: 120px;
        max-height: 320px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .bubble-chat-option {
        background: #fff;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 6px;
        cursor: pointer;
        font-size: 15px;
        color: #4F46E5;
        border: 1px solid #E5E7EB;
        transition: background 0.15s;
    }
    .bubble-chat-option:hover {
        background: #E0E7FF;
    }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-content">
        <a href="index.php">
            <div class="header-content">
                <img src="images/Olypic.png" alt="Sunny Logo">
                <span>Sunny Sport</span>
            </div>
        </a>
        <div class="sidebar-menu">
            <a href="index.php">Trang chủ</a>
            <a href="booking.php">Đặt sân</a>
            <a href="t.php">Cửa hàng</a>
            <!-- <a href="contact.php">Liên hệ</a> -->
            <div class="user-icon" id="userIcon" style="position:relative;">
                <i class="fas fa-user-circle"></i>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div id="userDropdown" class="user-dropdown" style="display:none;position:absolute;top:40px;right:0;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.15);border-radius:8px;min-width:180px;z-index:999;">
                        <a href="history.php" style="display:block;padding:12px 20px;color:#333;text-decoration:none;border-bottom:1px solid #eee;">Lịch sử giao dịch</a>
                        <a href="#" id="logoutBtn" style="display:block;padding:12px 20px;color:#dc3545;text-decoration:none;">Đăng xuất</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container">

<style>
.user-dropdown a:hover {
    background: #f8f9fa;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var userIcon = document.getElementById('userIcon');
    var userDropdown = document.getElementById('userDropdown');
    var logoutBtn = document.getElementById('logoutBtn');

    <?php if (!isset($_SESSION['user_id'])): ?>
        userIcon.onclick = function() {
            window.location.href = 'login.php';
        };
    <?php else: ?>
        userIcon.onclick = function(e) {
            e.stopPropagation();
            userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
        };
        document.body.onclick = function() {
            userDropdown.style.display = 'none';
        };
        userDropdown.onclick = function(e) {
            e.stopPropagation();
        };
        logoutBtn.onclick = function(e) {
            e.preventDefault();
            if (confirm('Bạn có chắc chắn muốn đăng xuất không?')) {
                window.location.href = 'logout.php';
            }
        };
    <?php endif; ?>
});
</script>

<!-- Bubble Chat -->
<button id="bubble-chat-btn" title="Chat hỗ trợ">
    💬
</button>
<div id="bubble-chat-window">
    <div class="bubble-chat-header">
        <span class="bubble-chat-title">Chat Sunny Sport</span>
        <button class="bubble-chat-close" aria-label="Đóng" onclick="document.getElementById('bubble-chat-window').style.display='none'">&times;</button>
    </div>
    <div class="bubble-chat-body" id="bubble-chat-body">
        <div class="bubble-chat-option" onclick="window.location.href='booking.php'">Đặt sân cầu lông</div>
        <div class="bubble-chat-option" onclick="window.location.href='shop.php'">Mua vợt, phụ kiện</div>
    </div>
</div>
<script>
document.getElementById('bubble-chat-btn').onclick = function() {
    var win = document.getElementById('bubble-chat-window');
    win.style.display = win.style.display === 'flex' ? 'none' : 'flex';
};
</script>