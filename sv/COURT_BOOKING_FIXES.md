# Khắc phục Logic Tìm Sân Cầu Lông

## Vấn đề ban đầu
- LLM sinh SQL sai logic khi tìm sân trống
- Sử dụng LEFT JOIN + điều kiện trên bảng booking → chỉ lọc sân đã có booking
- Kết quả: Bot báo "chưa có thông tin" khi có dữ liệu booking

## Giải pháp đã triển khai

### 1. Cải thiện Prompt (`handle_court_booking_query`)
- **Bảng chính**: Bắt đầu từ `courts` thay vì `bookings`
- **Logic tìm sân trống**: Sử dụng `NOT EXISTS` để loại trừ sân đã đặt
- **Xử lý khung giờ**: Kiểm tra chồng lấn thời gian với công thức `NOT (end_time <= start OR start_time >= end)`
- **Giới hạn kết quả**: Luôn có `LIMIT 5`

### 2. Thêm Validation (`validate_court_booking_sql`)
- Kiểm tra query bắt đầu từ bảng `courts`
- Kiểm tra sử dụng `NOT EXISTS` hoặc `NOT IN`
- Kiểm tra có `LIMIT` và tham chiếu `bookings`
- Fallback query chuẩn khi validation thất bại

### 3. Query Chuẩn (`create_standard_court_query`)
```sql
-- Chỉ có ngày
SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
FROM courts c 
WHERE NOT EXISTS (
    SELECT 1 FROM bookings b 
    WHERE b.court_id = c.court_id 
    AND b.booking_date = '2025-09-20' 
    AND b.status IN ('pending', 'confirmed')
)
LIMIT 5

-- Có khung giờ
SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
FROM courts c 
WHERE NOT EXISTS (
    SELECT 1 FROM bookings b 
    WHERE b.court_id = c.court_id 
    AND b.booking_date = '2025-09-20' 
    AND b.status IN ('pending', 'confirmed')
    AND NOT (b.end_time <= '07:00:00' OR b.start_time >= '09:00:00')
)
LIMIT 5
```

### 4. Cải thiện Response (`generate_court_answer`)
- Xử lý trường hợp không có dữ liệu
- Format response rõ ràng với số thứ tự
- Hiển thị giá và mô tả sân

## Các trường hợp được xử lý

### 1. Chỉ có ngày
- **Input**: "ngày mai có sân trống không"
- **Logic**: Loại trừ tất cả booking trong ngày với status 'pending'/'confirmed'

### 2. Có khung giờ
- **Input**: "ngày mai 7h-9h có sân trống không"  
- **Logic**: Loại trừ booking chồng lấn thời gian

### 3. Fallback
- **Khi**: LLM sinh SQL sai logic
- **Action**: Sử dụng query chuẩn với ngày hôm nay

## Test Cases

### Test 1: Sân trống ngày hôm nay
```python
# Query được sinh
SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
FROM courts c 
WHERE NOT EXISTS (
    SELECT 1 FROM bookings b 
    WHERE b.court_id = c.court_id 
    AND b.booking_date = '2025-01-20' 
    AND b.status IN ('pending', 'confirmed')
)
LIMIT 5
```

### Test 2: Sân trống có khung giờ
```python
# Query được sinh
SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
FROM courts c 
WHERE NOT EXISTS (
    SELECT 1 FROM bookings b 
    WHERE b.court_id = c.court_id 
    AND b.booking_date = '2025-01-20' 
    AND b.status IN ('pending', 'confirmed')
    AND NOT (b.end_time <= '07:00:00' OR b.start_time >= '09:00:00')
)
LIMIT 5
```

## Cải tiến bổ sung (v2)

### 5. Làm sạch SQL Output (`clean_sql_output`)
- **Vấn đề**: LLM trả về SQL trong code block (```sql ... ```)
- **Giải pháp**: Loại bỏ code block markers trước khi validate
- **Kết quả**: Validation hoạt động đúng

### 6. Logic thời gian cải tiến
- **Vấn đề**: Loại bỏ tất cả booking trong ngày, không phân biệt đã kết thúc
- **Giải pháp**: Chỉ loại booking chưa kết thúc (`b.end_time > current_time`)
- **Kết quả**: Sân có booking đã kết thúc được coi là trống

### 7. Fallback thông minh
- **Bước 1**: Tìm sân trống ngay bây giờ (có xem xét thời gian)
- **Bước 2**: Nếu không có, tìm sân trống cả ngày
- **Kết quả**: Luôn có thông tin hữu ích cho người dùng

## Kết quả mong đợi
- ✅ Tìm đúng sân trống thay vì sân đã đặt
- ✅ Xử lý đúng logic chồng lấn thời gian
- ✅ Xem xét booking đã kết thúc
- ✅ Làm sạch SQL output từ LLM
- ✅ Fallback thông minh khi LLM sinh SQL sai
- ✅ Response rõ ràng, dễ hiểu
- ✅ Giới hạn kết quả hợp lý (LIMIT 5)
