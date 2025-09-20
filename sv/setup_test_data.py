#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script để setup dữ liệu test cho tính năng khung giờ gợi ý
"""

import mysql.connector
import sys
import os

def connect_db():
    """Kết nối database"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="sunny_sport"
        )
        return conn
    except mysql.connector.Error as e:
        print(f"❌ Lỗi kết nối database: {e}")
        return None

def clear_test_data(conn):
    """Xóa dữ liệu test cũ"""
    try:
        cursor = conn.cursor()
        cursor.execute("DELETE FROM bookings WHERE booking_date = '2025-09-20'")
        conn.commit()
        print("✅ Đã xóa dữ liệu test cũ cho ngày 20/9/2025")
        cursor.close()
    except Exception as e:
        print(f"❌ Lỗi xóa dữ liệu cũ: {e}")

def insert_test_data(conn):
    """Thêm dữ liệu test mới"""
    try:
        cursor = conn.cursor()
        
        # Dữ liệu test cho ngày 20/9/2025
        test_bookings = [
            # Sáng: 6h-7h - Sân 1, 2, 3 đã đặt
            (100, 12, 1, '2025-09-20', '06:00:00', '07:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Nguyễn Văn A', '0911111111'),
            (101, 12, 2, '2025-09-20', '06:00:00', '07:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Trần Thị B', '0922222222'),
            (102, 12, 3, '2025-09-20', '06:00:00', '07:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Lê Văn C', '0933333333'),
            
            # Sáng: 7h-8h - Sân 1, 4 đã đặt (Sân 2, 3, 5 trống)
            (103, 12, 1, '2025-09-20', '07:00:00', '08:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Phạm Thị D', '0944444444'),
            (104, 12, 4, '2025-09-20', '07:00:00', '08:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Hoàng Văn E', '0955555555'),
            
            # Sáng: 8h-9h - TẤT CẢ SÂN ĐÃ ĐẶT (để test khung giờ gợi ý)
            (105, 12, 1, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Vũ Thị F', '0966666666'),
            (106, 12, 2, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Đặng Văn G', '0977777777'),
            (107, 12, 3, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Bùi Thị H', '0988888888'),
            (108, 12, 4, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Ngô Văn I', '0999999999'),
            (109, 12, 5, '2025-09-20', '08:00:00', '09:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', 'Dương Thị K', '0900000000'),
            
            # Sáng: 9h-10h - Sân 2, 4 trống (để test gợi ý)
            (110, 12, 1, '2025-09-20', '09:00:00', '10:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Lý Văn L', '0911111112'),
            (111, 12, 3, '2025-09-20', '09:00:00', '10:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Tôn Thị M', '0922222223'),
            (112, 12, 5, '2025-09-20', '09:00:00', '10:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', 'Võ Văn N', '0933333334'),
            
            # Sáng: 10h-11h - Sân 1, 3, 5 trống (để test gợi ý)
            (113, 12, 2, '2025-09-20', '10:00:00', '11:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Đinh Thị O', '0944444445'),
            (114, 12, 4, '2025-09-20', '10:00:00', '11:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Phan Văn P', '0955555556'),
            
            # Chiều: 14h-15h - Sân 1, 2 trống (để test gợi ý)
            (115, 12, 3, '2025-09-20', '14:00:00', '15:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Trương Thị Q', '0966666667'),
            (116, 12, 4, '2025-09-20', '14:00:00', '15:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Lâm Văn R', '0977777778'),
            (117, 12, 5, '2025-09-20', '14:00:00', '15:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', 'Hồ Thị S', '0988888889'),
            
            # Tối: 18h-19h - TẤT CẢ SÂN ĐÃ ĐẶT (để test khung giờ gợi ý)
            (118, 12, 1, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Chu Văn T', '0999999991'),
            (119, 12, 2, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Lưu Thị U', '0900000002'),
            (120, 12, 3, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Thạch Văn V', '0911111113'),
            (121, 12, 4, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Mai Thị W', '0922222224'),
            (122, 12, 5, '2025-09-20', '18:00:00', '19:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', 'Hứa Văn X', '0933333335'),
            
            # Tối: 19h-20h - Sân 2, 4 trống (để test gợi ý)
            (123, 12, 1, '2025-09-20', '19:00:00', '20:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Lý Văn Y', '0944444446'),
            (124, 12, 3, '2025-09-20', '19:00:00', '20:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Tôn Thị Z', '0955555557'),
            (125, 12, 5, '2025-09-20', '19:00:00', '20:00:00', 'ondelivery', 140000.00, 0.00, 'confirmed', 'Võ Văn AA', '0966666668'),
            
            # Tối: 20h-21h - Sân 1, 3, 5 trống (để test gợi ý)
            (126, 12, 2, '2025-09-20', '20:00:00', '21:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Đinh Thị BB', '0977777779'),
            (127, 12, 4, '2025-09-20', '20:00:00', '21:00:00', 'ondelivery', 150000.00, 0.00, 'confirmed', 'Phan Văn CC', '0988888890'),
        ]
        
        # Insert dữ liệu
        insert_query = """
        INSERT INTO bookings (booking_id, user_id, court_id, booking_date, start_time, end_time, 
                            payment_method, total_price, discount, status, fullname, phone) 
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        for booking in test_bookings:
            cursor.execute(insert_query, booking)
        
        conn.commit()
        print(f"✅ Đã thêm {len(test_bookings)} booking test cho ngày 20/9/2025")
        cursor.close()
        
    except Exception as e:
        print(f"❌ Lỗi thêm dữ liệu test: {e}")

def verify_data(conn):
    """Kiểm tra dữ liệu đã được thêm"""
    try:
        cursor = conn.cursor()
        cursor.execute("SELECT COUNT(*) FROM bookings WHERE booking_date = '2025-09-20'")
        count = cursor.fetchone()[0]
        print(f"📊 Tổng số booking cho ngày 20/9/2025: {count}")
        
        # Kiểm tra khung giờ 8h-9h (phải có 5 sân đã đặt)
        cursor.execute("""
            SELECT COUNT(*) FROM bookings 
            WHERE booking_date = '2025-09-20' 
            AND start_time = '08:00:00' 
            AND end_time = '09:00:00'
        """)
        count_8h = cursor.fetchone()[0]
        print(f"📊 Số sân đã đặt khung 8h-9h: {count_8h}")
        
        # Kiểm tra khung giờ 9h-10h (phải có 3 sân đã đặt, 2 sân trống)
        cursor.execute("""
            SELECT COUNT(*) FROM bookings 
            WHERE booking_date = '2025-09-20' 
            AND start_time = '09:00:00' 
            AND end_time = '10:00:00'
        """)
        count_9h = cursor.fetchone()[0]
        print(f"📊 Số sân đã đặt khung 9h-10h: {count_9h}")
        
        cursor.close()
        
    except Exception as e:
        print(f"❌ Lỗi kiểm tra dữ liệu: {e}")

def main():
    print("🚀 Bắt đầu setup dữ liệu test cho tính năng khung giờ gợi ý...")
    
    # Kết nối database
    conn = connect_db()
    if not conn:
        print("❌ Không thể kết nối database. Vui lòng kiểm tra cấu hình.")
        return
    
    try:
        # Xóa dữ liệu cũ
        clear_test_data(conn)
        
        # Thêm dữ liệu mới
        insert_test_data(conn)
        
        # Kiểm tra dữ liệu
        verify_data(conn)
        
        print("\n✅ Setup dữ liệu test hoàn thành!")
        print("\n📝 Bây giờ bạn có thể test các câu hỏi sau:")
        print("1. 'hôm nay 8h-9h có sân trống không' → Sẽ gợi ý khung giờ khác")
        print("2. 'hôm nay 7h-8h có sân trống không' → Sẽ có sân trống")
        print("3. 'hôm nay 18h-19h có sân trống không' → Sẽ gợi ý khung giờ khác")
        print("4. 'hôm nay 14h-15h có sân trống không' → Sẽ có sân trống")
        
    except Exception as e:
        print(f"❌ Lỗi trong quá trình setup: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    main()
