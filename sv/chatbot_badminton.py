from flask import Flask, request, jsonify
from openai import OpenAI
import mysql.connector
from datetime import datetime, timedelta
from decimal import Decimal
import json
import uuid
import paho.mqtt.client as mqtt
import time
import threading

app = Flask(__name__)


# ===== CONFIG MQTT =====
MQTT_SERVER = "broker.hivemq.com"
MQTT_PORT = 1883
MQTT_TOPIC = "badminton/device/control/sunny"

# ===== MQTT CLIENT GLOBAL =====
mqtt_client = None
mqtt_connected = False

#
# Cấu hình CORS thủ công
@app.after_request
def after_request(response):
    response.headers.add('Access-Control-Allow-Origin', '*')
    response.headers.add('Access-Control-Allow-Headers', 'Content-Type,Authorization')
    response.headers.add('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS')
    return response


def ket_noi_db():
    """Tạo và trả về kết nối tới cơ sở dữ liệu MySQL"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="sunny_sport"
        )
        return conn
    except mysql.connector.Error as e:
        print(f"Lỗi kết nối cơ sở dữ liệu: {e}")
        return None


# ===== MQTT FUNCTIONS =====
def on_mqtt_connect(client, userdata, flags, rc):
    """Callback khi kết nối MQTT thành công"""
    global mqtt_connected
    if rc == 0:
        print("[MQTT] Connected thành công")
        mqtt_connected = True
    else:
        print(f"[MQTT] Lỗi kết nối: {rc}")
        mqtt_connected = False


def on_mqtt_disconnect(client, userdata, rc):
    """Callback khi mất kết nối MQTT"""
    global mqtt_connected
    mqtt_connected = False
    print("[MQTT] Mất kết nối")


def connect_mqtt():
    """Kết nối tới MQTT broker"""
    global mqtt_client, mqtt_connected
    
    if mqtt_client and mqtt_connected:
        return mqtt_client
    
    try:
        mqtt_client = mqtt.Client(client_id="Chatbot_Server_001", protocol=mqtt.MQTTv311)
        mqtt_client.on_connect = on_mqtt_connect
        mqtt_client.on_disconnect = on_mqtt_disconnect
        
        mqtt_client.connect(MQTT_SERVER, MQTT_PORT, 60)
        mqtt_client.loop_start()  # Chạy loop trong background thread
        
        # Đợi kết nối thành công
        timeout = 10
        while not mqtt_connected and timeout > 0:
            time.sleep(1)
            timeout -= 1
            
        if mqtt_connected:
            print("[MQTT] Kết nối thành công")
            return mqtt_client
        else:
            print("[MQTT] Timeout kết nối")
            return None
            
    except Exception as e:
        print(f"[MQTT] Lỗi kết nối: {e}")
        return None


def send_mqtt_command(court_id, action):
    """Gửi lệnh điều khiển thiết bị qua MQTT"""
    global mqtt_client, mqtt_connected
    
    try:
        if not mqtt_client or not mqtt_connected:
            mqtt_client = connect_mqtt()
            
        if mqtt_client and mqtt_connected:
            if action == "on":
                msg = str(court_id)  # Ví dụ "1", "2", "3"
                print(f"[{datetime.now()}] MQTT -> Bật sân {court_id}")
            elif action == "off":
                msg = str(court_id * 10)  # Ví dụ "10", "20", "30"
                print(f"[{datetime.now()}] MQTT -> Tắt sân {court_id}")
            else:
                print(f"[MQTT] Action không hợp lệ: {action}")
                return False
                
            mqtt_client.publish(MQTT_TOPIC, msg)
            return True
        else:
            print("[MQTT] Không thể kết nối để gửi lệnh")
            return False
            
    except Exception as e:
        print(f"[MQTT] Lỗi gửi lệnh: {e}")
        return False


def check_and_control_court_devices():
    """Kiểm tra và điều khiển thiết bị sân cầu lông theo lịch booking"""
    try:
        conn = ket_noi_db()
        if not conn:
            return
            
        cursor = conn.cursor(dictionary=True)
        now = datetime.now()
        today = now.date()
        
        # Lấy tất cả booking hôm nay
        cursor.execute("""
            SELECT booking_id, court_id, booking_date, start_time, end_time, status
            FROM bookings
            WHERE booking_date = %s AND status IN ('pending', 'confirmed')
        """, (today,))
        bookings = cursor.fetchall()
        
        for booking in bookings:
            start_dt = datetime.combine(
                booking['booking_date'],
                datetime.strptime(str(booking['start_time']), "%H:%M:%S").time()
            )
            end_dt = datetime.combine(
                booking['booking_date'],
                datetime.strptime(str(booking['end_time']), "%H:%M:%S").time()
            )
            court_id = booking['court_id']
            
            # Bật thiết bị 15 phút trước giờ bắt đầu
            if start_dt - timedelta(minutes=15) <= now <= end_dt:
                send_mqtt_command(court_id, "on")
            # Tắt thiết bị sau giờ kết thúc
            elif now > end_dt:
                send_mqtt_command(court_id, "off")
        
        cursor.close()
        conn.close()
        
    except Exception as e:
        print(f"[IoT] Lỗi kiểm tra thiết bị: {e}")


def start_iot_monitoring():
    """Khởi động thread theo dõi và điều khiển thiết bị IoT"""
    def iot_loop():
        while True:
            try:
                check_and_control_court_devices()
                time.sleep(60)  # Kiểm tra mỗi 1 phút
            except Exception as e:
                print(f"[IoT] Lỗi trong vòng lặp IoT: {e}")
                time.sleep(60)
    
    # Khởi động thread IoT
    iot_thread = threading.Thread(target=iot_loop, daemon=True)
    iot_thread.start()
    print("[IoT] Đã khởi động monitoring thiết bị IoT")


def clean_sql_output(sql_string):
    """Làm sạch SQL output từ LLM, loại bỏ code block và ký tự đặc biệt"""
    if not sql_string:
        return ""

    # Loại bỏ code block markers
    cleaned = sql_string.replace('```sql', '').replace('```', '').strip()

    # Loại bỏ các ký tự đặc biệt khác
    cleaned = cleaned.replace('`', '').strip()

    # Loại bỏ dòng trống đầu và cuối
    lines = cleaned.split('\n')
    cleaned_lines = [line.strip() for line in lines if line.strip()]
    cleaned = '\n'.join(cleaned_lines)

    return cleaned


def create_standard_court_query(date, start_time=None, end_time=None, current_time=None):
    """Tạo SQL query chuẩn để tìm sân trống với logic chồng lấn thời gian chính xác"""

    if start_time and end_time:
        # Trường hợp có khung giờ cụ thể - ưu tiên kiểm tra chồng lấn thời gian
        query = """
    SELECT c.court_id, c.court_name, c.description, c.price_per_hour
    FROM courts c
    WHERE NOT EXISTS (
        SELECT 1
        FROM bookings b
        WHERE b.court_id = c.court_id
          AND b.booking_date = %s
          AND b.status IN ('pending','confirmed')
          AND NOT (b.end_time <= %s OR b.start_time >= %s)
    )
    LIMIT 5"""
        params = (date, start_time, end_time)

    elif current_time:
        # Trường hợp hôm nay mà không có khung giờ cụ thể - chỉ loại booking chưa kết thúc
        query = """
    SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
    FROM courts c 
    WHERE NOT EXISTS (
        SELECT 1 FROM bookings b 
        WHERE b.court_id = c.court_id 
        AND b.booking_date = %s 
        AND b.status IN ('pending', 'confirmed')
        AND b.end_time > %s
    )
    LIMIT 5"""
        params = (date, current_time)

    else:
        # Trường hợp ngày khác không có khung giờ - loại tất cả booking trong ngày
        query = """
    SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
    FROM courts c 
    WHERE NOT EXISTS (
        SELECT 1 FROM bookings b 
        WHERE b.court_id = c.court_id 
        AND b.booking_date = %s 
        AND b.status IN ('pending', 'confirmed')
    )
    LIMIT 5"""
        params = (date,)

    return query, params


def validate_court_booking_sql(query):
    """Kiểm tra SQL query có đúng logic tìm sân trống không"""
    query_lower = query.lower().strip()

    # Kiểm tra có bắt đầu từ bảng courts không
    if not query_lower.startswith('select') or 'from courts' not in query_lower:
        return False, "Query phải bắt đầu từ bảng courts"

    # Kiểm tra có sử dụng NOT EXISTS hoặc NOT IN để loại trừ booking không
    if 'not exists' not in query_lower and 'not in' not in query_lower:
        return False, "Query phải sử dụng NOT EXISTS hoặc NOT IN để loại trừ sân đã đặt"

    # Kiểm tra có giới hạn kết quả không
    if 'limit' not in query_lower:
        return False, "Query phải có LIMIT để giới hạn kết quả"

    # Kiểm tra có tham chiếu đến bảng bookings không
    if 'bookings' not in query_lower:
        return False, "Query phải tham chiếu đến bảng bookings"

    return True, "Query hợp lệ"


def execute_query(query, params=None):
    """Thực thi câu truy vấn MySQL và trả về kết quả"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="sunny_sport"
        )
        cursor = conn.cursor(dictionary=True)
        if params:
            cursor.execute(query, params)
        else:
            cursor.execute(query)
        results = cursor.fetchall()
        cursor.close()
        conn.close()
        return results
    except mysql.connector.Error as e:
        print(f"Lỗi thực thi truy vấn MySQL: {e}")
        return []
    except Exception as e:
        print(f"Lỗi khác: {e}")
        return []


# Định nghĩa các công cụ (tools) cho function calling
tools = [
    {
        "type": "function",
        "function": {
            "name": "classify_user_request",
            "description": "Phân loại yêu cầu của người dùng là đặt sân, tìm kiếm sản phẩm, hoặc tư vấn chung.",
            "parameters": {
                "type": "object",
                "properties": {
                    "request_type": {
                        "type": "string",
                        "enum": ["court_booking", "product_search", "consultation"],
                        "description": "Loại yêu cầu: đặt sân, tìm kiếm sản phẩm, hoặc tư vấn chung."
                    },
                    "message": {
                        "type": "string",
                        "description": "Tin nhắn gốc của người dùng."
                    },
                    "additional_info_needed": {
                        "type": "string",
                        "description": "Thông tin bổ sung cần yêu cầu từ người dùng nếu request_type là need_more_info."
                    }
                },
                "required": ["request_type", "message"],
                "additionalProperties": False
            }
        }
    }
]


