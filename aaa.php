<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

// Nếu đã có tên đăng nhập thì luôn ưu tiên set theo tên user
if (isset($_SESSION['ten_dang_nhap'])) {
    $_SESSION['chatbot_session_id'] = $_SESSION['ten_dang_nhap'];
} else {
    if (!isset($_SESSION['chatbot_session_id'])) {
        $_SESSION['chatbot_session_id'] = uniqid('guest_', true);
    }
}

$chatbot_session_id = $_SESSION['chatbot_session_id'];

?>
<style>
#chatBubbleBtn {
    position: fixed;
    bottom: 40px;
    right: 40px;
    z-index: 99999;
    cursor: pointer;
    background: #8BC34A;
    color: #fff;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 16px rgba(140,195,74,0.22);
    font-size: 2rem;
    transition: box-shadow 0.2s;
}
#chatBubbleBox {
    position: fixed;
    bottom: 100px;
    right: 32px;
    z-index: 10000;
    display: none;
    max-width: 350px;
    width: 90vw;
    box-shadow: 0 4px 24px rgba(140,195,74,0.13);
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
}
#chatBubbleBox .header {
    background: #8BC34A;
    color: #fff;
    padding: 16px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 10px;
}
#chatBubbleBox .header .close-btn {
    margin-left: auto;
    cursor: pointer;
    font-size: 1.3rem;
}
#chatBubbleMessages {
    background: #f9f9f9;
    padding: 16px;
    height: 340px;
    overflow-y: auto;
    font-size: 1rem;
}
#chatBubbleForm {
    display: flex;
    border-top: 1px solid #eee;
    background: #fff;
}
#chatBubbleInput {
    flex: 1;
    border: none;
    padding: 10px;
    font-size: 1rem;
    outline: none;
    background: transparent;
}
#chatBubbleForm button {
    background: #8BC34A;
    color: #fff;
    border: none;
    padding: 0 2px;
    font-size: 1.1rem;
    cursor: pointer;
    width: 50px;
}
.bubble-message {
    margin-bottom: 12px;
    display: flex;
}
.bubble-message.user {
    justify-content: flex-end;
}
.bubble-message.bot {
    justify-content: flex-start;
}
.bubble-message .message-content {
    max-width: 70%;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 1rem;
    line-height: 1.5;
}
.bubble-message .message-content img {
  max-width: 100%;   /* không cho vượt quá chiều rộng content */
  height: auto;      /* giữ đúng tỉ lệ */
  display: block;    /* tránh bị inline thò ra ngoài */
  border-radius: 8px; /* tuỳ chọn */
}

.bubble-message.user .message-content {
    background: #8BC34A;
    color: #fff;
    border-bottom-right-radius: 4px;
}
.bubble-message.bot .message-content {
    background: #e8f5e9;
    color: #333;
    border-bottom-left-radius: 4px;
}
@media (max-width: 600px) {
    #chatBubbleBox {
        right: 2vw;
        bottom: 2vw;
        max-width: 98vw;
    }
}
/* Áp dụng CSS riêng cho sản phẩm trong popup chatbot */
.chatbot-popup .product-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.chatbot-popup .product-card {
    width: 160px; /* nhỏ gọn chỉ trong popup */
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 6px;
    margin: 5px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.1);
    cursor: pointer;
    text-align: center;
    background: #fff;
}

.chatbot-popup .product-card img.product-image {
    max-width: 120px;      
    max-height: 120px;
    object-fit: cover;
    border-radius: 6px;
    display: block;
    margin: 0 auto;    
}



.chatbot-popup .product-card .product-name {
    font-size: 0.9em;
    font-weight: 500;
    margin: 5px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chatbot-popup .product-card .product-price {
    color: #388e3c;
    font-weight: bold;
    font-size: 0.9em;
}
/* Áp dụng CSS riêng cho sản phẩm trong popup chatbot */
.chatbot-popup .product-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.chatbot-popup .product-card {
    width: 150px;          /* giống popup admin */
    padding: 8px;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.1);
    background: #fff;
    text-align: center;
}


.chatbot-popup .product-card img.product-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 7px;
}

