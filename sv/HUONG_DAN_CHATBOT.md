# 🤖 Hướng dẫn sử dụng Chatbot Sunny Sport

## 🚀 Cách chạy server

### 1. Cài đặt dependencies
```bash
pip install -r install_requirements.txt
```

### 2. Chạy server
```bash
cd sv
python chatbot_badminton.py
```

### 3. Kiểm tra server
- Mở trình duyệt: `http://localhost:5000/api/chat`
- Nếu thấy lỗi 405 Method Not Allowed là bình thường (chỉ POST mới được)

## 🎯 Tính năng chatbot

### ✅ Đã hoàn thành:
- **Tìm kiếm sân cầu lông** - Trả về dạng text
- **Tìm kiếm sản phẩm** - Trả về dạng HTML cards
- **Tư vấn chung** - Trả về dạng text
- **Lưu lịch sử chat** - Theo user_id từ session
- **CORS** - Cho phép truy cập từ frontend
- **Hình ảnh sản phẩm** - Lấy từ database

### 🔧 Cấu trúc HTML sản phẩm:
```html
<div class="product-list" style="display:flex;flex-wrap:wrap;gap:15px;margin-top:10px;">
    <div class="product-card" onclick="window.location.href='ProductDetail.php?id=1'" style="...">
        <img src="images/[image_url]" class="product-image" style="...">
        <div class="product-name" style="...">Tên sản phẩm</div>
        <div class="product-price" style="...">Giá sản phẩm</div>
    </div>
</div>
```

## 🐛 Xử lý lỗi

### Lỗi CORS:
- ✅ Đã sửa bằng CORS headers
- ✅ Xử lý OPTIONS request

### Lỗi hình ảnh 404:
- ✅ Sử dụng đường dẫn đúng từ database
- ✅ Format: `images/[image_url]`

### Lỗi kết nối server:
- Kiểm tra server có chạy không
- Kiểm tra port 5000 có bị chiếm không
- Kiểm tra firewall

## 📊 Database

### Bảng chính:
- `products` - Thông tin sản phẩm
- `product_images` - Hình ảnh sản phẩm (is_primary = 1)
- `courts` - Thông tin sân cầu lông
- `bookings` - Lịch đặt sân
- `chat_history` - Lịch sử chat

### Query mẫu:
```sql
SELECT p.product_id, p.product_name, p.price, p.description, pi.image_url 
FROM products p 
LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
WHERE p.product_name LIKE '%vợt%' 
LIMIT 4
```

## 🎨 Frontend

### JavaScript trong header.php:
- ✅ Lấy user_id từ PHP session
- ✅ Gửi request đến API
- ✅ Hiển thị HTML response
- ✅ Xử lý lỗi chi tiết

### CSS:
- ✅ Styling cho product cards
- ✅ Responsive design
- ✅ Hover effects

## 🔄 Luồng hoạt động

1. **User gửi tin nhắn** → Frontend
2. **Frontend gửi API** → Server Flask
3. **Server phân loại** → OpenAI function calling
4. **Tạo SQL query** → OpenAI
5. **Thực thi query** → MySQL
6. **Tạo response** → OpenAI
7. **Trả về frontend** → Hiển thị

## 📝 Ghi chú

- Server chạy trên port 5000
- Frontend chạy trên port 8088
- Cần có OpenAI API key
- Cần có MySQL database
- Hình ảnh lưu trong thư mục `images/`