def classify_user_request(query):
    """Sử dụng function calling để phân loại yêu cầu người dùng"""
    try:
        prompt = f"""Câu yêu cầu của người dùng cung cấp là: '{query}'
        Bạn là bot hỗ trợ tư vấn sân cầu lông và sản phẩm cầu lông. Tên bạn là Sunny Sport:

        Phân tích câu hỏi của người dùng và xác định yêu cầu của họ:

        - Nếu người dùng hỏi về đặt sân cầu lông (ví dụ: đặt sân, sân trống, giá sân, thời gian), trả về request_type='court_booking'.
        - Nếu người dùng muốn tìm kiếm sản phẩm cầu lông (ví dụ: vợt, giày, áo, phụ kiện), trả về request_type='product_search'.
        - Nếu người dùng hỏi về thông tin chung (ví dụ: giới thiệu shop, chính sách, liên hệ), trả về request_type='consultation'.

        Trả về định dạng JSON theo schema của công cụ classify_user_request."""

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system",
                 "content": "Bạn là một trợ lý phân loại yêu cầu người dùng chính xác và chuyên nghiệp."},
                {"role": "user", "content": prompt}
            ],
            tools=tools,
            tool_choice={"type": "function", "function": {"name": "classify_user_request"}},
            temperature=0.5
        )

        tool_call = response.choices[0].message.tool_calls[0]
        arguments = json.loads(tool_call.function.arguments)
        return arguments
    except Exception as e:
        print(f"Lỗi phân loại yêu cầu: {e}")
        return {
            "request_type": "consultation",
            "message": query,
            "additional_info_needed": "Xin lỗi, tôi chưa hiểu ý bạn! Bạn muốn đặt sân cầu lông hay tìm kiếm sản phẩm nào?"
        }


def handle_court_booking_query(query):
    """Xử lý câu hỏi về đặt sân cầu lông"""
    try:
        prompt = f"""Bạn là một chuyên viên tư vấn đặt sân cầu lông chuyên nghiệp. Khách hàng hỏi: {query}

        Bạn dựa vào câu hỏi của người dùng để tạo ra câu truy vấn mysql tìm kiếm SÂN CÒN TRỐNG.

        QUAN TRỌNG: 
        - Bảng chính là COURTS (thông tin sân)
        - Tìm sân TRỐNG bằng cách LOẠI TRỪ các sân đã có booking
        - Sử dụng NOT EXISTS hoặc NOT IN để loại trừ sân đã đặt
        - CHỈ TRẢ VỀ CÂU TRUY VẤN MYSQL KHÔNG ĐƯỢC CÓ CÁC KÝ TỰ ĐẶT BIỆT GÌ HẾT
        - Giới hạn kết quả LIMIT 5
        - PHÂN TÍCH KỸ: Nếu hỏi "18h-20h ngày mai" thì tìm booking chồng lấn khung giờ này
        - XỬ LÝ THỜI GIAN: 
          + "ngày mai" = CURDATE() + INTERVAL 1 DAY
          + "hôm nay" = CURDATE()
          + "18h-20h" = start_time = '18:00:00', end_time = '20:00:00'

        LOGIC TÌM SÂN TRỐNG:
        1. Nếu hỏi về khung giờ cụ thể (ví dụ: 18h-20h ngày mai): 
           - Loại trừ sân có booking CHỒNG LẤN với khung giờ yêu cầu
           - Chồng lấn khi: NOT (b.end_time <= 'start_time' OR b.start_time >= 'end_time')
           - KHÔNG dùng CURTIME() cho ngày mai
        2. Nếu chỉ hỏi về ngày hôm nay: 
           - Loại trừ sân có booking chưa kết thúc (b.end_time > CURTIME())
        3. Nếu chỉ hỏi về ngày mai (không có giờ): 
           - Loại trừ TẤT CẢ booking trong ngày mai
        4. Luôn bắt đầu từ bảng courts và loại trừ qua bookings
        5. QUAN TRỌNG: Phân biệt rõ hôm nay vs ngày mai

        Cơ sở dữ liệu `sunny_sport`:
        - *courts*: `court_id`, `court_name`, `description`, `price_per_hour`
        - *bookings*: `booking_id`, `court_id`, `booking_date`, `start_time`, `end_time`, `status`

        Ví dụ SQL mẫu:

        # Trường hợp 1: Chỉ có ngày (xem xét thời gian hiện tại)
        # Mô tả: Tìm sân trống ngày 20/9, chỉ loại booking chưa kết thúc (sau thời gian hiện tại)
        # QUAN TRỌNG: Sử dụng thời gian hiện tại thực tế, không hardcode
        SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
        FROM courts c 
        WHERE NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.court_id = c.court_id 
            AND b.booking_date = '2025-09-20' 
            AND b.status IN ('pending', 'confirmed')
            AND b.end_time > CURTIME()
        )
        LIMIT 5

        # Trường hợp 2: Có khung giờ cụ thể (ngày mai)
        # Mô tả: Tìm sân trống ngày mai từ 18h-20h, loại booking chồng lấn thời gian
        # Khi nào dùng: Người dùng hỏi "ngày mai 18h-20h có sân trống không"
        # QUAN TRỌNG: Không dùng CURTIME() cho ngày mai
        # LOGIC: Loại sân có booking chồng lấn với 18h-20h:
        # - Booking 17h-19h: chồng lấn (19h > 18h và 17h < 20h)
        # - Booking 18h-20h: chồng lấn (trùng khớp)
        # - Booking 19h-21h: chồng lấn (19h < 20h và 21h > 18h)
        # - Booking 16h-18h: KHÔNG chồng lấn (18h = 18h)
        # - Booking 20h-22h: KHÔNG chồng lấn (20h = 20h)
        SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
        FROM courts c 
        WHERE NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.court_id = c.court_id 
            AND b.booking_date = CURDATE() + INTERVAL 1 DAY
            AND b.status IN ('pending', 'confirmed')
            AND NOT (b.end_time <= '18:00:00' OR b.start_time >= '20:00:00')
        )
        LIMIT 5

        # Trường hợp 3: Chỉ có ngày mai, không xem xét thời gian
        # Mô tả: Tìm sân trống ngày mai, loại tất cả booking trong ngày mai
        # Khi nào dùng: Người dùng hỏi "ngày mai có sân trống không" (không quan tâm giờ)
        SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
        FROM courts c 
        WHERE NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.court_id = c.court_id 
            AND b.booking_date = CURDATE() + INTERVAL 1 DAY
            AND b.status IN ('pending', 'confirmed')
        )
        LIMIT 5

        # Trường hợp 4: Chỉ có ngày hôm nay, xem xét thời gian hiện tại
        # Mô tả: Tìm sân trống hôm nay, chỉ loại booking chưa kết thúc
        # Khi nào dùng: Người dùng hỏi "hôm nay có sân trống không"
        SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
        FROM courts c 
        WHERE NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.court_id = c.court_id 
            AND b.booking_date = CURDATE()
            AND b.status IN ('pending', 'confirmed')
            AND b.end_time > CURTIME()
        )
        LIMIT 5
        """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system", "content": "Bạn là một chuyên gia truy vấn dữ liệu mysql, chuyên tìm sân trống."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.3,
            max_tokens=2000
        )
        raw_sql = response.choices[0].message.content.strip()
        # Làm sạch SQL output từ LLM
        cleaned_sql = clean_sql_output(raw_sql)
        return cleaned_sql
    except Exception as e:
        print(f"Lỗi xử lý đặt sân: {e}")
        return "Xin lỗi, tôi gặp khó khăn khi xử lý yêu cầu. Tôi có thể giúp gì về đặt sân cầu lông?"


def handle_product_search_query(query):
    """Xử lý câu hỏi tìm kiếm sản phẩm cầu lông"""
    try:
        prompt = f"""Bạn là một chuyên viên tư vấn sản phẩm cầu lông chuyên nghiệp. Khách hàng hỏi: {query}

        Bạn dựa vào câu hỏi của người dùng để tạo ra câu truy vấn mysql tìm kiếm SẢN PHẨM cầu lông phù hợp yêu cầu người dùng.
        # Chú ý: không cần trả lời câu hỏi của khách hàng mà chỉ cần tạo ra câu truy vấn mysql select tìm kiếm các thông tin theo yêu cầu của khách hàng.
        # Lưu ý thêm phần giới hạn top 4 kết quả và nên dùng like cho tìm kiếm trên text
        # QUAN TRỌNG: Phải JOIN với bảng product_images để lấy hình ảnh chính (is_primary = 1)
        CHỈ TRẢ VỀ CÂU TRUY VẤN MYSQL KHÔNG ĐƯỢC CÓ CÁC KÝ TỰ ĐẶT BIỆT GÌ HẾT.

        Cơ sở dữ liệu `sunny_sport` bao gồm các bảng chính liên quan đến sản phẩm cầu lông:

        - *products*: Lưu thông tin sản phẩm với các cột: `product_id` (ID, khóa chính), `product_name` (tên sản phẩm, text), `description` (mô tả, text), `price` (giá, decimal), `stock` (tồn kho, int), `category_id` (ID danh mục).
        - *product_categories*: Danh mục sản phẩm, gồm `category_id` (ID, khóa chính), `category_name` (tên danh mục, text), `description` (mô tả, text).
        - *product_images*: Hình ảnh sản phẩm, gồm `image_id` (ID), `product_id` (ID sản phẩm), `image_url` (đường dẫn ảnh, text), `is_primary` (ảnh chính, tinyint).
        - *product_variants*: Biến thể sản phẩm, gồm `variant_id` (ID), `product_id` (ID sản phẩm), `size` (kích thước, text), `color` (màu sắc, text), `stock` (tồn kho, int).

        Bảng `products` liên kết với các bảng khác qua khóa ngoại để hỗ trợ tìm kiếm theo danh mục, giá, và các thuộc tính khác.

        Ví dụ query mẫu:
        SELECT p.product_id, p.product_name, p.price, p.description, pi.image_url 
        FROM products p 
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
        WHERE p.product_name LIKE '%vợt%' 
        LIMIT 4
        """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system", "content": "Bạn là một chuyên gia truy vấn dữ liệu mysql."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.7,
            max_tokens=2000
        )
        return response.choices[0].message.content.strip().replace('```sql', '').replace('```', '')
    except Exception as e:
        print(f"Lỗi xử lý tìm kiếm sản phẩm: {e}")
        return "Xin lỗi, tôi gặp khó khăn khi xử lý yêu cầu. Tôi có thể giúp gì về sản phẩm cầu lông?"


