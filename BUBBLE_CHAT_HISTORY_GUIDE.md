# Hướng dẫn sử dụng Bubble Chat với Lịch sử

## Tính năng mới đã thêm

### 1. Hiển thị lịch sử chat trong bubble chat
- Khi mở bubble chat, tất cả tin nhắn cũ sẽ được hiển thị
- Lịch sử được sắp xếp theo thời gian (cũ nhất đến mới nhất)
- Hiển thị thời gian gửi tin nhắn cho mỗi tin nhắn
- Tin nhắn user và bot có màu sắc khác nhau
- **Polling sau 3 giây**: Tự động kiểm tra và load tin nhắn mới mỗi 3 giây (theo cách shoplinhkien)

### 2. Giao diện cải tiến
- Tin nhắn user: nền xanh nhạt (#EEF2FF)
- Tin nhắn bot: nền trắng (#FFFFFF)
- Header hiển thị "Bạn • 14:30" hoặc "Bot • 14:30"
- Auto scroll xuống tin nhắn mới nhất
- Hỗ trợ xuống dòng trong tin nhắn

### 3. Cách hoạt động

#### Khi mở bubble chat:
1. Gọi API `http://localhost:5000/api/chat/history/{user_id}`
2. Load lịch sử chat từ database
3. Hiển thị tất cả tin nhắn cũ với thời gian
4. Nếu không có lịch sử, hiển thị tin nhắn chào mừng
5. **Bắt đầu polling**: Tự động kiểm tra tin nhắn mới mỗi 3 giây

#### Khi gửi tin nhắn mới:
1. Hiển thị tin nhắn user ngay lập tức
2. Gọi API `http://localhost:5000/api/chat` để gửi tin nhắn
3. Hiển thị phản hồi bot với thời gian
4. Tự động scroll xuống cuối

#### Khi đóng bubble chat:
1. Dừng polling để tiết kiệm tài nguyên
2. Clear interval để tránh memory leak

## Cấu trúc Code

### JavaScript Functions:

#### `loadChatHistory()`
- Load lịch sử chat từ server
- Hiển thị tin nhắn với format đẹp
- **Hỗ trợ HTML**: Tự động detect và render HTML tags (sản phẩm, links)
- Xử lý trường hợp không có lịch sử
- Xóa nội dung cũ trước khi load mới (theo cách shoplinhkien)

#### `startPolling()` / `stopPolling()`
- Bắt đầu/dừng polling mỗi 3 giây
- Kiểm tra tin nhắn mới từ server
- Quản lý interval để tránh memory leak

#### `checkForNewMessages()`
- So sánh số lượng tin nhắn hiện tại vs server
- Chỉ reload khi có tin nhắn mới thực sự

#### `sendToAI(prompt)`
- Hiển thị tin nhắn user ngay lập tức
- Gửi request đến chatbot API
- Hiển thị phản hồi bot với thời gian

#### `appendBubble(text, role, elId)`
- Tạo tin nhắn bubble với style phù hợp
- Hỗ trợ xuống dòng với `whiteSpace: pre-line`

## API Endpoints cần thiết

### 1. `/api/chat/history/{user_id}` (GET)
```json
{
  "status": "success",
  "history": [
    {
      "role": "user",
      "message": "Tôi muốn đặt sân",
      "created_at": "2025-01-20T14:30:00Z"
    },
    {
      "role": "bot", 
      "message": "Bạn muốn đặt sân ngày nào?",
      "created_at": "2025-01-20T14:30:05Z"
    }
  ]
}
```

### 2. `/api/chat` (POST)
```json
{
  "message": "Tôi muốn đặt sân",
  "user_id": "user123"
}
```

## Cách sử dụng

### 1. Đảm bảo server chatbot chạy
```bash
cd sv
python chatbot_badminton.py
```

### 2. Truy cập trang web
- Mở bất kỳ trang nào có include `header.php`
- Click vào icon chat 💬 ở góc phải màn hình
- Lịch sử chat sẽ được load tự động

### 3. Sử dụng chat
- Nhập tin nhắn và nhấn Enter hoặc click nút gửi
- Tin nhắn mới sẽ được hiển thị ngay lập tức
- Bot sẽ phản hồi và lưu vào database

## Lưu ý quan trọng

1. **User ID**: Lấy từ `$_SESSION['user_id']` hoặc mặc định là 'guest'
2. **Database**: Cần có bảng `chat_history` trong database `sunny_sport`
3. **CORS**: Server chatbot cần hỗ trợ CORS cho domain của website
4. **Error Handling**: Có xử lý lỗi khi không kết nối được server

## Troubleshooting

### Lỗi không load được lịch sử
- Kiểm tra server chatbot có chạy không
- Kiểm tra API endpoint `/api/chat/history/{user_id}`
- Kiểm tra console browser để xem lỗi chi tiết

### Lỗi gửi tin nhắn
- Kiểm tra API endpoint `/api/chat`
- Kiểm tra kết nối mạng
- Kiểm tra user_id có đúng không

### Lỗi hiển thị
- Kiểm tra JavaScript console
- Kiểm tra format dữ liệu trả về từ API
- Kiểm tra CSS có bị conflict không

## Cải tiến có thể thêm

1. **Pagination**: Load lịch sử theo trang thay vì tất cả
2. **Real-time**: Sử dụng WebSocket để cập nhật real-time
3. **Search**: Tìm kiếm trong lịch sử chat
4. **Export**: Xuất lịch sử chat ra file
5. **Notification**: Thông báo khi có tin nhắn mới
6. **Configurable interval**: Cho phép thay đổi thời gian auto load (hiện tại 3 giây)
7. **Smart loading**: Chỉ load khi có thay đổi thực sự trong database
8. **Loading indicator**: Hiển thị indicator khi đang auto load
