#!/usr/bin/env python3
import mysql.connector

def check_database():
    try:
        # Kết nối MySQL
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="sunny_sport"
        )
        print("✅ Kết nối database thành công")
        
        cursor = conn.cursor()
        
        # Kiểm tra bảng chat_history
        cursor.execute("DESCRIBE chat_history")
        columns = cursor.fetchall()
        print("\n📋 Structure bảng chat_history:")
        for col in columns:
            print(f"  {col[0]} - {col[1]} - {col[2]} - {col[3]}")
        
        # Kiểm tra data mới nhất
        cursor.execute("SELECT COUNT(*) FROM chat_history")
        count = cursor.fetchone()[0]
        print(f"\n📊 Tổng số records: {count}")
        
        cursor.execute("SELECT user_id, role, message, bot_disabled, created_at FROM chat_history ORDER BY created_at DESC LIMIT 3")
        recent = cursor.fetchall()
        print("\n🕒 3 records mới nhất:")
        for row in recent:
            print(f"  {row[0]} | {row[1]} | {row[2][:30]}... | {row[3]} | {row[4]}")
        
        # Test insert
        print("\n🧪 Test insert...")
        cursor.execute("INSERT INTO chat_history (user_id, role, message, bot_disabled) VALUES (%s, %s, %s, %s)", 
                      ("test_user", "user", "Test message", 0))
        conn.commit()
        print("✅ Test insert thành công")
        
        # Xóa test record
        cursor.execute("DELETE FROM chat_history WHERE user_id = 'test_user'")
        conn.commit()
        print("✅ Test cleanup thành công")
        
        cursor.close()
        conn.close()
        
    except Exception as e:
        print(f"❌ Lỗi: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    check_database()