def handle_consultation_query(query):
    """Xử lý câu hỏi tư vấn chung"""
    try:
        prompt = f"""Bạn là một chuyên viên tư vấn shop cầu lông Sunny Sport chuyên nghiệp. Khách hàng hỏi: {query}

        Bạn dựa vào câu hỏi của người dùng để tạo ra câu truy vấn mysql tìm kiếm các thông tin theo yêu cầu.
        # Chú ý: không cần trả lời câu hỏi của khách hàng mà chỉ cần tạo ra câu truy vấn mysql select tìm kiếm các thông tin theo yêu cầu của khách hàng.
        # Lưu ý thêm phần giới hạn top 4 kết quả
        CHỈ TRẢ VỀ CÂU TRUY VẤN MYSQL KHÔNG ĐƯỢC CÓ CÁC KÝ TỰ ĐẶT BIỆT GÌ HẾT.

        Cơ sở dữ liệu `sunny_sport` bao gồm các bảng chính:

        - *shop_info*: Thông tin shop, gồm `shop_id` (ID), `shop_name` (tên shop, text), `description` (mô tả, text), `address` (địa chỉ, text), `phone` (số điện thoại, text), `email` (email, text), `opening_hours` (giờ mở cửa, text).
        - *events*: Sự kiện, gồm `event_id` (ID), `event_name` (tên sự kiện, text), `description` (mô tả, text), `event_date` (ngày sự kiện, date), `location` (địa điểm, text).
        - *courts*: Thông tin sân cầu lông, gồm `court_id` (ID), `court_name` (tên sân, text), `description` (mô tả, text), `price_per_hour` (giá mỗi giờ, decimal).
        """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system", "content": "Bạn là một chuyên gia truy vấn dữ liệu mysql."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.5,
            max_tokens=2000
        )
        return response.choices[0].message.content.strip()
    except Exception as e:
        print(f"Lỗi xử lý tư vấn: {e}")
        return "Xin lỗi, tôi gặp khó khăn khi xử lý yêu cầu. Tôi có thể giúp gì về Sunny Sport?"


def generate_answer(data, query):
    """Tạo câu trả lời dựa trên dữ liệu tìm được"""
    try:
        prompt = f"""Người dùng yêu cầu là: '{query}'.
        Bạn dựa vào yêu cầu và dữ liệu tìm được để tạo ra câu trả lời cho người dùng theo dạng văn bản thông thường
        Nếu thiếu thông tin thì trả về không có thông tin về yêu cầu của người dùng.
        Đây là dữ liệu tìm được: '{data}' 
        """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system",
                 "content": "Bạn là một trợ lý tư vấn trả lời câu hỏi về shop cầu lông Sunny Sport. Bạn tạo ra đoạn text trả lời yêu cầu người dùng. Nếu không có thông tin có thể yêu cầu người dùng hỏi lại để có đủ thông tin"},
                {"role": "user", "content": prompt}
            ],
            temperature=0.5,
            max_tokens=4000
        )
        result = response.choices[0].message.content.strip()
        return result
    except Exception as e:
        print(f"Lỗi tạo câu trả lời: {e}")
        return "Xin lỗi, hệ thống đang gặp sự cố. Vui lòng thử lại sau."


def generate_court_answer(data, query):
    """Tạo câu trả lời dạng text cho sân cầu lông"""
    try:
        # Xử lý trường hợp không có dữ liệu
        if not data or len(data) == 0:
            return "Hiện tại không có sân trống phù hợp. Bạn thử ngày khác nhé! 😊"

        # Nếu chỉ có 1 sân
        if len(data) == 1:
            court = data[0]
            response = f"✅ Có 1 sân trống:\n- {court['court_name']}"
        else:
            response = f"✅ Có {len(data)} sân trống:\n"
            for court in data:
                response += f"- {court['court_name']}\n"

        return response.strip()

    except Exception as e:
        print(f"Lỗi tạo court answer: {e}")
        return "Xin lỗi, không thể tạo thông tin sân. Vui lòng thử lại."


def find_alternative_time_slots(date, start_time, end_time, max_slots=5):
    """Tìm các khung giờ thay thế khi khung giờ yêu cầu không có sân trống"""
    try:
        from datetime import datetime, timedelta

        # Parse thời gian
        start_dt = datetime.strptime(f"{date} {start_time}", "%Y-%m-%d %H:%M:%S")
        end_dt = datetime.strptime(f"{date} {end_time}", "%Y-%m-%d %H:%M:%S")

        # Tính thời gian kết thúc của khung giờ yêu cầu
        requested_end_hour = end_dt.hour

        alternative_slots = []

        # Tìm các khung giờ tiếp theo trong ngày (từ 6h đến 22h)
        for hour in range(requested_end_hour, 22):
            slot_start = f"{hour:02d}:00:00"
            slot_end = f"{hour + 1:02d}:00:00"

            # Kiểm tra sân trống cho khung giờ này
            query = f"""
            SELECT c.court_id, c.court_name, c.description, c.price_per_hour 
            FROM courts c 
            WHERE NOT EXISTS (
                SELECT 1 FROM bookings b 
                WHERE b.court_id = c.court_id 
                AND b.booking_date = %s 
                AND b.status IN ('pending', 'confirmed')
                AND NOT (b.end_time <= %s OR b.start_time >= %s)
            )
            LIMIT 5
            """

            data = execute_query(query, (date, slot_start, slot_end))

            if data:
                court_names = [court['court_name'] for court in data]
                alternative_slots.append({
                    'time': f"{hour}h–{hour + 1}h",
                    'courts': court_names
                })

                # Giới hạn số lượng khung giờ gợi ý
                if len(alternative_slots) >= max_slots:
                    break

        return alternative_slots

    except Exception as e:
        print(f"Lỗi tìm khung giờ thay thế: {e}")
        return []


def extract_time_info_from_message(message):
    """Trích xuất thông tin ngày, giờ bắt đầu và kết thúc từ message của user"""
    try:
        import re
        from datetime import datetime, timedelta

        # Mặc định là hôm nay
        date = datetime.now().strftime('%Y-%m-%d')
        start_time = None
        end_time = None

        # Tìm ngày trong message
        date_patterns = [
            r'ngày\s+(\d{1,2})[/-](\d{1,2})',  # ngày 20/9, ngày 20-9
            r'(\d{1,2})[/-](\d{1,2})',  # 20/9, 20-9
            r'ngày\s+mai',  # ngày mai
            r'hôm\s+nay',  # hôm nay
        ]

        for pattern in date_patterns:
            match = re.search(pattern, message.lower())
            if match:
                if 'mai' in pattern:
                    date = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
                elif 'nay' in pattern:
                    date = datetime.now().strftime('%Y-%m-%d')
                else:
                    day, month = match.groups()
                    current_year = datetime.now().year
                    date = f"{current_year}-{month.zfill(2)}-{day.zfill(2)}"
                break

        # Tìm khung giờ trong message
        time_patterns = [
            r'(\d{1,2})h\s*[-–]\s*(\d{1,2})h',  # 8h-9h, 8h–9h
            r'(\d{1,2}):(\d{2}):(\d{2})\s*đến\s*(\d{1,2}):(\d{2}):(\d{2})',  # 06:30:00 đến 08:00:00
            r'(\d{1,2}):(\d{2})\s*[-–]\s*(\d{1,2}):(\d{2})',  # 8:00-9:00
            r'từ\s+(\d{1,2})h\s+đến\s+(\d{1,2})h',  # từ 8h đến 9h
            r'(\d{1,2})h\s+đến\s+(\d{1,2})h',  # 8h đến 9h
            r'(\d{1,2})h\s+đến\s+(\d{1,2})\s+giờ',  # 12h đến 1 giờ
            r'(\d{1,2})h\s+đến\s+(\d{1,2})',  # 12h đến 1
        ]

        for pattern in time_patterns:
            match = re.search(pattern, message.lower())
            if match:
                groups = match.groups()
                if len(groups) == 2:  # 8h-9h format
                    start_hour = int(groups[0])
                    end_hour = int(groups[1])
                    start_time = f"{start_hour:02d}:00:00"
                    end_time = f"{end_hour:02d}:00:00"
                elif len(groups) == 4:  # 8:00-9:00 format
                    start_hour, start_min = int(groups[0]), int(groups[1])
                    end_hour, end_min = int(groups[2]), int(groups[3])
                    start_time = f"{start_hour:02d}:{start_min:02d}:00"
                    end_time = f"{end_hour:02d}:{end_min:02d}:00"
                elif len(groups) == 6:  # 06:30:00 đến 08:00:00 format
                    start_hour, start_min, start_sec = int(groups[0]), int(groups[1]), int(groups[2])
                    end_hour, end_min, end_sec = int(groups[3]), int(groups[4]), int(groups[5])
                    start_time = f"{start_hour:02d}:{start_min:02d}:{start_sec:02d}"
                    end_time = f"{end_hour:02d}:{end_min:02d}:{end_sec:02d}"
                break

        # Nếu không tìm thấy khung giờ cụ thể, tìm giờ đơn lẻ
        if not start_time:
            single_hour_patterns = [
                r'(\d{1,2})h',  # 8h
                r'(\d{1,2}):(\d{2})',  # 8:00
            ]

            for pattern in single_hour_patterns:
                match = re.search(pattern, message.lower())
                if match:
                    groups = match.groups()
                    if len(groups) == 1:  # 8h format
                        hour = int(groups[0])
                        start_time = f"{hour:02d}:00:00"
                        end_time = f"{hour + 1:02d}:00:00"
                    elif len(groups) == 2:  # 8:00 format
                        hour, minute = int(groups[0]), int(groups[1])
                        start_time = f"{hour:02d}:{minute:02d}:00"
                        end_time = f"{hour + 1:02d}:00:00"
                    break

        return date, start_time, end_time

    except Exception as e:
        print(f"Lỗi trích xuất thông tin thời gian: {e}")
        return None, None, None


