#!/usr/bin/env python3
"""
Script test cho logic thời gian cải tiến
"""

import mysql.connector
from datetime import datetime, timedelta

def test_time_logic():
    """Test logic thời gian cải tiến"""
    
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="sunny_sport"
        )
        cursor = conn.cursor(dictionary=True)
        
        print("=== TEST LOGIC THỜI GIAN CẢI TIẾN ===\n")
        
        # Test 1: Làm sạch SQL output
        print("1. Test làm sạch SQL output:")
        test_sql_with_blocks = """
        ```sql
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
        """
        
        from chatbot_badminton import clean_sql_output
        cleaned = clean_sql_output(test_sql_with_blocks)
        print(f"   Input: {test_sql_with_blocks[:50]}...")
        print(f"   Output: {cleaned[:50]}...")
        print(f"   Starts with SELECT: {cleaned.lower().startswith('select')}")
        print()
        
        # Test 2: Logic thời gian - booking đã kết thúc
        print("2. Test logic thời gian - booking đã kết thúc:")
        now = datetime.now()
        today = now.strftime('%Y-%m-%d')
        current_time = now.strftime('%H:%M:%S')
        
        print(f"   Thời gian hiện tại: {current_time}")
        
        # Query cũ (loại tất cả booking trong ngày)
        old_query = f"""
        SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
        FROM courts c 
        WHERE NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.court_id = c.court_id 
            AND b.booking_date = '{today}' 
            AND b.status IN ('pending', 'confirmed')
        )
        LIMIT 5
        """
        
        # Query mới (chỉ loại booking chưa kết thúc)
        new_query = f"""
        SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
        FROM courts c 
        WHERE NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.court_id = c.court_id 
            AND b.booking_date = '{today}' 
            AND b.status IN ('pending', 'confirmed')
            AND b.end_time > '{current_time}'
        )
        LIMIT 5
        """
        
        print("   Query cũ (loại tất cả booking trong ngày):")
        cursor.execute(old_query)
        old_results = cursor.fetchall()
        print(f"   - Số sân trống: {len(old_results)}")
        for court in old_results:
            print(f"     * {court['court_name']}")
        
        print("\n   Query mới (chỉ loại booking chưa kết thúc):")
        cursor.execute(new_query)
        new_results = cursor.fetchall()
        print(f"   - Số sân trống: {len(new_results)}")
        for court in new_results:
            print(f"     * {court['court_name']}")
        
        print(f"\n   Cải thiện: Tăng {len(new_results) - len(old_results)} sân trống")
        print()
        
        # Test 3: Kiểm tra bookings hiện tại
        print("3. Bookings hiện tại:")
        cursor.execute(f"""
        SELECT b.booking_id, c.court_name, b.start_time, b.end_time, b.status,
               CASE 
                   WHEN b.end_time <= '{current_time}' THEN 'Đã kết thúc'
                   WHEN b.start_time > '{current_time}' THEN 'Chưa bắt đầu'
                   ELSE 'Đang diễn ra'
               END as time_status
        FROM bookings b
        JOIN courts c ON b.court_id = c.court_id
        WHERE b.booking_date = '{today}'
        ORDER BY b.start_time
        """)
        current_bookings = cursor.fetchall()
        
        if current_bookings:
            for booking in current_bookings:
                print(f"   - {booking['court_name']}: {booking['start_time']}-{booking['end_time']} ({booking['status']}) - {booking['time_status']}")
        else:
            print("   - Không có booking nào")
        print()
        
        # Test 4: Test với ngày có booking (2025-09-19)
        print("4. Test với ngày có booking (2025-09-19):")
        test_date = "2025-09-19"
        test_time = "09:00:00"  # Giả sử hiện tại là 9h sáng
        
        # Query cũ
        old_query_test = f"""
        SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
        FROM courts c 
        WHERE NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.court_id = c.court_id 
            AND b.booking_date = '{test_date}' 
            AND b.status IN ('pending', 'confirmed')
        )
        LIMIT 5
        """
        
        # Query mới
        new_query_test = f"""
        SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
        FROM courts c 
        WHERE NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.court_id = c.court_id 
            AND b.booking_date = '{test_date}' 
            AND b.status IN ('pending', 'confirmed')
            AND b.end_time > '{test_time}'
        )
        LIMIT 5
        """
        
        print(f"   Giả sử hiện tại: {test_time}")
        
        cursor.execute(old_query_test)
        old_results_test = cursor.fetchall()
        print(f"   Query cũ - Số sân trống: {len(old_results_test)}")
        
        cursor.execute(new_query_test)
        new_results_test = cursor.fetchall()
        print(f"   Query mới - Số sân trống: {len(new_results_test)}")
        
        # Kiểm tra bookings ngày test
        cursor.execute(f"""
        SELECT b.booking_id, c.court_name, b.start_time, b.end_time, b.status,
               CASE 
                   WHEN b.end_time <= '{test_time}' THEN 'Đã kết thúc'
                   WHEN b.start_time > '{test_time}' THEN 'Chưa bắt đầu'
                   ELSE 'Đang diễn ra'
               END as time_status
        FROM bookings b
        JOIN courts c ON b.court_id = c.court_id
        WHERE b.booking_date = '{test_date}'
        ORDER BY b.start_time
        """)
        test_bookings = cursor.fetchall()
        
        if test_bookings:
            print(f"   Bookings ngày {test_date}:")
            for booking in test_bookings:
                print(f"     - {booking['court_name']}: {booking['start_time']}-{booking['end_time']} ({booking['status']}) - {booking['time_status']}")
        
        cursor.close()
        conn.close()
        
        print("\n=== KẾT THÚC TEST ===")
        
    except mysql.connector.Error as e:
        print(f"Lỗi kết nối database: {e}")
    except Exception as e:
        print(f"Lỗi: {e}")

if __name__ == "__main__":
    test_time_logic()
