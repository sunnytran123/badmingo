#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test script cho tính năng khung giờ gợi ý
"""

import sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from chatbot_badminton import extract_time_info_from_message, find_alternative_time_slots, generate_court_answer_with_alternatives

def test_extract_time_info():
    """Test hàm trích xuất thông tin thời gian"""
    print("=== TEST TRÍCH XUẤT THÔNG TIN THỜI GIAN ===")
    
    test_cases = [
        "ngày mai 8h-9h có sân trống không",
        "hôm nay từ 14h đến 16h có sân không",
        "ngày 20/9 9h-10h còn sân không",
        "8h sáng mai có sân trống không",
        "các khung giờ nào còn trống",
    ]
    
    for message in test_cases:
        date, start_time, end_time = extract_time_info_from_message(message)
        print(f"Message: '{message}'")
        print(f"  Date: {date}")
        print(f"  Start: {start_time}")
        print(f"  End: {end_time}")
        print()

def test_alternative_slots():
    """Test hàm tìm khung giờ thay thế"""
    print("=== TEST TÌM KHUNG GIỜ THAY THẾ ===")
    
    # Test với ngày hôm nay
    from datetime import datetime
    today = datetime.now().strftime('%Y-%m-%d')
    
    # Test khung giờ 8h-9h
    alternatives = find_alternative_time_slots(today, "08:00:00", "09:00:00")
    
    print(f"Khung giờ thay thế cho {today} 8h-9h:")
    for slot in alternatives:
        print(f"  {slot['time']}: {', '.join(slot['courts'])}")
    
    print(f"\nTìm thấy {len(alternatives)} khung giờ thay thế")

def test_full_response():
    """Test câu trả lời đầy đủ với alternatives"""
    print("=== TEST CÂU TRẢ LỜI ĐẦY ĐỦ ===")
    
    # Test case 1: Có sân trống
    print("Test case 1: Có sân trống")
    data_with_courts = [
        {'court_name': 'Sân 1', 'price_per_hour': 150000},
        {'court_name': 'Sân 2', 'price_per_hour': 150000}
    ]
    response1 = generate_court_answer_with_alternatives(data_with_courts, "test", "2025-09-20", "08:00:00", "09:00:00")
    print(f"Response: {response1}")
    print()
    
    # Test case 2: Không có sân, tìm alternatives
    print("Test case 2: Không có sân, tìm alternatives")
    data_empty = []
    response2 = generate_court_answer_with_alternatives(data_empty, "test", "2025-09-20", "08:00:00", "09:00:00")
    print(f"Response: {response2}")
    print()

if __name__ == "__main__":
    print("🚀 Bắt đầu test tính năng khung giờ gợi ý...\n")
    
    try:
        test_extract_time_info()
        test_alternative_slots()
        test_full_response()
        
        print("✅ Tất cả test hoàn thành!")
        
    except Exception as e:
        print(f"❌ Lỗi trong quá trình test: {e}")
        import traceback
        traceback.print_exc()