def generate_court_answer_with_alternatives(data, query, date=None, start_time=None, end_time=None):
    """Tạo câu trả lời với khung giờ gợi ý nếu không có sân trống"""
    try:
        # Nếu có sân trống, trả kết quả bình thường
        if data and len(data) > 0:
            return generate_court_answer(data, query)

        # Nếu không có sân và có thông tin khung giờ, tìm khung giờ thay thế
        if not data and date and start_time and end_time:
            alternative_slots = find_alternative_time_slots(date, start_time, end_time)

            if alternative_slots:
                response = f"❌ Khung {start_time[:5]}–{end_time[:5]} đã kín.\n"
                response += "👉 Nhưng có sân trống ở các khung giờ sau:\n"

                for slot in alternative_slots:
                    courts_str = ", ".join(slot['courts'])
                    response += f"- {slot['time']}: {courts_str}\n"

                return response.strip()
            else:
                return "😔 Cả ngày không còn sân trống nào rồi. Bạn thử ngày khác nhé!"

        # Fallback cho trường hợp khác
        return "Hiện tại không có sân trống phù hợp. Bạn thử ngày khác nhé! 😊"

    except Exception as e:
        print(f"Lỗi tạo court answer với alternatives: {e}")
        return "Xin lỗi, không thể tạo thông tin sân. Vui lòng thử lại."


def generate_product_card(data, query):
    """Tạo HTML card hiển thị sản phẩm cầu lông"""
    try:
        prompt = f"""Yêu cầu của người dùng là: '{query}'.
        Bạn dựa vào yêu cầu và dữ liệu tôi cung cấp để tạo ra câu trả lời về các sản phẩm cầu lông cần tìm kiếm
        Dữ liệu tìm kiếm được là: '{data}'

        # CẤU TRÚC CÂU TRẢ LỜI
        Phần 1. Nếu tìm thấy sản phẩm, hãy tạo 1 câu dẫn phản hồi các yêu cầu của người dùng hoặc nếu không có thông tin thì trả lời là không có sản phẩm phù hợp.
        Phần 2. Đoạn mã HTML để hiển thị sản phẩm: 
        - Mỗi sản phẩm là 1 <div class="product-card">.
        - Trong mỗi product-card chỉ có ảnh, tên sản phẩm, giá (hoặc giá khuyến mãi nếu có).
        - Khi nhấn vào toàn bộ thẻ product-card thì chuyển hướng đến t.php?product_id=... (dùng thuộc tính onclick cho div với window.location.href, KHÔNG dùng window.open).
        - Sử dụng đường dẫn hình ảnh: images/[image_url] (từ database, không có dấu gạch chéo đầu)
        - Sử dụng CSS inline để styling
        - Nếu không có hình ảnh, sử dụng hình mặc định: images/no-image.jpg

        Ví dụ trả về:
        'Tìm thấy 2 sản phẩm phù hợp: <div class="product-list" style="display:flex;flex-wrap:wrap;gap:15px;margin-top:10px;">
            <div class="product-card" onclick="window.location.href='t.php?product_id=1'" style="width:200px;padding:15px;border:1px solid #ddd;border-radius:10px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s;">
                <img src="images/vot-cau-long-yonex-astrox-99-pro-trang-chinh-hang.webp" class="product-image" style="width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
                <div class="product-name" style="font-size:16px;color:#333;margin-bottom:8px;font-weight:bold;">Vợt Yonex Astrox 99 Pro</div>
                <div class="product-price" style="color:#e74c3c;font-weight:bold;font-size:18px;">2,500,000 VNĐ</div>
            </div>
            <div class="product-card" onclick="window.location.href='t.php?product_id=2'" style="width:200px;padding:15px;border:1px solid #ddd;border-radius:10px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s;">
                <img src="images/lining-attack.jpg" class="product-image" style="width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
                <div class="product-name" style="font-size:16px;color:#333;margin-bottom:8px;font-weight:bold;">Giày Lining Attack 2025</div>
                <div class="product-price" style="color:#e74c3c;font-weight:bold;font-size:18px;">1,500,000 VNĐ</div>
            </div>
        </div>'
        """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system",
                 "content": "Bạn là một trợ lý tạo HTML card hiển thị sản phẩm cầu lông với style đẹp."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.5,
            max_tokens=4000
        )
        result = response.choices[0].message.content.strip()
        return result
    except Exception as e:
        print(f"Lỗi tạo product card: {e}")
        return "Xin lỗi, không thể tạo thông tin sản phẩm. Vui lòng thử lại."


def save_chat_history(user_id, role, message, bot_disabled=None):
    """Lưu lịch sử chat vào database"""
    try:
        conn = ket_noi_db()
        if not conn:
            return False

        cursor = conn.cursor()

        # Nếu không truyền bot_disabled, mặc định là 0 (bot bật)
        if bot_disabled is None:
            bot_disabled = 0  # Mặc định bot bật cho tất cả tin nhắn mới

        query = "INSERT INTO chat_history (user_id, role, message, bot_disabled) VALUES (%s, %s, %s, %s)"
        cursor.execute(query, (user_id, role, message, bot_disabled))
        conn.commit()
        cursor.close()
        conn.close()
        return True
    except Exception as e:
        print(f"Lỗi lưu chat history: {e}")
        return False




# ==================== CONVERSATION STATE MANAGEMENT ====================

# In-memory storage cho conversation state (trong production nên dùng Redis)
conversation_states = {}


def get_conversation_state(user_id):
    """Lấy trạng thái conversation của user"""
    print(f"🔍 GET_STATE: User {user_id}, total states in memory: {len(conversation_states)}")
    print(f"🔍 Available users: {list(conversation_states.keys())}")
    state = conversation_states.get(user_id, {
        'step': None,
        'data': {},
        'last_courts': []
    })
    print(f"🔍 Returned state for {user_id}: {state}")
    return state


def set_conversation_state(user_id, step, data=None, last_courts=None):
    """Cập nhật trạng thái conversation của user"""
    if user_id not in conversation_states:
        conversation_states[user_id] = {}

    conversation_states[user_id]['step'] = step
    if data is not None:
        conversation_states[user_id]['data'] = data
    if last_courts is not None:
        conversation_states[user_id]['last_courts'] = last_courts

    print(f"🔧 SET STATE: User {user_id} → step: {step}, data: {data}")
    print(f"📊 FULL STATE: {conversation_states.get(user_id, {})}")


def clear_conversation_state(user_id):
    """Xóa trạng thái conversation"""
    print(f"🗑️  CLEAR_STATE called for user {user_id}")
    import traceback
    print("🗑️  Call stack:")
    traceback.print_stack()
    if user_id in conversation_states:
        del conversation_states[user_id]


# ==================== COURT BOOKING CONVERSATION FLOW ====================

def handle_court_booking_conversation(user_id, message_text):
    """Xử lý conversation flow cho đặt sân"""
    state = get_conversation_state(user_id)

    # Bước 1: Tìm sân và gợi ý
    if state['step'] is None:
        return find_courts_and_ask_booking(user_id, message_text)

    # Bước 2: Xử lý phản hồi đặt sân
    elif state['step'] == 'waiting_booking_confirmation':
        return handle_booking_confirmation(user_id, message_text)

    # Bước 3: Thu thập ngày đặt sân
    elif state['step'] == 'collecting_date':
        return collect_booking_date(user_id, message_text)

    # Bước 4: Thu thập giờ bắt đầu
    elif state['step'] == 'collecting_start_time':
        return collect_start_time(user_id, message_text)

    # Bước 5: Thu thập giờ kết thúc
    elif state['step'] == 'collecting_end_time':
        return collect_end_time(user_id, message_text)

    # Bước 6: Chọn sân cụ thể
    elif state['step'] == 'collecting_court':
        return collect_court_selection(user_id, message_text)

    # Bước 7: Thu thập thông tin user (nếu cần)
    elif state['step'] == 'collecting_user_name':
        return collect_user_name(user_id, message_text)

    elif state['step'] == 'collecting_user_phone':
        return collect_user_phone(user_id, message_text)

    # Bước 8: Tóm tắt và xác nhận thông tin
    elif state['step'] == 'waiting_info_confirmation':
        return handle_info_confirmation(user_id, message_text)

    # Bước 9: Chọn phương thức thanh toán
    elif state['step'] == 'collecting_payment_method':
        return collect_payment_method(user_id, message_text)

    # Bước 10: Xác nhận cuối cùng và ghi DB
    elif state['step'] == 'waiting_final_confirmation':
        return handle_final_confirmation(user_id, message_text)

    else:
        # Reset conversation nếu có lỗi
        clear_conversation_state(user_id)
        return find_courts_and_ask_booking(user_id, message_text)


def find_courts_and_ask_booking(user_id, message_text):
    """Bước 1: Tìm sân trống và hỏi có muốn đặt không"""
    # Trích xuất thông tin thời gian trước
    date, start_time, end_time = extract_time_info_from_message(message_text)

    # Ưu tiên sử dụng standard query với thông tin đã trích xuất
    from datetime import datetime
    now = datetime.now()

    if date and start_time and end_time:
        # Có đủ thông tin thời gian -> sử dụng standard query
        standard_query, params = create_standard_court_query(date, start_time, end_time)
        print(f"🔍 TÌM SÂN: {standard_query} | PARAMS: {params}")
        courts_data = execute_query(standard_query, params)
    elif date:
        # Chỉ có ngày -> dùng current time check
        if date == now.strftime('%Y-%m-%d'):  # Hôm nay
            current_time = now.strftime('%H:%M:%S')
            standard_query, params = create_standard_court_query(date, current_time=current_time)
        else:  # Ngày khác
            standard_query, params = create_standard_court_query(date)
        print(f"🔍 TÌM SÂN: {standard_query} | PARAMS: {params}")
        courts_data = execute_query(standard_query, params)
    else:
        # Không có thông tin thời gian rõ ràng -> thử LLM
        query = handle_court_booking_query(message_text)
        is_valid, validation_msg = validate_court_booking_sql(query)

        if not is_valid:
            # LLM thất bại -> fallback standard query
            today = now.strftime('%Y-%m-%d')
            current_time = now.strftime('%H:%M:%S')
            fallback_query, params = create_standard_court_query(today, current_time=current_time)
            print(f"🔍 TÌM SÂN: {fallback_query} | PARAMS: {params}")
            courts_data = execute_query(fallback_query, params)
        else:
            print(f"🔍 TÌM SÂN: {query}")
            courts_data = execute_query(query)

    if courts_data and len(courts_data) > 0:
        # Tạo response hiển thị sân trống
        response = "✅ Tìm thấy các sân trống:\n"
        for i, court in enumerate(courts_data, 1):
            response += f"{i}. {court['court_name']} - {court['price_per_hour']:,.0f} VNĐ/giờ\n"

        response += "\n🎯 **Bạn có muốn đặt một trong những sân này ngay bây giờ không?**\n"
        response += "Trả lời 'có' hoặc 'không'."

        # Lưu state và dữ liệu sân với thông tin đã trích xuất
        booking_data = {
            'booking_date': date,
            'start_time': start_time,
            'end_time': end_time,
            'original_message': message_text
        }
        set_conversation_state(user_id, 'waiting_booking_confirmation',
                               data=booking_data, last_courts=courts_data)

        return response
    else:
        # Không có sân trống - tìm khung giờ thay thế
        if date and start_time and end_time:
            alternative_slots = find_alternative_time_slots(date, start_time, end_time)
            if alternative_slots:
                response = f"❌ Khung {start_time[:5]}–{end_time[:5]} đã kín.\n"
                response += "👉 Nhưng có sân trống ở các khung giờ sau:\n"
                for slot in alternative_slots:
                    courts_str = ", ".join(slot['courts'])
                    response += f"- {slot['time']}: {courts_str}\n"
                response += "\n🎯 **Bạn có muốn đặt một trong những khung giờ này không?**\n"
                response += "Trả lời 'có' hoặc 'không'."
                return response
            else:
                return "😔 Cả ngày không còn sân trống nào rồi. Bạn thử ngày khác nhé!"
        else:
            return "Hiện tại không có sân trống phù hợp. Bạn thử ngày khác nhé! 😊"


