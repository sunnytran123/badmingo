# 🎯 Hệ thống Chat 3 Tầng - Sunny Sport

## 📋 **Tổng quan hệ thống**

Hệ thống chat được thiết kế với 3 vai trò chính:
- **User** ↔ **Bot**: Cuộc trò chuyện tự động
- **Admin**: Giám sát và can thiệp khi cần

## 🏗️ **Kiến trúc hệ thống**

### **Tầng 1: User ↔ Bot**
- User chat trực tiếp với Bot
- Bot tự động trả lời câu hỏi về đặt sân, sản phẩm
- Cuộc trò chuyện được lưu vào database

### **Tầng 2: Admin Monitoring**
- Admin xem danh sách tất cả cuộc hội thoại
- Giao diện sidebar trái: danh sách chat
- Giao diện bên phải: nội dung chat chi tiết
- Thông báo real-time khi có tin nhắn mới

### **Tầng 3: Admin Intervention**
- Admin có thể chen vào cuộc trò chuyện
- Trả lời bổ sung hoặc sửa câu trả lời của Bot
- User sẽ thấy tin nhắn từ Admin

## 🚀 **Cách sử dụng**

### **1. Khởi động hệ thống**

```bash
# Khởi động Python server
cd sv
python chatbot_badminton.py
```

### **2. Truy cập giao diện**

#### **User Chat:**
- URL: `http://localhost/BadminGo/user_chat.php` (hoặc giao diện user hiện có)
- Chức năng: Chat với Bot, đặt sân, tìm sản phẩm

#### **Admin Dashboard:**
- URL: `http://localhost/BadminGo/chatbot_admin.php`
- Chức năng: Giám sát, can thiệp cuộc trò chuyện

### **3. Luồng hoạt động**

#### **Bước 1: User bắt đầu chat**
1. User truy cập `user_chat.php`
2. Gửi tin nhắn cho Bot
3. Bot tự động trả lời

#### **Bước 2: Admin giám sát**
1. Admin truy cập `chatbot_admin.php`
2. Xem danh sách cuộc trò chuyện (sidebar trái)
3. Click vào cuộc trò chuyện để xem chi tiết (bên phải)

#### **Bước 3: Admin can thiệp (nếu cần)**
1. Admin đọc nội dung chat
2. Nếu Bot trả lời không phù hợp
3. Admin gửi tin nhắn bổ sung
4. User sẽ thấy tin nhắn từ Admin

## 📁 **Cấu trúc file**

```
BadminGo/
├── user_chat.php              # Giao diện chat cho User (tùy chọn)
├── chatbot_admin.php          # Giao diện admin dashboard (đã cập nhật)
├── sv/
│   └── chatbot_badminton.py   # Python server chính
└── SYSTEM_GUIDE.md           # Hướng dẫn này
```

## 🔧 **API Endpoints**

### **User APIs:**
- `POST /api/chat` - Gửi tin nhắn cho Bot
- `GET /api/chat/history/<user_id>` - Lấy lịch sử chat

### **Admin APIs:**
- `GET /api/admin/conversations` - Lấy danh sách cuộc trò chuyện
- `POST /api/admin/send_message` - Admin gửi tin nhắn
- `GET /api/admin/user_info/<user_id>` - Thông tin chi tiết user

## 🎨 **Tính năng chính**

### **User Interface:**
- ✅ Chat trực tiếp với Bot
- ✅ Quick actions (đặt sân, tìm sản phẩm)
- ✅ Hiển thị sản phẩm dạng card
- ✅ Real-time polling tin nhắn mới

### **Admin Interface:**
- ✅ Danh sách cuộc trò chuyện (sidebar trái)
- ✅ Xem nội dung chat chi tiết
- ✅ Thông báo tin nhắn mới
- ✅ Gửi tin nhắn can thiệp
- ✅ Tìm kiếm cuộc trò chuyện

### **Bot Features:**
- ✅ Đặt sân cầu lông với conversation flow
- ✅ Tìm kiếm sản phẩm cầu lông
- ✅ Tư vấn chung về shop
- ✅ Lưu lịch sử chat

## 🔄 **Real-time Features**

### **Polling System:**
- User: Kiểm tra tin nhắn mới mỗi 3 giây
- Admin: Kiểm tra cuộc trò chuyện mới mỗi 3 giây
- Thông báo khi có tin nhắn mới

### **Notification System:**
- Admin nhận thông báo khi có tin nhắn mới
- Highlight cuộc trò chuyện có tin nhắn mới
- Badge hiển thị số tin nhắn mới

## 🗄️ **Database Schema**

### **Bảng `chat_history`:**
```sql
CREATE TABLE chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255),
    role ENUM('user', 'bot', 'admin'),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🚨 **Lưu ý quan trọng**

1. **CORS**: Server đã cấu hình CORS để cho phép truy cập từ web
2. **Session**: Admin cần đăng nhập với role 'admin'
3. **User ID**: User có thể là guest hoặc đã đăng ký
4. **Polling**: Hệ thống sử dụng polling thay vì WebSocket
5. **Database**: Đảm bảo database `sunny_sport` đã được tạo

## 🐛 **Troubleshooting**

### **Lỗi thường gặp:**

1. **Không kết nối được API:**
   - Kiểm tra Python server đã chạy chưa
   - Kiểm tra port 5000 có bị chiếm không

2. **Admin không thấy cuộc trò chuyện:**
   - Kiểm tra database có dữ liệu không
   - Kiểm tra API `/api/admin/conversations`

3. **Tin nhắn không hiển thị real-time:**
   - Kiểm tra polling interval
   - Kiểm tra console log có lỗi không

## 📞 **Hỗ trợ**

Nếu gặp vấn đề, hãy kiểm tra:
1. Console log trong browser
2. Terminal log của Python server
3. Database connection
4. API endpoints hoạt động

---

**🎉 Hệ thống đã sẵn sàng sử dụng!**
