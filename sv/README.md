# Sunny Sport Chatbot Server

## Mô tả
Flask server cung cấp API chat cho website Sunny Sport, hỗ trợ tìm kiếm sản phẩm và thông tin sân cầu lông.

## Cài đặt

### 1. Cài đặt Python dependencies
```bash
pip install flask flask-cors openai mysql-connector-python
```

### 2. Cấu hình Database
Đảm bảo MySQL server đang chạy và database `sunny_sport` đã được tạo.

### 3. Chạy server

#### Cách 1: Sử dụng script tự động
```bash
python run_server.py
```

#### Cách 2: Chạy trực tiếp
```bash
python chatbotsever.py
```

## API Endpoints

### 1. Chat API
- **URL**: `http://localhost:8000/api/chat`
- **Method**: POST
- **Body**:
```json
{
    "message": "Tôi muốn tìm vợt cầu lông",
    "user_id": "12"
}
```

### 2. Health Check
- **URL**: `http://localhost:8000/api/health`
- **Method**: GET

## Các chức năng hỗ trợ

### 1. Tư vấn thông tin shop
- Địa chỉ shop
- Số điện thoại
- Email
- Website
- Facebook, Instagram
- Giờ mở cửa

### 2. Tìm kiếm sân cầu lông
- Danh sách sân trống
- Giá thuê sân
- Thông tin sân

## Troubleshooting

### Lỗi kết nối database
- Kiểm tra MySQL server đang chạy
- Kiểm tra thông tin kết nối trong code
- Đảm bảo database `sunny_sport` tồn tại

### Lỗi OpenAI API
- Kiểm tra API key có hợp lệ
- Kiểm tra kết nối internet

### Lỗi CORS
- Server đã được cấu hình CORS cho phép tất cả origins
- Nếu vẫn lỗi, kiểm tra URL trong frontend

## Cấu trúc file
```
sv/
├── chatbotsever.py      # Flask server chính
├── run_server.py        # Script chạy server
├── serverlinhkien.py    # Server cũ (backup)
├── server.py            # MQTT server
└── README.md           # Hướng dẫn này
```