def handle_booking_confirmation(user_id, message_text):
    """Bước 2: Xử lý xác nhận muốn đặt sân và kiểm tra thông tin đã có"""
    message_lower = message_text.lower().strip()
    print(f"🎯 HANDLE_BOOKING_CONFIRMATION: User {user_id}, message: '{message_text}'")

    # Kiểm tra user có muốn đặt sân không
    if any(keyword in message_lower for keyword in ['có', 'ok', 'được', 'yes', 'đồng ý']):
        state = get_conversation_state(user_id)
        data = state['data']

        # Kiểm tra thông tin nào đã có từ message gốc
        missing_info = []
        next_step = None

        if not data.get('booking_date'):
            missing_info.append('ngày')
            if not next_step:
                next_step = 'collecting_date'

        if not data.get('start_time'):
            missing_info.append('giờ bắt đầu')
            if not next_step:
                next_step = 'collecting_start_time'

        if not data.get('end_time'):
            missing_info.append('giờ kết thúc')
            if not next_step:
                next_step = 'collecting_end_time'

        # Nếu thiếu thông tin, hỏi từng cái
        if missing_info:
            if next_step == 'collecting_date':
                response = "📅 **Bước 1: Chọn ngày đặt sân**\n\n"
                response += "Vui lòng cho biết ngày bạn muốn đặt sân:\n"
                response += "• Ví dụ: 'ngày mai', 'hôm nay', '22/09', '22/09/2025'\n"
                response += "• Hoặc: 'thứ 2 tuần tới', '2 ngày nữa'"
            elif next_step == 'collecting_start_time':
                response = "⏰ **Bước 2: Chọn giờ bắt đầu**\n\n"
                response += "Vui lòng cho biết giờ bắt đầu đặt sân:\n"
                response += "• Ví dụ: '18h', '18:00', '6 giờ tối'"
            elif next_step == 'collecting_end_time':
                response = "⏰ **Bước 3: Chọn giờ kết thúc**\n\n"
                response += "Vui lòng cho biết giờ kết thúc:\n"
                response += "• Ví dụ: '20h', '20:00', '8 giờ tối'"
            else:
                response = "❌ Có lỗi trong quy trình. Vui lòng thử lại."

            set_conversation_state(user_id, next_step, data=data)
            return response
        else:
            # Đã có đủ thông tin thời gian, chuyển tới chọn sân
            courts_data = state['last_courts']
            if len(courts_data) == 1:
                # Chỉ có 1 sân, tự động chọn
                data['selected_court'] = courts_data[0]
                return proceed_to_user_info_or_summary(user_id)
            else:
                # Nhiều sân, yêu cầu chọn
                response = "🏸 **Chọn sân bạn muốn đặt:**\n\n"
                response += "Các sân trống trong khung giờ này:\n"
                for court in courts_data:
                    response += f"• {court['court_name']} - {court['price_per_hour']:,.0f} VNĐ/giờ\n"
                response += "\nVui lòng trả lời tên sân bạn muốn đặt (ví dụ: 'Sân 1')."

                set_conversation_state(user_id, 'collecting_court', data=data)
                return response
    else:
        # User không muốn đặt
        clear_conversation_state(user_id)
        return "Được rồi! Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"


def collect_booking_date(user_id, message_text):
    """Bước 3: Thu thập ngày đặt sân"""
    try:
        import re
        from datetime import datetime, timedelta

        message_lower = message_text.lower().strip()

        # Kiểm tra lệnh hủy
        if message_lower in ['hủy', 'huy', 'cancel', 'dừng', 'stop']:
            clear_conversation_state(user_id)
            return "❌ Đã hủy quy trình đặt sân. Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"

        booking_date = None

        # Parse các format ngày khác nhau
        if 'ngày mai' in message_lower or 'mai' in message_lower:
            booking_date = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
        elif 'hôm nay' in message_lower or 'nay' in message_lower:
            booking_date = datetime.now().strftime('%Y-%m-%d')
        elif 'mốt' in message_lower or '2 ngày' in message_lower:
            booking_date = (datetime.now() + timedelta(days=2)).strftime('%Y-%m-%d')
        else:
            # Tìm pattern ngày/tháng/năm
            date_patterns = [
                r'(\d{1,2})[/-](\d{1,2})[/-](\d{4})',  # 22/09/2025
                r'(\d{1,2})[/-](\d{1,2})',  # 22/09
            ]

            for pattern in date_patterns:
                match = re.search(pattern, message_text)
                if match:
                    if len(match.groups()) == 3:  # dd/mm/yyyy
                        day, month, year = match.groups()
                        booking_date = f"{year}-{month.zfill(2)}-{day.zfill(2)}"
                    else:  # dd/mm
                        day, month = match.groups()
                        current_year = datetime.now().year
                        booking_date = f"{current_year}-{month.zfill(2)}-{day.zfill(2)}"
                    break

        if booking_date:
            # Kiểm tra ngày hợp lệ
            try:
                date_obj = datetime.strptime(booking_date, '%Y-%m-%d')
                if date_obj.date() < datetime.now().date():
                    return "❌ Không thể đặt sân cho ngày trong quá khứ. Vui lòng chọn ngày khác."

                # Lưu ngày và chuyển sang bước tiếp theo
                state = get_conversation_state(user_id)
                state['data']['booking_date'] = booking_date
                set_conversation_state(user_id, 'collecting_start_time', data=state['data'])

                date_display = date_obj.strftime('%d/%m/%Y')
                response = f"✅ Đã chọn ngày: {date_display}\n\n"
                response += "⏰ **Bước 2/5: Chọn giờ bắt đầu**\n\n"
                response += "Vui lòng cho biết giờ bắt đầu đặt sân:\n"
                response += "• Ví dụ: '18h', '18:00', '6 giờ tối'"

                return response

            except ValueError:
                pass

        # Không hiểu format ngày
        response = "❌ Tôi không hiểu ngày bạn muốn đặt. Vui lòng thử lại:\n"
        response += "• 'ngày mai', 'hôm nay'\n"
        response += "• '22/09', '22/09/2025'\n"
        response += "• 'mốt', '2 ngày nữa'"
        return response

    except Exception as e:
        print(f"Lỗi thu thập ngày: {e}")
        return "❌ Có lỗi xử lý ngày. Vui lòng thử lại với format như '22/09' hoặc 'ngày mai'."


def collect_start_time(user_id, message_text):
    """Bước 4: Thu thập giờ bắt đầu"""
    try:
        import re

        message_lower = message_text.lower().strip()

        # Kiểm tra lệnh hủy
        if message_lower in ['hủy', 'huy', 'cancel', 'dừng', 'stop', 'exit']:
            clear_conversation_state(user_id)
            return "❌ Đã hủy quy trình đặt sân. Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"

        time_patterns = [
            r'(\d{1,2})[h:](\d{2})',  # 18:30, 18h30
            r'(\d{1,2})\s*h',  # 18h
            r'(\d{1,2})\s*gi[oờ]',  # 18 giờ
        ]

        start_time = None
        for pattern in time_patterns:
            match = re.search(pattern, message_text)
            if match:
                groups = match.groups()
                if len(groups) == 2:  # Có phút
                    hour, minute = int(groups[0]), int(groups[1])
                else:  # Chỉ có giờ
                    hour, minute = int(groups[0]), 0

                # Validate giờ
                if 6 <= hour <= 22 and 0 <= minute <= 59:
                    start_time = f"{hour:02d}:{minute:02d}:00"
                    break

        if start_time:
            # Lưu giờ bắt đầu và chuyển sang bước tiếp theo
            state = get_conversation_state(user_id)
            state['data']['start_time'] = start_time
            set_conversation_state(user_id, 'collecting_end_time', data=state['data'])

            response = f"✅ Đã chọn giờ bắt đầu: {start_time[:5]}\n\n"
            response += "⏰ **Bước 3/5: Chọn giờ kết thúc**\n\n"
            response += "Vui lòng cho biết giờ kết thúc:\n"
            response += "• Ví dụ: '20h', '20:00', '8 giờ tối'"

            return response
        else:
            response = "❌ Tôi không hiểu giờ bắt đầu. Vui lòng thử lại:\n"
            response += "• '18h', '18:00'\n"
            response += "• '6 giờ tối'\n"
            response += "• Giờ mở cửa: 6h-22h"
            return response

    except Exception as e:
        print(f"Lỗi thu thập giờ bắt đầu: {e}")
        return "❌ Có lỗi xử lý giờ. Vui lòng thử lại với format như '18h' hoặc '18:00'."


