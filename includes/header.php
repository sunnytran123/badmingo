<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<!-- Font Awesome 5 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sân Cầu Lông SportPro</title>
    <style>
    * {margin:0; padding:0; box-sizing: border-box;}
    body {font-family: "Segoe UI", sans-serif; background: #f8f9fa; color: #333;}
    .sidebar {
        background: linear-gradient(90deg, #007bff, #ff6200);
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
        gap: 20px;
        justify-content: space-between;
        padding-left: 20px;
        padding-right: 20px;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }
    .sidebar-content a {
        text-decoration: none;
    }
    .header-content {
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
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
        background: #ff8c00;
        color: white;
    }
    .user-icon {
        padding: 10px 20px;
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
        background: #ff8c00;
        color: white;
    }
    .logout-icon {
        padding: 10px 20px;
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
    .logout-icon:hover {
        background: #ff8c00;
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
        color: #007bff;
        border-left: 5px solid #007bff;
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
        background: #007bff;
        color: white;
        border: none;
        cursor: pointer;
    }
    button:hover {
        background: #0056b3;
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
        color: #dc3545;
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
        background: #007bff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .chat-input button:hover {
        background: #0056b3;
    }
    .shop-intro {
        background: linear-gradient(135deg, #007bff, #ff6200);
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
        width: 400px;
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
        height: 400px;
        max-height: 75vh;        
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
    .bubble-chat-option:hover { background: #E0E7FF; }
    .bubble-chat-input {
        display: flex;
        gap: 8px;
        padding: 12px 16px;
        border-top: 1px solid #E5E7EB;
        background: #fff;
    }
    .bubble-chat-input input {
        flex: 1;
        padding: 12px 14px;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        font-size: 16px;
        min-height: 46px;
        outline: none;
    }
    .bubble-chat-send {
        background: #6366F1;
        color: #fff;
        border: none;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 6px 14px rgba(99,102,241,0.2);
    }
    .bubble-chat-send:hover { background: #4F46E5; }
    .cart-toggle {
        position: fixed;
        bottom: 110px;
        right: 30px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        z-index: 1001;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
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
            <a href="shop_list.php">Cửa hàng</a>
            <a href="history.php">Lịch sử</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="user-icon">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>
                <a href="logout.php" class="logout-icon" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="user-icon">
                    <i class="fas fa-user-circle"></i>
                    <span>Đăng nhập</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">

<!-- Chatbot Button -->
<div class="chatbot-button">
    <button onclick="openChatbot()" title="Mở Chatbot">
        <i class="fas fa-robot"></i>
        <span>Chatbot</span>
    </button>
</div>

<!-- Chatbot Popup -->
<div id="chatbot-popup" class="chatbot-popup" style="display: none;">
    <div class="chatbot-header">
        <h3>Sunny Sport Chatbot</h3>
        <button onclick="closeChatbot()" class="chatbot-close" title="Đóng">×</button>
    </div>
    <iframe id="chatbot-frame" src="" frameborder="0"></iframe>
</div>
<style>
/* Chatbot Button Styles */
.chatbot-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 10000;
    transition: all 0.3s ease;
}

.chatbot-button button {
    display: flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 14px 24px;
    border-radius: 30px;
    border: none;
    box-shadow: 
        0 8px 25px rgba(102, 126, 234, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    font-size: 15px;
    letter-spacing: 0.3px;
    opacity: 1;
    transform: scale(1);
}

.chatbot-button button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.chatbot-button button:hover::before {
    left: 100%;
}

.chatbot-button button:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 
        0 12px 35px rgba(102, 126, 234, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.2);
    animation: pulse 2s infinite;
}

.chatbot-button i {
    font-size: 20px;
    position: relative;
    z-index: 1;
}

.chatbot-button span {
    font-size: 15px;
    position: relative;
    z-index: 1;
}

/* Admin button different color */
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
.chatbot-button button {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    box-shadow: 
        0 8px 25px rgba(231, 76, 60, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.1);
}

.chatbot-button button:hover {
    box-shadow: 
        0 12px 35px rgba(231, 76, 60, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.2);
}
<?php endif; ?>

/* Chatbot Popup Styles */
.chatbot-popup {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 380px;
    height: 580px;
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 20px;
    box-shadow: 
        0 20px 40px rgba(0, 0, 0, 0.15),
        0 0 0 1px rgba(255, 255, 255, 0.8),
        inset 0 1px 0 rgba(255, 255, 255, 0.6);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
    opacity: 1;
    transform: translateY(0) scale(1);
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideInUp {
    from {
        transform: translateY(100px) scale(0.9);
        opacity: 0;
    }
    to {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
    }
}

/* Responsive: Điều chỉnh kích thước chatbot */
@media (max-width: 768px) {
    .chatbot-popup {
        width: 360px;
        height: 520px;
        bottom: 15px;
        right: 15px;
        border-radius: 18px;
    }
    
    .chatbot-popup .chatbot-header {
        padding: 18px 22px;
        border-radius: 18px 18px 0 0;
    }
    
    .chatbot-popup .chatbot-header h3 {
        font-size: 18px;
    }
    
    .chatbot-popup .chatbot-close {
        width: 32px;
        height: 32px;
        font-size: 18px;
    }
}

@media (max-width: 480px) {
    .chatbot-popup {
        width: 340px;
        height: 480px;
        bottom: 10px;
        right: 10px;
        border-radius: 16px;
    }
    
    .chatbot-popup .chatbot-header {
        padding: 16px 20px;
        border-radius: 16px 16px 0 0;
    }
    
    .chatbot-popup .chatbot-header h3 {
        font-size: 16px;
    }
    
    .chatbot-popup .chatbot-close {
        width: 30px;
        height: 30px;
        font-size: 16px;
    }
}

.chatbot-popup .chatbot-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 20px 20px 0 0;
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
}

.chatbot-popup .chatbot-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%, rgba(255,255,255,0.1) 100%);
    pointer-events: none;
}

.chatbot-popup .chatbot-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    letter-spacing: -0.5px;
    position: relative;
    z-index: 1;
}

.chatbot-popup .chatbot-close {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1;
    backdrop-filter: blur(10px);
}

.chatbot-popup .chatbot-close:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

#chatbot-frame {
    flex: 1;
    width: 100%;
    border: none;
    border-radius: 0 0 20px 20px;
    min-height: 0;
    background: #fafbfc;
}

/* Admin popup different color */
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
.chatbot-popup .chatbot-header {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}
<?php endif; ?>
</style>

<script>
function openChatbot() {
    const popup = document.getElementById('chatbot-popup');
    const frame = document.getElementById('chatbot-frame');
    const button = document.querySelector('.chatbot-button');
    
    // Xác định URL chatbot dựa trên role
    const isAdmin = <?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'true' : 'false'; ?>;
    const chatbotUrl = isAdmin ? 'chatbot_admin.php' : 'chatbot_user.php';
    
    // Set iframe source
    frame.src = chatbotUrl;
    
    // Hiển thị popup với animation
    popup.style.display = 'flex';
    popup.style.opacity = '0';
    popup.style.transform = 'translateY(100px) scale(0.9)';
    
    // Fade in popup
    setTimeout(() => {
        popup.style.opacity = '1';
        popup.style.transform = 'translateY(0) scale(1)';
    }, 10);
    
    // Fade out button
    button.style.opacity = '0';
    button.style.transform = 'scale(0.8)';
    setTimeout(() => {
        button.style.display = 'none';
    }, 300);
}

function closeChatbot() {
    const popup = document.getElementById('chatbot-popup');
    const frame = document.getElementById('chatbot-frame');
    const button = document.querySelector('.chatbot-button');
    
    // Fade out popup
    popup.style.opacity = '0';
    popup.style.transform = 'translateY(100px) scale(0.9)';
    
    setTimeout(() => {
        popup.style.display = 'none';
        // Clear iframe source để dừng các process
        frame.src = '';
    }, 300);
    
    // Hiện lại button với animation
    button.style.display = 'block';
    button.style.opacity = '0';
    button.style.transform = 'scale(0.8)';
    
    setTimeout(() => {
        button.style.opacity = '1';
        button.style.transform = 'scale(1)';
    }, 350);
}

// Đóng chatbot bằng phím ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeChatbot();
    }
});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>