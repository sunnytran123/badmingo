import mysql.connector
import paho.mqtt.client as mqtt
from datetime import datetime, timedelta
import time

# ===== CONFIG MYSQL =====
MYSQL_HOST = "127.0.0.1"
MYSQL_USER = "root"
MYSQL_PASS = ""   # thay bằng mật khẩu MySQL
MYSQL_DB   = "sunny_sport"

# ===== CONFIG MQTT =====
MQTT_SERVER = "broker.hivemq.com"
MQTT_PORT   = 1883
MQTT_TOPIC  = "badminton/device/control/sunny"

# ===== KẾT NỐI MYSQL =====
def connect_mysql():
    while True:
        try:
            db = mysql.connector.connect(
                host=MYSQL_HOST,
                user=MYSQL_USER,
                password=MYSQL_PASS,
                database=MYSQL_DB
            )
            print("[MySQL] Connected thành công")
            return db
        except Exception as e:
            print(f"[MySQL] Lỗi kết nối: {e}, thử lại sau 5s...")
            time.sleep(5)

# ===== KẾT NỐI MQTT =====
def connect_mqtt():
    client = mqtt.Client(client_id="Python_Server_001", protocol=mqtt.MQTTv311)
    while True:
        try:
            client.connect(MQTT_SERVER, MQTT_PORT, 60)
            print("[MQTT] Connected thành công")
            return client
        except Exception as e:
            print(f"[MQTT] Lỗi kết nối: {e}, thử lại sau 5s...")
            time.sleep(5)


def main_loop():
    db = connect_mysql()
    cursor = db.cursor(dictionary=True)
    client = connect_mqtt()

    while True:
        try:
            now = datetime.now()
            today = now.date()

            cursor.execute("""
                SELECT booking_id, court_id, booking_date, start_time, end_time, status
                FROM bookings
                WHERE booking_date = %s AND status != 'cancelled'
            """, (today,))
            bookings = cursor.fetchall()

            for b in bookings:
                start_dt = datetime.combine(
                    b['booking_date'],
                    datetime.strptime(str(b['start_time']), "%H:%M:%S").time()
                )
                end_dt = datetime.combine(
                    b['booking_date'],
                    datetime.strptime(str(b['end_time']), "%H:%M:%S").time()
                )
                court = b['court_id']

                if start_dt - timedelta(minutes=15) <= now <= end_dt:
                    msg = str(court)   # Ví dụ "1", "2", "3"
                    print(f"[{now}] MQTT -> Bật sân {court}")
                    client.publish(MQTT_TOPIC, msg)
                elif now > end_dt:
                    msg = str(court * 10)  # Ví dụ "10", "20", "30"
                    print(f"[{now}] MQTT -> Tắt sân {court}")
                    client.publish(MQTT_TOPIC, msg)

            time.sleep(60)  # Check mỗi 1 phút

        except mysql.connector.Error as e:
            print(f"[MySQL] Error: {e}, reconnect DB...")
            db = connect_mysql()
            cursor = db.cursor(dictionary=True)

        except Exception as e:
            print(f"[MAIN LOOP ERROR] {e}")
            time.sleep(5)

if __name__ == "__main__":
    main_loop()