def collect_end_time(user_id, message_text):
    """Bước 5: Thu thập giờ kết thúc"""
    try:
        import re
        from datetime import datetime, timedelta

        message_lower = message_text.lower().strip()

        # Kiểm tra lệnh hủy
        if message_lower in ['hủy', 'huy', 'cancel', 'dừng', 'stop', 'exit']:
            clear_conversation_state(user_id)
            return "❌ Đã hủy quy trình đặt sân. Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"

        time_patterns = [
            r'(\d{1,2})[h:](\d{2})',  # 20:30, 20h30
            r'(\d{1,2})\s*h',  # 20h
            r'(\d{1,2})\s*gi[oờ]',  # 20 giờ
        ]

        end_time = None
        for pattern in time_patterns:
            match = re.search(pattern, message_text)
            if match:
                groups = match.groups()
                if len(groups) == 2:  # Có phút
                    hour, minute = int(groups[0]), int(groups[1])
                else:  # Chỉ có giờ
                    hour, minute = int(groups[0]), 0

                # Validate giờ
                if 6 <= hour <= 22 and 0 <= minute <= 59:
                    end_time = f"{hour:02d}:{minute:02d}:00"
                    break

        if end_time:
            state = get_conversation_state(user_id)
            start_time = state['data']['start_time']

            # Kiểm tra giờ kết thúc phải sau giờ bắt đầu
            start_dt = datetime.strptime(start_time, '%H:%M:%S')
            end_dt = datetime.strptime(end_time, '%H:%M:%S')

            if end_dt <= start_dt:
                return f"❌ Giờ kết thúc ({end_time[:5]}) phải sau giờ bắt đầu ({start_time[:5]}). Vui lòng chọn lại."

            # Lưu giờ kết thúc và chuyển sang bước chọn sân
            state['data']['end_time'] = end_time
            set_conversation_state(user_id, 'collecting_court', data=state['data'])

            # Hiển thị danh sách sân
            courts_data = state['last_courts']
            response = f"✅ Đã chọn giờ kết thúc: {end_time[:5]}\n\n"
            response += "🏸 **Bước 4/5: Chọn sân**\n\n"
            response += "Các sân trống trong khung giờ này:\n"
            for court in courts_data:
                response += f"• {court['court_name']} - {court['price_per_hour']:,.0f} VNĐ/giờ\n"
            response += "\nVui lòng trả lời tên sân bạn muốn đặt (ví dụ: 'Sân 1')."

            return response
        else:
            response = "❌ Tôi không hiểu giờ kết thúc. Vui lòng thử lại:\n"
            response += "• '20h', '20:00'\n"
            response += "• '8 giờ tối'\n"
            response += "• Giờ mở cửa: 6h-22h"
            return response

    except Exception as e:
        print(f"Lỗi thu thập giờ kết thúc: {e}")
        return "❌ Có lỗi xử lý giờ. Vui lòng thử lại với format như '20h' hoặc '20:00'."


def collect_court_selection(user_id, message_text):
    """Bước 6: Thu thập lựa chọn sân theo tên"""
    try:
        message_lower = message_text.lower().strip()

        # Kiểm tra lệnh hủy
        if message_lower in ['hủy', 'huy', 'cancel', 'dừng', 'stop', 'exit']:
            clear_conversation_state(user_id)
            return "❌ Đã hủy quy trình đặt sân. Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"

        state = get_conversation_state(user_id)
        courts_data = state['last_courts']
        selected_court = None

        # Kiểm tra khung giờ đã chọn để xác nhận sân còn trống
        booking_date = state['data']['booking_date']
        start_time = state['data']['start_time']
        end_time = state['data']['end_time']

        # Tìm sân theo tên trong message
        message_lower = message_text.lower().strip()
        for court in courts_data:
            court_name_lower = court['court_name'].lower()
            if court_name_lower in message_lower or message_lower in court_name_lower:
                # Kiểm tra lại sân có trống trong khung giờ này không
                check_query = """
                SELECT 1 FROM bookings 
                WHERE court_id = %s 
                AND booking_date = %s 
                AND status IN ('pending', 'confirmed')
                AND NOT (end_time <= %s OR start_time >= %s)
                """
                check_result = execute_query(check_query, (court['court_id'], booking_date, start_time, end_time))

                if not check_result:  # Sân vẫn trống
                    selected_court = court
                    break
                else:
                    return f"❌ {court['court_name']} đã có người đặt trong khung giờ này. Vui lòng chọn sân khác."

        if selected_court:
            state['data']['selected_court'] = selected_court
            return proceed_to_user_info_or_summary(user_id)

        # Không tìm thấy sân
        response = "❌ Tôi không tìm thấy sân bạn chọn. Các sân trống hiện tại:\n"
        for court in courts_data:
            response += f"• {court['court_name']}\n"
        response += "\nVui lòng chọn tên sân chính xác."
        return response

    except Exception as e:
        print(f"Lỗi chọn sân: {e}")
        return "❌ Có lỗi xử lý. Vui lòng chọn lại tên sân."


def proceed_to_user_info_or_summary(user_id):
    """Kiểm tra cần thu thập thông tin user hay chuyển tới tóm tắt"""
    state = get_conversation_state(user_id)

    # Kiểm tra xem cần thu thập thông tin user không
    # Luôn luôn thu thập thông tin cho guest user, và cả user không có tên/sđt
    if (user_id == "guest" or not user_id or user_id.isdigit() is False or
            'user_name' not in state['data'] or 'user_phone' not in state['data']):
        # Cần thu thập thông tin user
        selected_court = state['data']['selected_court']
        set_conversation_state(user_id, 'collecting_user_name', data=state['data'])

        response = f"✅ Đã chọn {selected_court['court_name']}\n\n"
        response += "👤 **Thông tin liên hệ**\n\n"
        response += "Vui lòng cho biết họ tên của bạn:"

        return response
    else:
        # User đã có thông tin, chuyển tới tóm tắt
        return proceed_to_summary(user_id)


def collect_user_name(user_id, message_text):
    """Thu thập tên user"""
    message_lower = message_text.lower().strip()

    # Kiểm tra lệnh hủy
    if message_lower in ['hủy', 'huy', 'cancel', 'dừng', 'stop', 'exit']:
        clear_conversation_state(user_id)
        return "❌ Đã hủy quy trình đặt sân. Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"

    name = message_text.strip()
    if len(name) >= 2:
        state = get_conversation_state(user_id)
        state['data']['user_name'] = name
        set_conversation_state(user_id, 'collecting_user_phone', data=state['data'])

        response = f"✅ Đã lưu tên: {name}\n\n"
        response += "📱 **Bước 5b/5: Số điện thoại**\n\n"
        response += "Vui lòng cho biết số điện thoại liên hệ:"

        return response
    else:
        return "❌ Vui lòng nhập tên đầy đủ (ít nhất 2 ký tự)."


def collect_user_phone(user_id, message_text):
    """Thu thập số điện thoại user"""
    import re

    message_lower = message_text.lower().strip()

    # Kiểm tra lệnh hủy
    if message_lower in ['hủy', 'huy', 'cancel', 'dừng', 'stop', 'exit']:
        clear_conversation_state(user_id)
        return "❌ Đã hủy quy trình đặt sân. Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"

    # Loại bỏ ký tự không phải số
    phone = re.sub(r'[^\d]', '', message_text.strip())

    if len(phone) >= 10:
        state = get_conversation_state(user_id)
        state['data']['user_phone'] = phone
        set_conversation_state(user_id, 'waiting_info_confirmation', data=state['data'])

        return proceed_to_summary(user_id)
    else:
        return "❌ Vui lòng nhập số điện thoại hợp lệ (ít nhất 10 số).\n\n💡 *Trả lời 'hủy' nếu không muốn đặt sân nữa*"


def proceed_to_summary(user_id):
    """Tiến tới bước tóm tắt thông tin"""
    try:
        state = get_conversation_state(user_id)
        data = state['data']

        # Tính toán giá tiền

        start_dt = datetime.strptime(data['start_time'], '%H:%M:%S')
        end_dt = datetime.strptime(data['end_time'], '%H:%M:%S')
        hours = (end_dt - start_dt).total_seconds() / 3600
        base_price = float(data['selected_court']['price_per_hour']) * hours

        # Format ngày hiển thị
        date_obj = datetime.strptime(data['booking_date'], '%Y-%m-%d')
        date_display = date_obj.strftime('%d/%m/%Y')

        # Tạo tóm tắt
        response = "📋 **Tóm tắt thông tin đặt sân:**\n\n"
        response += f"🏸 Sân: {data['selected_court']['court_name']}\n"
        response += f"📅 Ngày: {date_display}\n"
        response += f"⏰ Thời gian: {data['start_time'][:5]} - {data['end_time'][:5]} ({hours} giờ)\n"
        response += f"💰 Giá gốc: {base_price:,.0f} VNĐ\n"

        if 'user_name' in data:
            response += f"👤 Tên: {data['user_name']}\n"
            response += f"📱 SĐT: {data['user_phone']}\n"

        response += "\n✅ **Thông tin đúng chưa?**\n"
        response += "Trả lời 'đúng' hoặc 'chưa' để tiếp tục chọn phương thức thanh toán."

        # Lưu giá gốc để tính toán sau
        data['base_price'] = base_price
        data['hours'] = hours
        set_conversation_state(user_id, 'waiting_info_confirmation', data=data)

        return response

    except Exception as e:
        print(f"Lỗi tạo tóm tắt: {e}")
        clear_conversation_state(user_id)
        return "❌ Có lỗi tạo tóm tắt. Vui lòng thử lại từ đầu."


def handle_info_confirmation(user_id, message_text):
    """Xử lý xác nhận thông tin và chuyển sang chọn phương thức thanh toán"""
    message_lower = message_text.lower().strip()

    if any(keyword in message_lower for keyword in ['đúng', 'ok', 'được', 'có', 'yes', 'chính xác']):
        # Chuyển sang bước chọn phương thức thanh toán
        response = "💳 **Chọn phương thức thanh toán:**\n\n"
        response += "1️⃣ **Thanh toán khi đến sân (ondelivery)**\n"
        response += "   • Thanh toán sau khi chơi xong\n"
        response += "   • Giá gốc không đổi\n\n"
        response += "2️⃣ **Chuyển khoản trước (prepaid) - GIẢM 10%**\n"
        response += "   • Chuyển khoản ngay bây giờ\n"
        response += "   • Được giảm 10% tổng tiền\n\n"
        response += "Vui lòng trả lời:\n"
        response += "• 'ondelivery' để thanh toán sau\n"
        response += "• 'prepaid' để chuyển khoản trước"

        set_conversation_state(user_id, 'collecting_payment_method')
        return response
    else:
        # User muốn sửa thông tin
        clear_conversation_state(user_id)
        return "❌ Đã hủy đặt sân. Nếu bạn muốn đặt lại, vui lòng bắt đầu từ đầu."


