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




# #include <WiFi.h>
# #include <PubSubClient.h>
# #include <ESP32Servo.h>
#
# // WiFi
# // const char* ssid = "baocao";
# // const char* password = "12345678";
# const char *ssid = "Wokwi-GUEST";
# const char *password = "";
# // Public MQTT broker HiveMQ
# const char* mqtt_server = "broker.hivemq.com";
# const int mqtt_port = 1883;
# const char* mqtt_topic = "badminton/device/control/sunny";
#
# WiFiClient espClient;
# PubSubClient client(espClient);
#
# // LED pins (giả lập đèn sân)
# const int led1 = 32;
# const int led2 = 12;
# const int led3 = 23;
# const int led4 = 4;
#
# // Servo giả lập quạt
# Servo servo1, servo2, servo3, servo4;
#
# void callback(char* topic, byte* message, unsigned int length) {
#   String msg;
#   for (int i = 0; i < length; i++) {
#     msg += (char)message[i];
#   }
#   Serial.println("Received: " + msg);
#
#   if (msg == "1") {  // Bật sân 1
#     digitalWrite(led1, HIGH);
#     for (int pos = 0; pos <= 90; pos++) {
#       servo1.write(pos);
#       delay(15);
#     }
#     Serial.println("DEBUG: Đã bật sân 1");
#   } else if (msg == "2") {
#     digitalWrite(led2, HIGH);
#     for (int pos = 0; pos <= 90; pos++) {
#       servo2.write(pos);
#       delay(15);
#     }
#     Serial.println("DEBUG: Đã bật sân 2");
#   } else if (msg == "3") {
#     digitalWrite(led3, HIGH);
#     for (int pos = 0; pos <= 90; pos++) {
#       servo3.write(pos);
#       delay(15);
#     }
#     Serial.println("DEBUG: Đã bật sân 3");
#   } else if (msg == "4") {
#     digitalWrite(led4, HIGH);
#     for (int pos = 0; pos <= 90; pos++) {
#       servo4.write(pos);
#       delay(15);
#     }
#     Serial.println("DEBUG: Đã bật sân 4");
#   } else if (msg == "10") { // Tắt sân 1
#     digitalWrite(led1, LOW);
#     for (int pos = 90; pos >= 0; pos--) {
#       servo1.write(pos);
#       delay(15);
#     }
#     Serial.println("DEBUG: Đã tắt sân 1");
#   } else if (msg == "20") {
#     digitalWrite(led2, LOW);
#     for (int pos = 90; pos >= 0; pos--) {
#       servo2.write(pos);
#       delay(15);
#     }
#     Serial.println("DEBUG: Đã tắt sân 2");
#   } else if (msg == "30") {
#     digitalWrite(led3, LOW);
#     for (int pos = 90; pos >= 0; pos--) {
#       servo3.write(pos);
#       delay(15);
#     }
#     Serial.println("DEBUG: Đã tắt sân 3");
#   } else if (msg == "40") {
#     digitalWrite(led4, LOW);
#     for (int pos = 90; pos >= 0; pos--) {
#       servo4.write(pos);
#       delay(15);
#     }
#     Serial.println("DEBUG: Đã tắt sân 4");
#   } else {
#     Serial.println("DEBUG: Lệnh không hợp lệ -> " + msg);
#   }
# }
#
#
# void setup_wifi() {
#   delay(10);
#   Serial.println("Connecting to WiFi...");
#   WiFi.begin(ssid, password);
#
#   while (WiFi.status() != WL_CONNECTED) {
#     delay(500);
#     Serial.print(".");
#   }
#   Serial.println("\nWiFi connected");
# }
#
# void reconnect() {
#   while (!client.connected()) {
#     Serial.print("Attempting MQTT connection...");
# // ESP32
#   if (client.connect("ESP32_Sunny_001")) {
#     Serial.println("MQTT connected, subscribing...");
#     client.subscribe(mqtt_topic);
#     Serial.print("Subscribed to: ");
#     Serial.println(mqtt_topic);
#   }
#   else {
#       Serial.print("failed, rc=");
#       Serial.print(client.state());
#       delay(2000);
#     }
#   }
# }
#
# void setup() {
#   Serial.begin(115200);
#
#   pinMode(led1, OUTPUT);
#   pinMode(led2, OUTPUT);
#   pinMode(led3, OUTPUT);
#   pinMode(led4, OUTPUT);
#
#   servo1.attach(33);
#   servo2.attach(13);
#   servo3.attach(22);
#   servo4.attach(2);
#
#   setup_wifi();
#   client.setServer(mqtt_server, mqtt_port);
#   client.setCallback(callback);
# }
#
# void loop() {
#   if (!client.connected()) {
#     reconnect();
#   }
#   client.loop();
# }
