<?php
session_start();
// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sunny Sport</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            height: 100vh;
            overflow: hidden;
        }

        .dashboard-container {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* Sidebar - Danh sách cuộc trò chuyện */
        .conversations-sidebar {
            width: 135px;
            background: white;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            background: #667eea;
            color: white;
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .sidebar-header .subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .search-box {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
        }

        .search-input:focus {
            border-color: #667eea;
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }

        .conversation-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s;
            position: relative;
        }

        .conversation-item:hover {
            background: #f8f9fa;
        }

        .conversation-item.active {
            background: #e3f2fd;
            border-left: 4px solid #667eea;
        }

        .conversation-item.has-new-message {
            background: #fff3cd;
        }

        .conversation-user {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }



        .new-message-badge {
            position: absolute;
            top: 12px;
            right: 15px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        /* Main Chat Area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 10px 10px;
            background: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-user-info {
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            font-size: 18px;
        }

        .user-details h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .user-details .user-id {
            font-size: 14px;
            color: #666;
        }

        .chat-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 10px 18px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            font-weight: 500;
        }

        .action-btn:hover {
            background: #f8f9fa;
        }

        .action-btn.primary {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .action-btn.primary:hover {
            background: #5a6fd8;
        }

        .chat-messages {
            flex: 1;
            padding: 1px 30px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
        }

        .message.user {
            align-items: flex-end;
        }

        .message.bot {
            align-items: flex-start;
        }

        .message.admin {
            align-items: flex-start;
        }

        .message-content {
            max-width: 60%;
            padding: 8px 12px;
            border-radius: 15px;
            font-size: 12px;
            line-height: 1.3;
            word-wrap: break-word;
            white-space: pre-line;
        }

        .message.user .message-content {
            background: #007bff;
            color: white;
            border-bottom-right-radius: 8px;
        }

        .message.bot .message-content {
            background: white;
            color: #333;
            border: 1px solid #e5e7eb;
            border-bottom-left-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .message.admin .message-content {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            border-bottom-left-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }


        .chat-input-area {
            padding: 20px 30px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .chat-input-wrapper {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .chat-input {
            flex: 1;
            padding: 12px 18px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .chat-input:focus {
            border-color: #667eea;
        }

        .send-btn {
            background: #667eea;
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .send-btn:hover {
            background: #5a6fd8;
        }

        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .typing-indicator {
            display: none;
            padding: 12px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            border-bottom-left-radius: 5px;
            font-size: 14px;
            color: #666;
            max-width: 70%;
        }

        .typing-dots {
            display: inline-block;
            animation: typing 1.5s infinite;
        }

        @keyframes typing {
            0%, 60%, 100% { opacity: 0.3; }
            30% { opacity: 1; }
        }

        .no-conversation {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            font-size: 16px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #667eea;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
        }

        /* Product card styles for admin */
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .product-card {
            width: 200px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-2px);
        }

        .product-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .product-name {
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .product-price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar - Danh sách cuộc trò chuyện -->
        <div class="conversations-sidebar">
            
            <div class="conversations-list" id="conversationsList">
                <!-- Danh sách cuộc trò chuyện sẽ được load ở đây -->
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main">
            <div class="chat-header" id="chatHeader" style="display: none;">
                <div class="chat-user-info">
                    <div class="user-details">
                        <h3 id="userName">User</h3>
                    </div>
                </div>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="no-conversation">
                    Chọn một cuộc trò chuyện để xem
                </div>
            </div>
            
            <div class="chat-input-area" id="chatInputArea" style="display: none;">
                <div class="chat-input-wrapper">
                    <input type="text" class="chat-input" id="chatInput" placeholder="Nhập tin nhắn admin..." autocomplete="off">
                    <button class="send-btn" id="sendBtn" onclick="sendAdminMessage()">
                        <span>📤</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script>
        // Config
        var API_BASE_URL = 'http://localhost:5000/api';
        var adminId = '<?php echo $_SESSION["user_id"]; ?>';
        var currentConversationId = null;
        var pollingInterval;
        var conversations = [];

        // DOM elements
        var conversationsList = document.getElementById('conversationsList');
        var chatHeader = document.getElementById('chatHeader');
        var chatMessages = document.getElementById('chatMessages');
        var chatInputArea = document.getElementById('chatInputArea');
        var chatInput = document.getElementById('chatInput');
        var sendBtn = document.getElementById('sendBtn');
        var searchInput = document.getElementById('searchInput');
        var notification = document.getElementById('notification');

        // Load conversations on page load
        window.onload = function() {
            loadConversations();
            startPolling();
        };

        // Load conversations list
        async function loadConversations() {
            try {
                console.log('🔄 Đang load conversations từ:', API_BASE_URL + '/admin/conversations');
                const response = await fetch(API_BASE_URL + '/admin/conversations');
                console.log('📡 Response status:', response.status);
                
                const data = await response.json();
                console.log('📨 Data nhận được:', data);
                
                if (data && data.status === "success") {
                    conversations = data.conversations;
                    console.log('✅ Số conversation:', conversations.length);
                    displayConversations(conversations);
                } else {
                    console.error('❌ API trả về lỗi:', data);
                    conversationsList.innerHTML = '<div style="padding: 20px; text-align: center; color: #e74c3c;">❌ Lỗi load danh sách: ' + (data?.message || 'Unknown error') + '</div>';
                }
            } catch (error) {
                console.error('❌ Lỗi load conversations:', error);
                conversationsList.innerHTML = '<div style="padding: 20px; text-align: center; color: #e74c3c;">❌ Không thể kết nối server Python<br>Kiểm tra: python chatbot_badminton.py</div>';
            }
        }

        // Display conversations
        function displayConversations(conversations) {
            conversationsList.innerHTML = '';
            
            if (conversations.length === 0) {
                conversationsList.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Chưa có cuộc trò chuyện nào</div>';
                return;
            }

            conversations.forEach(conv => {
                const convDiv = document.createElement('div');
                convDiv.className = 'conversation-item';
                if (conv.has_new_message) {
                    convDiv.classList.add('has-new-message');
                }
                
                convDiv.innerHTML = `
                    <div class="conversation-user">${conv.user_name || 'User ' + conv.user_id}</div>
                    ${conv.new_message_count > 0 ? `<div class="new-message-badge">${conv.new_message_count}</div>` : ''}
                `;
                
                convDiv.setAttribute('data-user-id', conv.user_id);
                convDiv.onclick = function() {
                    selectConversation(conv.user_id);
                };
                conversationsList.appendChild(convDiv);
            });
        }

        // Select conversation
        async function selectConversation(userId) {
            currentConversationId = userId;
            
            // Update UI
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Find and highlight the clicked conversation
            const clickedItem = document.querySelector(`[data-user-id="${userId}"]`);
            if (clickedItem) {
                clickedItem.classList.add('active');
            }
            
            // Show chat area
            chatHeader.style.display = 'flex';
            chatInputArea.style.display = 'block';
            
            // Load conversation messages
            await loadConversationMessages(userId);
        }

        // Load conversation messages
        async function loadConversationMessages(userId) {
            try {
                const response = await fetch(API_BASE_URL + '/chat/history/' + userId);
                const data = await response.json();
                
                if (data && data.status === "success") {
                    displayMessages(data.history);
                    updateUserInfo(userId);
                }
            } catch (error) {
                console.error('Lỗi load messages:', error);
            }
        }

        // Display messages
        function displayMessages(messages) {
            chatMessages.innerHTML = '';
            
            if (messages.length === 0) {
                chatMessages.innerHTML = '<div class="no-conversation">Chưa có tin nhắn nào</div>';
                return;
            }

            messages.forEach(msg => {
                displayMessage(msg.message, msg.role, msg.created_at);
            });
            
            scrollToBottom();
        }

        // Display single message
        function displayMessage(message, role, timestamp) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message ' + role;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            
            // Check if message contains HTML (like product cards)
            if(/<\/?[a-z][\s\S]*>/i.test(message)) {
                contentDiv.innerHTML = message;
            } else {
                contentDiv.textContent = message;
            }
            
            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);
        }


        // Update user info
        function updateUserInfo(userId) {
            const user = conversations.find(c => c.user_id === userId);
            if (user) {
                document.getElementById('userName').textContent = user.user_name || 'User ' + userId;
                document.getElementById('userId').textContent = user.user_name || 'User ' + userId;
                document.getElementById('userAvatar').textContent = (user.user_name || 'U').charAt(0).toUpperCase();
            }
        }

        // Send admin message
        function sendAdminMessage() {
            const message = chatInput.value.trim();
            if (!message || !currentConversationId) return;
            
            // Disable input
            chatInput.disabled = true;
            sendBtn.disabled = true;
            
            // Send to API
            fetch(API_BASE_URL + '/admin/send_message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: currentConversationId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Add message to chat
                    displayMessage(message, 'admin', new Date().toISOString());
                    chatInput.value = '';
                    scrollToBottom();
                } else {
                    showNotification('Lỗi gửi tin nhắn: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Lỗi gửi tin nhắn:', error);
                showNotification('Lỗi gửi tin nhắn', 'error');
            })
            .finally(() => {
                // Re-enable input
                chatInput.disabled = false;
                sendBtn.disabled = false;
                chatInput.focus();
            });
        }

        // Refresh conversation
        function refreshConversation() {
            if (currentConversationId) {
                loadConversationMessages(currentConversationId);
            }
        }

        // Show notification
        function showNotification(message, type = 'info') {
            notification.textContent = message;
            notification.style.display = 'block';
            notification.style.background = type === 'error' ? '#dc3545' : '#e74c3c';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }


        // Scroll to bottom
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }


        // Polling for updates
        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(() => {
                checkForUpdates();
            }, 3000);
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        async function checkForUpdates() {
            try {
                const response = await fetch(API_BASE_URL + '/admin/conversations');
                const data = await response.json();
                
                if (data && data.status === "success") {
                    const newConversations = data.conversations;
                    console.log('🔄 Polling update - conversations:', newConversations.length);
                    
                    // Check for new messages
                    newConversations.forEach(newConv => {
                        const oldConv = conversations.find(c => c.user_id === newConv.user_id);
                        if (oldConv && newConv.last_time > oldConv.last_time) {
                            // New message detected
                            if (newConv.user_id !== currentConversationId) {
                                showNotification(`Tin nhắn mới từ ${newConv.user_name || 'User ' + newConv.user_id}`, 'info');
                            }
                        }
                    });
                    
                    conversations = newConversations;
                    displayConversations(conversations);
                }
            } catch (error) {
                console.error('❌ Lỗi polling:', error);
            }
        }

        // Event listeners
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendAdminMessage();
            }
        });

        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const filtered = conversations.filter(conv => 
                (conv.user_name && conv.user_name.toLowerCase().includes(query)) ||
                conv.user_id.toLowerCase().includes(query)
            );
            displayConversations(filtered);
        });

        // Cleanup
        window.addEventListener('beforeunload', function() {
            stopPolling();
        });

        // Handle product card clicks to open in new tab (admin opens in new tab for convenience)
        document.addEventListener('click', function(e) {
            // Check if clicked element is a product card or inside a product card
            var productCard = e.target.closest('.product-card');
            if (productCard) {
                e.preventDefault();
                e.stopPropagation();
                
                // Extract product_id from onclick attribute
                var onclick = productCard.getAttribute('onclick');
                if (onclick) {
                    // Extract URL from onclick="window.location.href='...'"
                    var urlMatch = onclick.match(/window\.location\.href\s*=\s*['"]([^'"]*)['"]/);
                    if (urlMatch) {
                        // Admin mở tab mới để tiện theo dõi chat
                        window.open(urlMatch[1], '_blank');
                    }
                }
            }
        });
    </script>
</body>
</html>