def collect_payment_method(user_id, message_text):
    """Thu thập phương thức thanh toán"""
    try:
        message_lower = message_text.lower().strip()

        # Kiểm tra lệnh hủy
        if message_lower in ['hủy', 'huy', 'cancel', 'dừng', 'stop', 'exit']:
            clear_conversation_state(user_id)
            return "❌ Đã hủy quy trình đặt sân. Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"

        state = get_conversation_state(user_id)
        data = state['data']

        if message_lower in ['ondelivery', 'sau', 'thanh toán sau']:
            # Thanh toán khi đến sân
            data['payment_method'] = 'ondelivery'
            data['discount'] = 0
            data['final_price'] = float(data['base_price'])
            data['status'] = 'confirmed'

            response = "✅ **Phương thức: Thanh toán khi đến sân**\n\n"
            response += f"💰 Tổng tiền: {data['final_price']:,.0f} VNĐ\n"
            response += f"🏸 Sân: {data['selected_court']['court_name']}\n"
            response += f"📅 Ngày: {data['booking_date']}\n"
            response += f"⏰ Thời gian: {data['start_time'][:5]} - {data['end_time'][:5]}\n\n"
            response += "🎯 **Xác nhận đặt sân này không?**\n"
            response += "Trả lời 'xác nhận' để hoàn tất đặt sân."

        elif message_lower in ['prepaid', 'trước', 'chuyển khoản']:
            # Chuyển khoản trước - giảm 10%
            data['payment_method'] = 'prepaid'
            data['discount'] = 10
            data['final_price'] = float(data['base_price']) * 0.9
            data['status'] = 'pending'

            response = "✅ **Phương thức: Chuyển khoản trước (GIẢM 10%)**\n\n"
            response += f"💰 Giá gốc: {data['base_price']:,.0f} VNĐ\n"
            response += f"🎁 Giảm giá: {float(data['base_price']) * 0.1:,.0f} VNĐ (10%)\n"
            response += f"💸 Tổng thanh toán: {data['final_price']:,.0f} VNĐ\n\n"

            response += "🏦 **Thông tin chuyển khoản:**\n"
            response += "• Số tài khoản: **0123456789**\n"
            response += "• Ngân hàng: **Vietcombank – Chi nhánh Hà Nội**\n"
            response += "• Chủ tài khoản: **SUNNY SPORT (Trần Phương Thùy)**\n\n"

            user_name = data.get('user_name', 'Guest')
            response += f"📝 **Nội dung chuyển khoản:**\n"
            response += f"`{user_name} - {data['booking_date']} - {data['start_time'][:5]} - {data['end_time'][:5]}`\n\n"

            response += "🎯 **Xác nhận đặt sân này không?**\n"
            response += "Trả lời 'xác nhận' để hoàn tất đặt sân.\n"
            response += "⚠️ Lưu ý: Sau khi xác nhận, vui lòng chuyển khoản và báo admin để xác nhận."

        else:
            return ("❌ Vui lòng chọn phương thức thanh toán:\n"
                    "• 'ondelivery' để thanh toán sau\n"
                    "• 'prepaid' để chuyển khoản trước")

        set_conversation_state(user_id, 'waiting_final_confirmation', data=data)
        return response

    except Exception as e:
        print(f"Lỗi xử lý payment method: {e}")
        return "❌ Có lỗi xử lý phương thức thanh toán. Vui lòng thử lại."


def handle_final_confirmation(user_id, message_text):
    """Bước 4: Xử lý xác nhận cuối cùng và ghi DB"""
    message_lower = message_text.lower().strip()

    if any(keyword in message_lower for keyword in ['xác nhận', 'đồng ý', 'có', 'ok', 'được', 'yes']):
        return create_booking_in_database(user_id)
    else:
        clear_conversation_state(user_id)
        return "Đặt sân đã bị hủy. Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"


def create_booking_in_database(user_id):
    """Tạo booking trong database với thông tin đầy đủ"""
    try:
        state = get_conversation_state(user_id)
        data = state['data']

        # Chuẩn bị thông tin user
        user_name = data.get('user_name', '')
        user_phone = data.get('user_phone', '')

        # Chuẩn bị SQL INSERT với thông tin đầy đủ (không chỉ định booking_id để dùng AUTO_INCREMENT)
        insert_query = """
        INSERT INTO bookings 
        (user_id, court_id, booking_date, start_time, end_time, 
         total_price, status, payment_method, discount, fullname, phone, created_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())
        """

        params = (
            user_id,
            data['selected_court']['court_id'],
            data['booking_date'],
            data['start_time'],
            data['end_time'],
            data['final_price'],
            data['status'],
            data['payment_method'],
            data['discount'],
            user_name,
            user_phone
        )

        # Thực thi INSERT
        conn = ket_noi_db()
        if not conn:
            raise Exception("Không thể kết nối database")

        cursor = conn.cursor()
        cursor.execute(insert_query, params)

        # Lấy booking_id vừa được tạo từ AUTO_INCREMENT
        booking_id = cursor.lastrowid

        conn.commit()
        cursor.close()
        conn.close()

        # Kiểm tra và điều khiển thiết bị IoT nếu booking hôm nay
        booking_date = datetime.strptime(data['booking_date'], '%Y-%m-%d').date()
        today = datetime.now().date()
        
        if booking_date == today:
            # Nếu booking hôm nay, kiểm tra xem có cần bật thiết bị ngay không
            start_dt = datetime.strptime(data['start_time'], '%H:%M:%S').time()
            end_dt = datetime.strptime(data['end_time'], '%H:%M:%S').time()
            now_time = datetime.now().time()
            
            # Bật thiết bị nếu đang trong khung giờ hoặc sắp tới (trong 15 phút)
            start_datetime = datetime.combine(booking_date, start_dt)
            if start_datetime - timedelta(minutes=15) <= datetime.now() <= datetime.combine(booking_date, end_dt):
                send_mqtt_command(data['selected_court']['court_id'], "on")
                print(f"[IoT] Tự động bật thiết bị cho sân {data['selected_court']['court_id']}")

        # Tạo response thành công
        response = "🎉 **ĐẶT SÂN THÀNH CÔNG!**\n\n"
        response += f"📝 Mã đặt sân: #{booking_id}\n"
        response += f"🏸 Sân: {data['selected_court']['court_name']}\n"
        response += f"📅 Ngày: {data['booking_date']}\n"
        response += f"⏰ Thời gian: {data['start_time'][:5]} - {data['end_time'][:5]}\n"
        response += f"💰 Tổng tiền: {data['final_price']:,.0f} VNĐ\n"

        if data['payment_method'] == 'prepaid':
            response += f"🎁 Đã giảm: {data['discount']}%\n"
            response += f"💳 Phương thức: Chuyển khoản trước\n"
            response += f"📋 Trạng thái: Chờ xác nhận thanh toán\n\n"
            response += "⚠️ **Quan trọng:** Vui lòng chuyển khoản theo thông tin đã cung cấp và báo admin để xác nhận!"
        else:
            response += f"💳 Phương thức: Thanh toán khi đến sân\n"
            response += f"📋 Trạng thái: Đã xác nhận\n\n"
            response += "✅ Bạn có thể đến sân theo giờ đã đặt. Vui lòng thanh toán tại quầy sau khi chơi xong!"

        # Thêm thông tin IoT
        if booking_date == today:
            response += "\n🔌 **Thiết bị sân:** Đèn và quạt sẽ tự động bật/tắt theo lịch đặt sân"
        
        response += "\n🙏 Cảm ơn bạn đã sử dụng dịch vụ Sunny Sport!"

        # Xóa conversation state
        clear_conversation_state(user_id)

        return response

    except Exception as e:
        print(f"Lỗi tạo booking: {e}")
        clear_conversation_state(user_id)
        return "❌ Có lỗi xảy ra khi đặt sân. Vui lòng thử lại sau hoặc liên hệ admin để được hỗ trợ."


@app.route('/api/chat', methods=['POST', 'OPTIONS'])
def chat():
    # Xử lý CORS preflight request
    if request.method == 'OPTIONS':
        return '', 200
    try:
        data = request.json
        message_text = data.get("message", "").strip()
        user_id = data.get("user_id", "guest")

        if not message_text:
            return jsonify({"status": "error", "message": "Vui lòng nhập tin nhắn"}), 400

        # Bot luôn hoạt động - đã xóa chức năng toggle bot

        # Lưu tin nhắn của user
        save_chat_history(user_id, "user", message_text)

        # Kiểm tra nếu user đang trong quá trình đặt sân
        current_state = get_conversation_state(user_id)
        print(f"🔍 DEBUG: User {user_id} current state: {current_state}")

        # WORKAROUND: Nếu state bị mất nhưng message là confirmation keywords
        if (current_state['step'] is None and
                message_text.lower().strip() in ['xác nhận', 'đồng ý', 'ok', 'có', 'được']):
            print("⚠️  DETECTED: Final confirmation but state is lost")
            return jsonify(
                {"status": "success", "response": "❌ Phiên đặt sân đã hết hạn. Vui lòng bắt đầu đặt sân từ đầu."}), 200

        if current_state['step'] is not None:
            # Kiểm tra lệnh hủy
            if message_text.lower().strip() in ['hủy', 'huy', 'cancel', 'dừng','stop', 'exit']:
                clear_conversation_state(user_id)
                response = "❌ Đã hủy quy trình đặt sân. Nếu bạn cần hỗ trợ gì khác, hãy cho tôi biết nhé! 😊"
                save_chat_history(user_id, "bot", response)
                return jsonify({"status": "success", "response": response}), 200

            # User đang trong conversation flow đặt sân
            response = handle_court_booking_conversation(user_id, message_text)
            print("Continuing court booking conversation:", response)
            save_chat_history(user_id, "bot", response)
            return jsonify({"status": "success", "response": response}), 200

        # Phân loại yêu cầu chỉ khi user không trong conversation flow
        classification = classify_user_request(message_text)
        print("Classification:", classification)

        if classification["request_type"] == "court_booking":
            # Sử dụng conversation flow mới
            response = handle_court_booking_conversation(user_id, message_text)
            print("Court booking conversation response:", response)

            # Lưu phản hồi của bot
            save_chat_history(user_id, "bot", response)
            return jsonify({"status": "success", "response": response}), 200

        elif classification["request_type"] == "product_search":
            query = handle_product_search_query(message_text)
            print("Product query:", query)
            data = execute_query(query)
            print("Product data:", data)
            response = generate_product_card(data, message_text)
            print("Product response:", response)
            # Lưu phản hồi của bot
            save_chat_history(user_id, "bot", response)
            return jsonify({"status": "success", "response": response}), 200

        elif classification["request_type"] == "consultation":
            query = handle_consultation_query(message_text)
            print("Consultation query:", query)
            data = execute_query(query)
            print("Consultation data:", data)
            response = generate_answer(data, message_text)
            print("Consultation response:", response)
            # Lưu phản hồi của bot
            save_chat_history(user_id, "bot", response)
            return jsonify({"status": "success", "response": response}), 200

        else:  # need_more_info
            response_text = classification.get("additional_info_needed",
                                               "Xin lỗi, tôi chưa hiểu yêu cầu của bạn. Bạn có thể nói rõ đặt sân hay tìm sản phẩm?")
            # Lưu phản hồi của bot
            save_chat_history(user_id, "bot", response_text)
            return jsonify({"status": "success", "response": response_text}), 200

    except Exception as e:
        print(f"Lỗi trong chat API: {e}")
        return jsonify({"status": "error", "message": "Đã xảy ra lỗi. Vui lòng thử lại."}), 500


