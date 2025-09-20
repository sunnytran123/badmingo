# 🧪 Test Cases cho Tính năng Khung giờ Gợi ý

## 📊 Dữ liệu Test
Đã tạo dữ liệu test trong file `test_booking_data.sql` cho ngày **20/9/2025** với các khung giờ:

### Khung giờ ĐÃ KÍN (tất cả sân):
- **6h-7h**: Sân 1, 2, 3 đã đặt
- **8h-9h**: TẤT CẢ SÂN (1,2,3,4,5) đã đặt
- **18h-19h**: TẤT CẢ SÂN (1,2,3,4,5) đã đặt

### Khung giờ CÒN SÂN TRỐNG:
- **7h-8h**: Sân 2, 3, 5 trống
- **9h-10h**: Sân 2, 4 trống
- **10h-11h**: Sân 1, 3, 5 trống
- **14h-15h**: Sân 1, 2 trống
- **19h-20h**: Sân 2, 4 trống
- **20h-21h**: Sân 1, 3, 5 trống

---

## 🎯 Test Cases

### Test Case 1: Có sân trống bình thường
**Input:** `"hôm nay 7h-8h có sân trống không"`

**Expected Response:**
```
✅ Có 3 sân trống:
- Sân 2
- Sân 3
- Sân 5
```

---

### Test Case 2: Không có sân, có khung giờ gợi ý
**Input:** `"hôm nay 8h-9h có sân trống không"`

**Expected Response:**
```
❌ Khung 8h–9h đã kín.
👉 Nhưng có sân trống ở các khung giờ sau:
- 9h–10h: Sân 2, Sân 4
- 10h–11h: Sân 1, Sân 3, Sân 5
- 11h–12h: Sân 1, Sân 2, Sân 3, Sân 4, Sân 5
- 12h–13h: Sân 1, Sân 2, Sân 3, Sân 4, Sân 5
- 13h–14h: Sân 1, Sân 2, Sân 3, Sân 4, Sân 5
```

---

### Test Case 3: Không có sân, có khung giờ gợi ý (chiều)
**Input:** `"hôm nay 18h-19h có sân trống không"`

**Expected Response:**
```
❌ Khung 18h–19h đã kín.
👉 Nhưng có sân trống ở các khung giờ sau:
- 19h–20h: Sân 2, Sân 4
- 20h–21h: Sân 1, Sân 3, Sân 5
- 21h–22h: Sân 1, Sân 2, Sân 3, Sân 4, Sân 5
```

---

### Test Case 4: Chỉ hỏi giờ đơn lẻ
**Input:** `"hôm nay 8h có sân trống không"`

**Expected Response:**
```
❌ Khung 8h–9h đã kín.
👉 Nhưng có sân trống ở các khung giờ sau:
- 9h–10h: Sân 2, Sân 4
- 10h–11h: Sân 1, Sân 3, Sân 5
- 11h–12h: Sân 1, Sân 2, Sân 3, Sân 4, Sân 5
- 12h–13h: Sân 1, Sân 2, Sân 3, Sân 4, Sân 5
- 13h–14h: Sân 1, Sân 2, Sân 3, Sân 4, Sân 5
```

---

### Test Case 5: Hỏi ngày mai
**Input:** `"ngày mai 8h-9h có sân trống không"`

**Expected Response:**
```
✅ Có 5 sân trống:
- Sân 1
- Sân 2
- Sân 3
- Sân 4
- Sân 5
```
*(Vì ngày mai chưa có booking nào)*

---

### Test Case 6: Hỏi khung giờ có sân trống
**Input:** `"hôm nay 14h-15h có sân trống không"`

**Expected Response:**
```
✅ Có 2 sân trống:
- Sân 1
- Sân 2
```

---

### Test Case 7: Hỏi khung giờ không cụ thể
**Input:** `"các khung giờ nào còn trống"`

**Expected Response:**
```
✅ Có 5 sân trống:
- Sân 1
- Sân 2
- Sân 3
- Sân 4
- Sân 5
```
*(Sẽ tìm sân trống ngay bây giờ)*

---

## 🔧 Cách chạy test

### Bước 1: Import dữ liệu test
```sql
-- Chạy file SQL để import dữ liệu test
source sv/test_booking_data.sql;
```

### Bước 2: Khởi động chatbot
```bash
cd sv
python chatbot_badminton.py
```

### Bước 3: Test qua frontend
1. Mở trình duyệt: `http://localhost/BadminGo`
2. Click vào bubble chat
3. Nhập các câu hỏi test ở trên
4. Kiểm tra response có đúng như expected không

### Bước 4: Test qua API trực tiếp
```bash
curl -X POST http://localhost:5000/api/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "hôm nay 8h-9h có sân trống không", "user_id": "test"}'
```

---

## 📋 Checklist Test

- [ ] Test Case 1: Có sân trống bình thường
- [ ] Test Case 2: Không có sân, có khung giờ gợi ý (sáng)
- [ ] Test Case 3: Không có sân, có khung giờ gợi ý (tối)
- [ ] Test Case 4: Chỉ hỏi giờ đơn lẻ
- [ ] Test Case 5: Hỏi ngày mai
- [ ] Test Case 6: Hỏi khung giờ có sân trống
- [ ] Test Case 7: Hỏi khung giờ không cụ thể

---

## 🐛 Debug Tips

### Nếu không hoạt động:
1. **Kiểm tra database**: Đảm bảo dữ liệu test đã được import
2. **Kiểm tra thời gian**: Đảm bảo server chạy đúng ngày 20/9/2025
3. **Kiểm tra logs**: Xem console log để debug
4. **Kiểm tra SQL**: Test query trực tiếp trong MySQL

### Logs quan trọng:
```
Court query: SELECT c.court_id, c.court_name...
Court data: [{'court_id': 1, 'court_name': 'Sân 1'...}]
Court response: ❌ Khung 8h–9h đã kín...
```

---

## 🎉 Kết quả mong đợi

Sau khi test, bạn sẽ thấy:
- ✅ Bot trả lời chính xác khi có sân trống
- ✅ Bot gợi ý khung giờ thay thế khi không có sân
- ✅ Format câu trả lời đẹp và dễ đọc
- ✅ Logic thông minh, không cần hỏi lại user