.chatbot-popup .product-card .product-name {
    font-size: 0.9em;
    font-weight: 500;
    margin: 5px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chatbot-popup .product-card .product-price {
    color: #388e3c;
    font-weight: bold;
    font-size: 0.9em;
}


</style>
<div id="chatBubbleBtn">
    <i class="fas fa-robot"></i>
</div>
<div id="chatBubbleBox">
    <div class="header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-robot"></i> 
            <span>Chatbot</span>
            <div id="botStatusIndicator" style="display: none; font-size: 0.7rem; background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 10px;">
                <i class="fas fa-user-shield"></i> Admin đang hỗ trợ
            </div>
        </div>
        <span class="close-btn" id="closeChatBubble">&times;</span>
    </div>
    <div id="chatBubbleMessages"></div>
    <form id="chatBubbleForm" autocomplete="off">
        <input type="text" id="chatBubbleInput" placeholder="Nhập câu hỏi..." required>
        <button type="submit"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>
<script>
const CHATBOT_SESSION_ID = "<?php echo $chatbot_session_id; ?>";
function openChatBubble() {
    document.getElementById('chatBubbleBox').style.display = 'block';
    loadChatHistory();
    startPolling();
}
document.getElementById('chatBubbleBtn').onclick = openChatBubble;
document.getElementById('closeChatBubble').onclick = function(){
    document.getElementById('chatBubbleBox').style.display = 'none';
    stopPolling();
};
window.addEventListener('mousedown', function(e){
    var box = document.getElementById('chatBubbleBox');
    if(box.style.display==='block' && !box.contains(e.target) && e.target.id!=='chatBubbleBtn'){
        box.style.display='none';
        stopPolling();
    }
});
const chatBubbleForm = document.getElementById('chatBubbleForm');
const chatBubbleInput = document.getElementById('chatBubbleInput');
const chatBubbleMessages = document.getElementById('chatBubbleMessages');
function appendBubbleMsg(content, sender) {
    const msgDiv = document.createElement('div');
    msgDiv.className = 'bubble-message ' + sender;
    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';
    if(/<\/?[a-z][\s\S]*>/i.test(content)) {
        contentDiv.innerHTML = content;
    } else {
        contentDiv.textContent = content;
    }
    msgDiv.appendChild(contentDiv);
    // Nếu là admin thì thêm nhãn
    if(sender === 'admin') {
        const adminTag = document.createElement('span');
        adminTag.textContent = 'Admin';
        adminTag.style = "font-size:0.8em;color:#e65100;margin-left:8px;";
        contentDiv.appendChild(adminTag);
    }
    
    // Nếu bot thông báo admin đã tham gia, thêm style đặc biệt
    if(sender === 'bot' && content.includes('Admin đã tham gia')) {
        contentDiv.style.background = '#fff3cd';
        contentDiv.style.border = '1px solid #ffeaa7';
        contentDiv.style.color = '#856404';
    }
    chatBubbleMessages.appendChild(msgDiv);
    chatBubbleMessages.scrollTop = chatBubbleMessages.scrollHeight;
}

async function loadChatHistory() {
    const sessionId = CHATBOT_SESSION_ID;
    chatBubbleMessages.innerHTML = ''; // Xóa cũ trước khi load mới
    
    // Kiểm tra trạng thái admin trước
    await checkAdminStatus(sessionId);
    
    const res = await fetch('http://127.0.0.1:5000/api/chat/history?session_id=' + sessionId);
    const data = await res.json();
    if(data && data.status === "success" && Array.isArray(data.history)) {
        data.history.forEach(msg => {
            appendBubbleMsg(msg.message, msg.sender);
        });
    } else {
        appendBubbleMsg('Chào bạn, tôi có thể giúp gì cho bạn?', 'bot');
    }
}

async function checkAdminStatus(sessionId) {
    try {
        const res = await fetch(`http://127.0.0.1:5000/api/admin/session_status/${encodeURIComponent(sessionId)}`);
        const data = await res.json();
        const indicator = document.getElementById('botStatusIndicator');
        if (data.status === "success" && data.is_admin_joined) {
            indicator.style.display = 'block';
            indicator.innerHTML = '<i class="fas fa-user-shield"></i> Admin đang hỗ trợ';
        } else {
            indicator.style.display = 'none';
        }
    } catch (e) {
        console.error('Lỗi kiểm tra trạng thái admin:', e);
    }
}


// Polling để kiểm tra tin nhắn mới từ admin
let pollingInterval = null;
function startPolling() {
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(async () => {
        if (document.getElementById('chatBubbleBox').style.display === 'block') {
            await checkForNewMessages();
        }
    }, 3000); // Kiểm tra mỗi 3 giây
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

async function checkForNewMessages() {
    try {
        const sessionId = CHATBOT_SESSION_ID;
        const res = await fetch('http://127.0.0.1:5000/api/chat/history?session_id=' + sessionId);
        const data = await res.json();
        if(data && data.status === "success" && Array.isArray(data.history)) {
            const currentMessages = document.querySelectorAll('.bubble-message').length;
            if (data.history.length > currentMessages) {
                // Có tin nhắn mới, load lại toàn bộ
                loadChatHistory();
            }
        }
    } catch (e) {
        console.error('Lỗi kiểm tra tin nhắn mới:', e);
    }
}


chatBubbleForm.onsubmit = async function(e){
    e.preventDefault();
    const userMsg = chatBubbleInput.value.trim();
    if(!userMsg) return;
    appendBubbleMsg(userMsg, 'user');
    chatBubbleInput.value = '';
    appendBubbleMsg('Đang trả lời...', 'bot');
    try{
        const sessionId = CHATBOT_SESSION_ID;
        const response = await fetch('http://127.0.0.1:5000/api/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: userMsg, session_id: sessionId })
        });
        const data = await response.json();
        // Xóa "Đang trả lời..."
        chatBubbleMessages.lastChild.remove();
        // Nếu trả về HTML (thẻ sản phẩm), chèn innerHTML, nếu không thì textContent
        if(data && data.response && data.response.trim() !== "") {
            const msgDiv = document.createElement('div');
            msgDiv.className = 'bubble-message bot';
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            // Nếu có thẻ div hoặc html, dùng innerHTML, ngược lại dùng textContent
            if(/<\/?[a-z][\s\S]*>/i.test(data.response)) {
                contentDiv.innerHTML = data.response;
            } else {
                contentDiv.textContent = data.response;
            }
            msgDiv.appendChild(contentDiv);
            chatBubbleMessages.appendChild(msgDiv);
            
            // Kiểm tra lại trạng thái admin sau khi nhận phản hồi
            if(data.response.includes('Admin đã tham gia')) {
                checkAdminStatus(sessionId);
            }
        } else if(data && data.response === "") {
            // Bot im lặng - không hiển thị gì cả
            console.log('Bot đang im lặng vì admin đã tham gia');
        } else {
            appendBubbleMsg('Xin lỗi, không nhận được phản hồi từ server.', 'bot');
        }
        chatBubbleMessages.scrollTop = chatBubbleMessages.scrollHeight;
    }catch(err){
        chatBubbleMessages.lastChild.remove();
        appendBubbleMsg('Có lỗi xảy ra: ' + err, 'bot');
    }
};
</script>