@app.route('/api/chat/history/<user_id>', methods=['GET', 'OPTIONS'])
def get_chat_history(user_id):
    # Xử lý CORS preflight request
    if request.method == 'OPTIONS':
        return '', 200
    """Lấy lịch sử chat của user"""
    try:
        query = """
        SELECT role, message, created_at 
        FROM chat_history 
        WHERE user_id = %s AND role != 'system'
        ORDER BY created_at ASC 
        LIMIT 100
        """
        data = execute_query(query, (user_id,))
        # Reverse để tin nhắn cũ nhất lên đầu, mới nhất ở cuối
        return jsonify({"status": "success", "history": data}), 200
    except Exception as e:
        print(f"Lỗi lấy chat history: {e}")
        return jsonify({"status": "error", "message": "Không thể lấy lịch sử chat"}), 500


# ==================== ADMIN MONITORING ENDPOINTS ====================

@app.route('/api/admin/conversations', methods=['GET', 'OPTIONS'])
def get_admin_conversations():
    """Lấy danh sách tất cả cuộc trò chuyện cho admin"""
    if request.method == 'OPTIONS':
        return '', 200

    try:
        query = """
        SELECT 
            ch.user_id,
            COALESCE(u.username, CONCAT('User ', ch.user_id)) as user_name,
            MAX(ch.created_at) as last_time,
            COUNT(*) as total_messages,
            SUM(CASE WHEN ch.role = 'user' THEN 1 ELSE 0 END) as user_messages,
            SUM(CASE WHEN ch.role = 'bot' THEN 1 ELSE 0 END) as bot_messages,
            SUM(CASE WHEN ch.role = 'admin' THEN 1 ELSE 0 END) as admin_messages
        FROM chat_history ch
        LEFT JOIN users u ON ch.user_id = u.user_id
        GROUP BY ch.user_id, u.username
        ORDER BY last_time DESC 
        LIMIT 50
        """
        conversations = execute_query(query)

        # Lấy tin nhắn cuối cùng cho mỗi cuộc trò chuyện
        for conv in conversations:
            last_msg_query = """
            SELECT message, role, created_at 
            FROM chat_history 
            WHERE user_id = %s 
            ORDER BY created_at DESC 
            LIMIT 1
            """
            last_msg = execute_query(last_msg_query, (conv['user_id'],))
            if last_msg:
                conv['last_message'] = last_msg[0]['message']
                conv['last_message_role'] = last_msg[0]['role']
            else:
                conv['last_message'] = ''
                conv['last_message_role'] = ''

            # user_name đã được lấy từ query

            # Đếm tin nhắn mới (trong 5 phút qua)
            new_msg_query = """
            SELECT COUNT(*) as new_count
            FROM chat_history 
            WHERE user_id = %s 
            AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            """
            new_count = execute_query(new_msg_query, (conv['user_id'],))
            conv['new_message_count'] = new_count[0]['new_count'] if new_count else 0
            conv['has_new_message'] = conv['new_message_count'] > 0

        return jsonify({"status": "success", "conversations": conversations}), 200
    except Exception as e:
        print(f"Lỗi lấy conversations: {e}")
        return jsonify({"status": "error", "message": "Không thể lấy danh sách cuộc trò chuyện"}), 500


@app.route('/api/admin/send_message', methods=['POST', 'OPTIONS'])
def admin_send_message():
    """Admin gửi tin nhắn cho user"""
    if request.method == 'OPTIONS':
        return '', 200

    try:
        data = request.json
        user_id = data.get("user_id")
        message = data.get("message", "").strip()

        if not user_id or not message:
            return jsonify({"status": "error", "message": "Thiếu user_id hoặc message"}), 400

        # Lưu tin nhắn admin
        save_chat_history(user_id, "admin", message)

        return jsonify({"status": "success", "message": "Đã gửi tin nhắn admin"}), 200
    except Exception as e:
        print(f"Lỗi gửi tin nhắn admin: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500


@app.route('/api/admin/user_info/<user_id>', methods=['GET', 'OPTIONS'])
def get_admin_user_info(user_id):
    """Lấy thông tin chi tiết của user cho admin"""
    if request.method == 'OPTIONS':
        return '', 200

    try:
        # Lấy thông tin cơ bản từ chat_history
        query = """
        SELECT 
            user_id,
            MIN(created_at) as first_message,
            MAX(created_at) as last_message,
            COUNT(*) as total_messages,
            SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as user_messages,
            SUM(CASE WHEN role = 'bot' THEN 1 ELSE 0 END) as bot_messages,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_messages
        FROM chat_history 
        WHERE user_id = %s
        """
        user_info = execute_query(query, (user_id,))

        if user_info:
            return jsonify({"status": "success", "user_info": user_info[0]}), 200
        else:
            return jsonify({"status": "error", "message": "Không tìm thấy user"}), 404
    except Exception as e:
        print(f"Lỗi lấy user info: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500


# ==================== IOT DEVICE MANAGEMENT ENDPOINTS ====================

@app.route('/api/iot/status', methods=['GET', 'OPTIONS'])
def get_iot_status():
    """Lấy trạng thái kết nối IoT và thiết bị"""
    if request.method == 'OPTIONS':
        return '', 200
    
    try:
        global mqtt_client, mqtt_connected
        
        # Lấy thông tin booking hôm nay
        today = datetime.now().strftime('%Y-%m-%d')
        query = """
        SELECT 
            b.booking_id,
            b.court_id,
            c.court_name,
            b.start_time,
            b.end_time,
            b.status,
            CASE 
                WHEN NOW() BETWEEN 
                    CONCAT(b.booking_date, ' ', b.start_time) - INTERVAL 15 MINUTE 
                    AND CONCAT(b.booking_date, ' ', b.end_time) 
                THEN 'active'
                WHEN NOW() > CONCAT(b.booking_date, ' ', b.end_time) 
                THEN 'ended'
                ELSE 'pending'
            END as device_status
        FROM bookings b
        JOIN courts c ON b.court_id = c.court_id
        WHERE b.booking_date = %s 
        AND b.status IN ('pending', 'confirmed')
        ORDER BY b.start_time
        """
        
        bookings = execute_query(query, (today,))
        
        return jsonify({
            "status": "success",
            "mqtt_connected": mqtt_connected,
            "today_bookings": bookings,
            "timestamp": datetime.now().isoformat()
        }), 200
        
    except Exception as e:
        print(f"Lỗi lấy trạng thái IoT: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500


@app.route('/api/iot/control', methods=['POST', 'OPTIONS'])
def control_iot_device():
    """Điều khiển thiết bị IoT thủ công"""
    if request.method == 'OPTIONS':
        return '', 200
    
    try:
        data = request.json
        court_id = data.get('court_id')
        action = data.get('action')  # 'on' hoặc 'off'
        
        if not court_id or not action:
            return jsonify({"status": "error", "message": "Thiếu court_id hoặc action"}), 400
            
        if action not in ['on', 'off']:
            return jsonify({"status": "error", "message": "Action phải là 'on' hoặc 'off'"}), 400
        
        # Gửi lệnh MQTT
        success = send_mqtt_command(court_id, action)
        
        if success:
            return jsonify({
                "status": "success", 
                "message": f"Đã {action} thiết bị sân {court_id}",
                "court_id": court_id,
                "action": action,
                "timestamp": datetime.now().isoformat()
            }), 200
        else:
            return jsonify({"status": "error", "message": "Không thể gửi lệnh điều khiển"}), 500
            
    except Exception as e:
        print(f"Lỗi điều khiển thiết bị: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500


@app.route('/api/iot/check', methods=['POST', 'OPTIONS'])
def check_iot_devices():
    """Kiểm tra và cập nhật trạng thái thiết bị theo lịch booking"""
    if request.method == 'OPTIONS':
        return '', 200
    
    try:
        # Chạy kiểm tra thiết bị
        check_and_control_court_devices()
        
        return jsonify({
            "status": "success",
            "message": "Đã kiểm tra và cập nhật trạng thái thiết bị",
            "timestamp": datetime.now().isoformat()
        }), 200
        
    except Exception as e:
        print(f"Lỗi kiểm tra thiết bị: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500




if __name__ == '__main__':
    print("🚀 Đang khởi động server chatbot cầu lông...")
    print("📍 Server: http://localhost:5000")
    print("🔗 API: http://localhost:5000/api/chat")
    print("🔌 IoT: Tự động điều khiển thiết bị sân")
    print("⏹️  Nhấn Ctrl+C để dừng")
    print("-" * 50)
    
    try:
        # Khởi động kết nối MQTT
        print("🔌 Đang kết nối MQTT...")
        connect_mqtt()
        
        # Khởi động monitoring IoT
        print("🔌 Đang khởi động monitoring thiết bị IoT...")
        start_iot_monitoring()
        
        # Khởi động Flask server
        app.run(host="0.0.0.0", port=5000, debug=True)
    except Exception as e:
        print(f"❌ Lỗi khởi động server: {e}")
        print("💡 Thử chạy: python chatbot_badminton.py")