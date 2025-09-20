#!/usr/bin/env python3
"""
Script test cho logic tìm sân cầu lông
"""

import mysql.connector
from datetime import datetime, timedelta

def test_court_booking_queries():
    """Test các trường hợp tìm sân cầu lông"""
    
    # Kết nối database
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="sunny_sport"
        )
        cursor = conn.cursor(dictionary=True)
        
        print("=== TEST LOGIC TÌM SÂN CẦU LÔNG ===\n")
        
        # Test 1: Lấy tất cả sân
        print("1. Tất cả sân có sẵn:")
        cursor.execute("SELECT court_id, court_name, description, price_per_hour FROM courts")
        all_courts = cursor.fetchall()
        for court in all_courts:
            print(f"   - {court['court_name']}: {court['price_per_hour']:,.0f} VNĐ/giờ")
        print()
        
        # Test 2: Sân trống ngày hôm nay (chỉ ngày)
        today = datetime.now().strftime('%Y-%m-%d')
        print(f"2. Sân trống ngày {today} (chỉ ngày):")
        query = f"""
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
        cursor.execute(query)
        available_courts = cursor.fetchall()
        if available_courts:
            for court in available_courts:
                print(f"   - {court['court_name']}: {court['price_per_hour']:,.0f} VNĐ/giờ")
        else:
            print("   - Không có sân trống")
        print()
        
        # Test 3: Sân trống ngày hôm nay từ 7h-9h (có khung giờ)
        print(f"3. Sân trống ngày {today} từ 07:00-09:00:")
        query = f"""
        SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
        FROM courts c 
        WHERE NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.court_id = c.court_id 
            AND b.booking_date = '{today}' 
            AND b.status IN ('pending', 'confirmed')
            AND NOT (b.end_time <= '07:00:00' OR b.start_time >= '09:00:00')
        )
        LIMIT 5
        """
        cursor.execute(query)
        available_courts_time = cursor.fetchall()
        if available_courts_time:
            for court in available_courts_time:
                print(f"   - {court['court_name']}: {court['price_per_hour']:,.0f} VNĐ/giờ")
        else:
            print("   - Không có sân trống trong khung giờ này")
        print()
        
        # Test 4: Kiểm tra bookings hiện tại
        print(f"4. Bookings hiện tại ngày {today}:")
        cursor.execute(f"""
        SELECT b.booking_id, c.court_name, b.start_time, b.end_time, b.status
        FROM bookings b
        JOIN courts c ON b.court_id = c.court_id
        WHERE b.booking_date = '{today}'
        ORDER BY b.start_time
        """)
        current_bookings = cursor.fetchall()
        if current_bookings:
            for booking in current_bookings:
                print(f"   - {booking['court_name']}: {booking['start_time']}-{booking['end_time']} ({booking['status']})")
        else:
            print("   - Không có booking nào")
        print()
        
        # Test 5: Test với ngày có booking
        print("5. Test với ngày có booking (2025-09-19):")
        test_date = "2025-09-19"
        query = f"""
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
        cursor.execute(query)
        available_courts_test = cursor.fetchall()
        if available_courts_test:
            for court in available_courts_test:
                print(f"   - {court['court_name']}: {court['price_per_hour']:,.0f} VNĐ/giờ")
        else:
            print("   - Không có sân trống")
        
        # Kiểm tra bookings ngày test
        cursor.execute(f"""
        SELECT b.booking_id, c.court_name, b.start_time, b.end_time, b.status
        FROM bookings b
        JOIN courts c ON b.court_id = c.court_id
        WHERE b.booking_date = '{test_date}'
        ORDER BY b.start_time
        """)
        test_bookings = cursor.fetchall()
        if test_bookings:
            print(f"   Bookings ngày {test_date}:")
            for booking in test_bookings:
                print(f"     - {booking['court_name']}: {booking['start_time']}-{booking['end_time']} ({booking['status']})")
        
        cursor.close()
        conn.close()
        
        print("\n=== KẾT THÚC TEST ===")
        
    except mysql.connector.Error as e:
        print(f"Lỗi kết nối database: {e}")
    except Exception as e:
        print(f"Lỗi: {e}")

if __name__ == "__main__":
    test_court_booking_queries()
