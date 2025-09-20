# Tính năng Khung giờ Gợi ý (Alternative Time Slots)

## 🎯 Mục đích
Khi người dùng hỏi về khung giờ cụ thể mà không có sân trống, bot sẽ tự động tìm và gợi ý các khung giờ khác trong ngày còn sân trống.

## 🔧 Cách hoạt động

### 1. Xử lý bình thường
- Nếu có sân trống → trả kết quả như cũ
- Nếu không có sân → chuyển sang bước 2

### 2. Tìm khung giờ gợi ý
- Lấy `end_time` của khung giờ user hỏi (ví dụ 9h)
- Sinh thêm các khung giờ tiếp theo trong ngày (9h–10h, 10h–11h, 11h–12h...)
- Với mỗi khung giờ, chạy query check sân trống
- Nếu có sân → thêm vào list gợi ý

### 3. Format câu trả lời
```
❌ Khung 8h–9h đã kín.
👉 Nhưng có sân trống ở các khung giờ sau:
- 9h–10h: Sân 2, Sân 4
- 10h–11h: Sân 1, Sân 3, Sân 5
```

## 📝 Các hàm chính

### `extract_time_info_from_message(message)`
Trích xuất thông tin ngày, giờ bắt đầu và kết thúc từ message của user.

**Hỗ trợ các format:**
- Ngày: `ngày mai`, `hôm nay`, `ngày 20/9`, `20/9`
- Giờ: `8h-9h`, `8h–9h`, `từ 8h đến 9h`, `8h đến 9h`, `8:00-9:00`

### `find_alternative_time_slots(date, start_time, end_time, max_slots=5)`
Tìm các khung giờ thay thế khi khung giờ yêu cầu không có sân trống.

**Tham số:**
- `date`: Ngày cần tìm (YYYY-MM-DD)
- `start_time`: Giờ bắt đầu khung giờ yêu cầu (HH:MM:SS)
- `end_time`: Giờ kết thúc khung giờ yêu cầu (HH:MM:SS)
- `max_slots`: Số lượng khung giờ gợi ý tối đa (mặc định 5)

**Trả về:** List các dict chứa `time` và `courts`

### `generate_court_answer_with_alternatives(data, query, date, start_time, end_time)`
Tạo câu trả lời với khung giờ gợi ý nếu không có sân trống.

**Logic:**
1. Nếu có sân trống → trả kết quả bình thường
2. Nếu không có sân và có thông tin khung giờ → tìm alternatives
3. Nếu có alternatives → format câu trả lời gợi ý
4. Nếu không có alternatives → báo cả ngày không còn sân

## 🧪 Test Cases

### Test 1: Trích xuất thông tin thời gian
```python
# Input: "ngày mai 8h-9h có sân trống không"
# Output: date="2025-09-21", start_time="08:00:00", end_time="09:00:00"

# Input: "hôm nay từ 14h đến 16h có sân không"  
# Output: date="2025-09-20", start_time="14:00:00", end_time="16:00:00"
```

### Test 2: Tìm khung giờ thay thế
```python
# Input: date="2025-09-20", start_time="08:00:00", end_time="09:00:00"
# Output: [
#   {'time': '9h–10h', 'courts': ['Sân 2', 'Sân 4']},
#   {'time': '10h–11h', 'courts': ['Sân 1', 'Sân 3', 'Sân 5']}
# ]
```

### Test 3: Câu trả lời đầy đủ
```python
# Có sân trống:
# "✅ Có 2 sân trống:\n- Sân 1\n- Sân 2"

# Không có sân, có alternatives:
# "❌ Khung 8h–9h đã kín.\n👉 Nhưng có sân trống ở các khung giờ sau:\n- 9h–10h: Sân 2, Sân 4"

# Không có sân, không có alternatives:
# "😔 Cả ngày không còn sân trống nào rồi. Bạn thử ngày khác nhé!"
```

## 🚀 Cách sử dụng

### Trong hàm `chat()`:
```python
# Trích xuất thông tin khung giờ từ message
date, start_time, end_time = extract_time_info_from_message(message_text)

# Sử dụng logic mới với khung giờ gợi ý
response = generate_court_answer_with_alternatives(data, message_text, date, start_time, end_time)
```

## 📊 Lợi ích

1. **Trải nghiệm người dùng tốt hơn**: Không chỉ báo "không có sân" mà còn gợi ý khung giờ khác
2. **Tăng tỷ lệ đặt sân**: Người dùng có nhiều lựa chọn hơn
3. **Thông tin hữu ích**: Biết chính xác sân nào còn trống ở khung giờ nào
4. **Tự động hóa**: Không cần hỏi lại người dùng về khung giờ khác

## 🔄 Luồng xử lý

```
User hỏi khung giờ cụ thể
         ↓
Trích xuất thông tin thời gian
         ↓
Chạy query tìm sân trống
         ↓
Có sân trống? → Có → Trả kết quả bình thường
         ↓
        Không
         ↓
Tìm khung giờ thay thế
         ↓
Có alternatives? → Có → Format câu trả lời gợi ý
         ↓
        Không
         ↓
Báo cả ngày không còn sân
```

## ⚙️ Cấu hình

- **Giới hạn khung giờ**: Từ 6h đến 22h
- **Số lượng gợi ý tối đa**: 5 khung giờ
- **Khoảng thời gian**: Mỗi khung giờ 1 tiếng (8h-9h, 9h-10h...)
- **Trạng thái booking**: Chỉ loại trừ 'pending' và 'confirmed'
