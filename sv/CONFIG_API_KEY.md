# 🔑 Cấu hình API Key OpenAI

## ⚠️ Lỗi hiện tại:
```
Error code: 401 - Incorrect API key provided
```

## 🛠️ Cách sửa:

### 1. Lấy API Key mới:
1. Truy cập: https://platform.openai.com/account/api-keys
2. Đăng nhập tài khoản OpenAI
3. Tạo API key mới (nếu cần)
4. Copy API key

### 2. Cập nhật trong code:
Mở file `sv/chatbot_badminton.py` và thay thế dòng 20:

```python
# Thay thế dòng này:
client = OpenAI( api_key="sk-proj-YOUR_ACTUAL_API_KEY_HERE" )

# Bằng API key thật của bạn:
client = OpenAI( api_key="sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" )
```

### 3. Kiểm tra:
- API key phải bắt đầu bằng `sk-proj-`
- Không có khoảng trắng thừa
- Không có dấu ngoặc kép thừa

## 🔍 Các lỗi thường gặp:

### Lỗi 401 - Invalid API Key:
- ✅ Kiểm tra API key có đúng không
- ✅ Kiểm tra tài khoản OpenAI có còn credit không
- ✅ Kiểm tra API key có bị vô hiệu hóa không

### Lỗi 429 - Rate Limit:
- ✅ Chờ một chút rồi thử lại
- ✅ Nâng cấp gói OpenAI nếu cần

### Lỗi 500 - Server Error:
- ✅ Kiểm tra kết nối internet
- ✅ Thử lại sau vài phút

## 💡 Lưu ý:
- API key là thông tin nhạy cảm, không chia sẻ công khai
- Có thể tạo nhiều API key để dự phòng
- Mỗi API key có giới hạn sử dụng riêng
