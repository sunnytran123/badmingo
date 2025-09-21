<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot User - Sunny Sport</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 15px;
            text-align: center;
            position: relative;
            flex-shrink: 0;
        }

        .chat-header h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .chat-header .subtitle {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 3px;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .chat-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .message.user {
            align-items: flex-end;
        }

        .message.assistant {
            align-items: flex-start;
        }

        .message-content {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .message.user .message-content {
            background: #007bff;
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message.assistant .message-content {
            background: white;
            color: #333;
            border: 1px solid #e5e7eb;
            border-bottom-left-radius: 5px;
        }

        .message-time {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
            margin-left: 10px;
            margin-right: 10px;
        }

        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .chat-input-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .chat-input:focus {
            border-color: #007bff;
        }

        .send-btn {
            background: #007bff;
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
            background: #0056b3;
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
            max-width: 80%;
        }

        .typing-dots {
            display: inline-block;
            animation: typing 1.5s infinite;
        }

        @keyframes typing {
            0%, 60%, 100% { opacity: 0.3; }
            30% { opacity: 1; }
        }

        /* Product card styles */
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

        .welcome-message {
            text-align: center;
            color: #666;
            font-style: italic;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="chat-container">

        
        <div class="chat-body" id="chatBody">
            <div class="welcome-message">
                Xin chào! Tôi là Sunny Sport, tôi có thể giúp bạn đặt sân cầu lông hoặc tìm kiếm sản phẩm. Bạn cần hỗ trợ gì?
            </div>
        </div>
        
        <div class="chat-input-container">
            <div class="chat-input-wrapper">
                <input type="text" class="chat-input" id="chatInput" placeholder="Nhập tin nhắn..." autocomplete="off">
                <button class="send-btn" id="sendBtn" onclick="sendMessage()">
                    <span>📤</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Config
        var CHAT_API_URL = 'http://localhost:5000/api/chat';
        var userId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : "guest"; ?>';
        var pollingInterval;

        // DOM elements
        var chatBody = document.getElementById('chatBody');
        var chatInput = document.getElementById('chatInput');
        var sendBtn = document.getElementById('sendBtn');

        // Load chat history on page load
        window.onload = function() {
            loadChatHistory();
            startPolling();
        };

        // Load chat history
        async function loadChatHistory() {
            try {
                const response = await fetch('http://localhost:5000/api/chat/history/' + userId);
                const data = await response.json();
                
                if (data && data.status === "success" && Array.isArray(data.history)) {
                    chatBody.innerHTML = '';
                    
                    if (data.history.length === 0) {
                        showWelcomeMessage();
                    } else {
                        data.history.forEach(function(msg) {
                            displayMessage(msg.message, msg.role, msg.created_at);
                        });
                    }
                    
                    scrollToBottom();
                } else {
                    showWelcomeMessage();
                }
            } catch (error) {
                console.error('Lỗi load lịch sử chat:', error);
                showWelcomeMessage();
            }
        }

        // Show welcome message
        function showWelcomeMessage() {
            chatBody.innerHTML = '<div class="welcome-message">Xin chào! Tôi là Sunny Sport, tôi có thể giúp bạn đặt sân cầu lông hoặc tìm kiếm sản phẩm. Bạn cần hỗ trợ gì?</div>';
        }

        // Display message
        function displayMessage(message, role, timestamp) {
            var messageDiv = document.createElement('div');
            messageDiv.className = 'message ' + role;
            
            var contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            
            // Check if message contains HTML
            if(/<\/?[a-z][\s\S]*>/i.test(message)) {
                contentDiv.innerHTML = message;
            } else {
                contentDiv.textContent = message;
            }
            
            var timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            if (timestamp) {
                var time = new Date(timestamp).toLocaleTimeString('vi-VN', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                timeDiv.textContent = time;
            }
            
            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(timeDiv);
            chatBody.appendChild(messageDiv);
        }

        // Send message
        function sendMessage() {
            var message = chatInput.value.trim();
            if (!message) return;
            
            // Disable input and button
            chatInput.disabled = true;
            sendBtn.disabled = true;
            
            // Display user message
            displayMessage(message, 'user');
            chatInput.value = '';
            scrollToBottom();
            
            // Show typing indicator
            showTypingIndicator();
            
            // Send to API
            fetch(CHAT_API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                hideTypingIndicator();
                
                if (data.status === 'success') {
                    // Kiểm tra bot_disabled từ response
                    if (data.bot_disabled === 1 || data.bot_disabled === true) {
                        // Bot bị tắt - không hiển thị gì cả, không làm gì
                        console.log('Bot is disabled for this user - no response shown');
                        return; // Thoát sớm, không làm gì cả
                    } else if (data.response && data.response.trim() !== "") {
                        // Bot bật và có response - hiển thị bình thường
                        displayMessage(data.response, 'assistant');
                    }
                } else {
                    displayMessage('Xin lỗi, có lỗi xảy ra. Vui lòng thử lại.', 'assistant');
                }
                
                scrollToBottom();
            })
            .catch(error => {
                hideTypingIndicator();
                displayMessage('Xin lỗi, không thể kết nối. Vui lòng thử lại.', 'assistant');
                scrollToBottom();
            })
            .finally(() => {
                // Re-enable input and button
                chatInput.disabled = false;
                sendBtn.disabled = false;
                chatInput.focus();
            });
        }

        // Show typing indicator
        function showTypingIndicator() {
            var typingDiv = document.createElement('div');
            typingDiv.className = 'message assistant';
            typingDiv.id = 'typingIndicator';
            
            var contentDiv = document.createElement('div');
            contentDiv.className = 'typing-indicator';
            contentDiv.innerHTML = 'Đang nhập<span class="typing-dots">...</span>';
            
            typingDiv.appendChild(contentDiv);
            chatBody.appendChild(typingDiv);
            scrollToBottom();
        }

        // Hide typing indicator
        function hideTypingIndicator() {
            var typingDiv = document.getElementById('typingIndicator');
            if (typingDiv) {
                typingDiv.remove();
            }
        }

        // Scroll to bottom
        function scrollToBottom() {
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        // Polling for new messages
        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(function() {
                checkForNewMessages();
            }, 3000);
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        async function checkForNewMessages() {
            try {
                const response = await fetch('http://localhost:5000/api/chat/history/' + userId);
                const data = await response.json();
                if (data && data.status === "success" && Array.isArray(data.history)) {
                    const currentMessages = chatBody.children.length;
                    if (data.history.length > currentMessages) {
                        loadChatHistory();
                    }
                }
            } catch (e) {
                console.error('Lỗi kiểm tra tin nhắn mới:', e);
            }
        }

        // Event listeners
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopPolling();
        });

        // Handle product card clicks to open in new tab
        document.addEventListener('click', function(e) {
            // Check if clicked element is a product card or inside a product card
            var productCard = e.target.closest('.product-card');
            if (productCard) {
                e.preventDefault();
                e.stopPropagation();
                
                // Extract product_id from onclick attribute or href
                var onclick = productCard.getAttribute('onclick');
                if (onclick) {
                    // Extract URL from onclick="window.location.href='...'" or onclick="window.open('...', '_blank')"
                    var urlMatch = onclick.match(/['"]([^'"]*t\.php\?product_id=\d+[^'"]*)['"]/);
                    if (urlMatch) {
                        window.open(urlMatch[1], '_blank');
                    }
                }
            }
        });
    </script>
</body>
</html>
