<?php include 'includes/header.php'; ?>

<h2 class="section-title">Chat với chúng tôi</h2>
<div class="chatbox">
    <div class="chat-messages" id="chatMessages">
        <p><b>Bot:</b> Xin chào! Bạn muốn đặt sân hay mua hàng?</p>
    </div>
    <div class="chat-input">
        <input type="text" id="userInput" placeholder="Nhập tin nhắn...">
        <button onclick="sendMessage()">Gửi</button>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
