from flask import Flask, request, jsonify, session
from openai import OpenAI
import mysql.connector
import json
from flask_cors import CORS
import traceback
from datetime import datetime
import re
import uuid

app = Flask(__name__)
CORS(app)

# Khởi tạo client OpenAI
# client = OpenAI(api_key=os.getenv("OPENAI_API_KEY"))

# Định nghĩa các công cụ (tools) cho function calling
tools = [
    {
        "type": "function",
        "function": {
            "name": "classify_user_request",
            "description": "Phân loại yêu cầu của người dùng là tư vấn chung, tìm kiếm sản phẩm, hoặc yêu cầu thêm thông tin nếu không rõ ràng.",
            "parameters": {
                "type": "object",
                "properties": {
                    "request_type": {
                        "type": "string",
                        "enum": ["consultation", "product_search"],
                        "description": "Loại yêu cầu: tư vấn chung, tìm kiếm sản phẩm, hoặc cần thêm thông tin."
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


# Hàm phân loại yêu cầu người dùng
def classify_user_request(query):
    try:
        # Nhận diện các từ khóa phàn nàn, đổi trả, phản hồi
        lower_query = query.lower()
        keywords = [
            "phàn nàn", "khiếu nại", "phản hồi", "đổi trả", "trả hàng", "bảo hành", "lỗi sản phẩm",
            "không hài lòng", "sai hàng", "giao nhầm", "hỏng", "bị lỗi", "không đúng", "không nhận được"
        ]
        if any(kw in lower_query for kw in keywords):
            return {
                "request_type": "need_admin",
                "message": query,
                "additional_info_needed": "Yêu cầu của bạn đã được chuyển tới admin. Vui lòng đợi phản hồi từ admin."
            }

        # ...phần còn lại giữ nguyên...
        prompt = f"""Câu yêu cầu của người dùng là: '{query}'
        Bạn là bot hỗ trợ tư vấn mua sắm quần áo và túi. 
        - Nếu người dùng muốn hỏi thông tin chung (ví dụ: giờ mở cửa, chính sách đổi trả, shop ở đâu, tư vấn mix đồ), thì trả về request_type='consultation'.
        - Nếu người dùng muốn tìm sản phẩm, kể cả khi họ mô tả ngữ cảnh (ví dụ: "có loại váy nào đi biển không", "áo nào hợp đi làm", "giày dưới 500k"), thì trả về request_type='product_search'.

        Trả về JSON đúng định dạng của công cụ classify_user_request.
        """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[{"role": "system", "content": "Bạn là một trợ lý phân loại yêu cầu người dùng."},
                      {"role": "user", "content": prompt}],
            tools=tools,
            temperature=0.5
        )

        tool_call = response.choices[0].message.tool_calls[0]
        arguments = json.loads(tool_call.function.arguments)
        return arguments
    except Exception as e:
        print(f"Lỗi phân loại yêu cầu: {e}")
        return {
            "request_type": "need_more_info",
            "message": query,
            "additional_info_needed": "Xin lỗi, tôi chưa hiểu ý bạn! Bạn cần tìm kiếm thông tin về sản phẩm nào?"
        }


# Hàm xử lý câu hỏi tư vấn chung
def handle_consultation_query(query):
    try:
        prompt = f"""Bạn là một chuyên viên tư vấn bán quần áo túi nam và nữ. Khách hàng hỏi: {query}

                Bạn dựa vào câu hỏi của khách hàng để tạo ra câu truy vấn MySQL tìm kiếm các thông tin theo yêu cầu của khách hàng. 
                #Chú ý: không cần trả lời câu hỏi của khách hàng mà chỉ cần tạo ra câu truy vấn mysql select tìm kiếm các thông tin theo yêu cầu của khách hàng.
                #Lưu ý: 
                - Câu truy vấn cần giới hạn tối đa 5 kết quả.
                - Đảm bảo không có ký tự đặc biệt trong câu truy vấn.
                # Trả về câu truy vấn MySQL, không cần giải thích gì thêm.
                Cơ sở dữ liệu `shoplinhkien` bao gồm các bảng sau:

            - *sanpham*: Sản phẩm quần áo túi của nam và nữ, gồm `sanphamid` (ID, khóa chính), `tensanpham` (tên sản phẩm), `mota` (mô tả sản phẩm), `gia` (giá bán), `madanhmuc` (liên kết với danh mục), `makhuyenmai` (liên kết với khuyến mãi), `chatlieu` (chất liệu), `thuonghieu` (thương hiệu), `baohanh` (bảo hành).
            - *khuyenmai*: Khuyến mãi, gồm `khuyenmaiid` (ID, khóa chính), `tenkhuyenmai` (tên khuyến mãi), `giatri` (giá trị giảm giá), `ngaybatdau` (ngày bắt đầu), `ngayketthuc` (ngày kết thúc).
            - *danhmuc*: Danh mục sản phẩm, gồm `danhmucid` (ID, khóa chính), `tendanhmuc` (tên danh mục).
            - *mausac*: Màu sắc sản phẩm, gồm `mausacid` (ID, khóa chính), `tenmau` (tên màu), `mamau` (mã màu hex).
            - *size*: Kích cỡ sản phẩm, gồm `sizeid` (ID, khóa chính), `kichco` (kích cỡ sản phẩm).
            - *hinhanhsanpham*: Hình ảnh sản phẩm, gồm `hinhanhid` (ID, khóa chính), `masanpham` (liên kết với sản phẩm), `duongdan` (đường dẫn hình ảnh).
                """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[{"role": "system", "content": "Bạn là chuyên gia truy vấn dữ liệu MySQL."},
                      {"role": "user", "content": prompt}],
            temperature=0.5,
            max_tokens=2000
        )

        query_sql = response.choices[0].message.content.strip().replace('```sql', '').replace('```', '')
        return query_sql
    except Exception as e:
        print(f"Lỗi xử lý tư vấn: {e}")
        return "Xin lỗi, tôi gặp khó khăn khi xử lý yêu cầu."


# Hàm xử lý tìm kiếm sản phẩm
def handle_product_search_query(query):
    try:
        # Tạo câu truy vấn SQL theo câu hỏi của người dùng
        prompt = f"""Bạn là một chuyên viên tư vấn bán quần áo túi nam và nữ. Khách hàng hỏi: {query}
    QUAN TRỌNG:
        bạn dựa vào câu hỏi của người dung để tạo ra câu truy vấn mysql tìm kiếm SẢN PHẨM  phù hợp yêu cầu người dùng.
        # Chú ý: không cần trả lời câu hỏi của khách hàng mà chỉ cần tạo ra câu truy vấn mysql select tìm kiếm các thông tin theo yêu cầu của khách hàng.
        # lưu ý thêm phần giới hạn top 5 kết quả truy trong câu truy vấn mysql. nên dùng like cho tìm kiếm trên text
        CHỈ TRẢ VỀ CÂU TRUY VẤN MYSQL KHÔNG ĐƯỢC CÓ CÁC KÝ TỰ ĐẶT BIỆT GÌ HẾT.

        Cơ sở dữ liệu `shoplinhkien` bao gồm các bảng sau:
        - *sanpham*: Sản phẩm quần áo túi của nam và nữ, gồm `sanphamid` (ID, khóa chính), `tensanpham` (tên sản phẩm), `mota` (mô tả sản phẩm), `gia` (giá bán), `madanhmuc` (liên kết với danh mục), `makhuyenmai` (liên kết với khuyến mãi), `chatlieu` (chất liệu), `thuonghieu` (thương hiệu), `baohanh` (bảo hành).
        - *khuyenmai*: Khuyến mãi, gồm `khuyenmaiid` (ID, khóa chính), `tenkhuyenmai` (tên khuyến mãi), `giatri` (giá trị giảm giá), `ngaybatdau` (ngày bắt đầu), `ngayketthuc` (ngày kết thúc).
        - *danhmuc*: Danh mục sản phẩm, gồm `danhmucid` (ID, khóa chính), `tendanhmuc` (tên danh mục).
        - *mausac*: Màu sắc sản phẩm, gồm `mausacid` (ID, khóa chính), `tenmau` (tên màu), `mamau` (mã màu hex).
        - *size*: Kích cỡ sản phẩm, gồm `sizeid` (ID, khóa chính), `kichco` (kích cỡ sản phẩm).
        - *hinhanhsanpham*: Hình ảnh sản phẩm, gồm `hinhanhid` (ID, khóa chính), `masanpham` (liên kết với sản phẩm), `duongdan` (đường dẫn hình ảnh).

        dưới đây là ví dụ về câu truy vấn
        Hãy tạo câu truy vấn MySQL tìm kiếm sản phẩm với cấu trúc sau:
            SELECT sp.*, MIN(ha.duongdan) as duongdan
            FROM sanpham sp
            LEFT JOIN hinhanhsanpham ha ON sp.sanphamid = ha.masanpham
            WHERE [điều kiện tìm kiếm]
            GROUP BY sp.sanphamid
            LIMIT 5
        """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[{"role": "system", "content": "Bạn là chuyên gia truy vấn dữ liệu MySQL."},
                      {"role": "user", "content": prompt}],
            temperature=0.3,
            max_tokens=2000
        )

        query_sql = response.choices[0].message.content.strip().replace('```sql', '').replace('```', '')
        return query_sql
    except Exception as e:
        print(f"Lỗi xử lý tìm kiếm sản phẩm: {e}")
        return "Xin lỗi, tôi gặp khó khăn khi xử lý yêu cầu tìm kiếm sản phẩm."


# Hàm thực thi câu truy vấn MySQL
def execute_query(query):
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor(dictionary=True)
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

def filter_results_by_keywords(data, query):
    """Lọc kết quả tìm kiếm theo từ khóa trong câu hỏi người dùng"""
    keywords = query.lower().split()
    filtered = []
    for item in data:
        text = f"{item.get('tensanpham','')} {item.get('mota','')} {item.get('chatlieu','')}".lower()
        if all(kw in text for kw in keywords if len(kw) > 2):
            filtered.append(item)
    return filtered or data  # nếu lọc trống thì giữ nguyên kết quả ban đầu

# Hàm tạo câu trả lời văn bản
def generate_answer(data, query):
    """Tạo câu trả lời văn bản dựa trên yêu cầu tìm kiếm sản phẩm"""
    try:
        prompt = f"""Người dùng yêu cầu là: '{query}'.
        Bạn dựa vào yêu cầu và dữ liệu tìm được để tạo ra câu trả lời cho người dùng theo dạng văn bản thông thường.
        Trả lời ngắn gọn đúng trọng tâm.
        Không dùng ký tự ** hoặc markdown.
        Nếu thiếu thông tin thì trả về không có thông tin về yêu cầu của người dùng.
        đây là dữ liệu tìm được: '{data}' 
        """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system", "content": "Bạn là một trợ lý tư vấn trả lời câu hỏi về shop bán quần áo và túi nam nữ."},
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


# Hàm tạo HTML cho sản phẩm
def generate_product_card(data, query):
    """Tạo mã HTML cho sản phẩm dựa trên yêu cầu tìm kiếm của người dùng"""
    try:
        prompt = f"""yêu cầu của người dùng là: '{query}'.
        bạn dựa vào yêu cầu và dữ liệu tôi cung cấp để tạo ra câu trả lời về các sản phẩm cần tìm kiếm.
        dữ liệu tìm kiếm được là: '{data}'
        # CẤU TRÚC CÂU TRẢ LỜI
        Phần 1. Nếu tìm thấy sản phẩm, hãy tạo 1 câu dẫn phản hồi các yêu cầu của người dùng hoặc nếu không có thông tin thì trả lời là không có sản phẩm phù hợp.
        Phần 2. Đoạn mã HTML để hiển thị sản phẩm: 
        - Mỗi sản phẩm là 1 <div class="product-card">.
        - Trong mỗi product-card chỉ có ảnh, tên sản phẩm, giá (hoặc giá khuyến mãi nếu có).
        - Khi nhấn vào toàn bộ thẻ product-card thì chuyển hướng đến ProductDetail.php?id=... (dùng thuộc tính onclick cho div).

        Ví dụ trả về:
        'Tìm thấy 2 sản phẩm phù hợp: <div class="product-list">
            <div class="product-card" onclick="window.location.href='ProductDetail.php?id=31'">
                <img src="picture/1744571319_278381rbr-0006.jpg" class="product-image">
                <div class="product-name">Mạch Thu Phát RF Lora SX1278 433Mhz Ra-02 - có ra chân</div>
                <div class="product-price">đ89.500 - đ93.599</div>
            </div>
            <div class="product-card" onclick="window.location.href='ProductDetail.php?id=37'">
                <img src="picture/1744573300_avr-3.jpg" class="product-image">
                <div class="product-name">Tên sản phẩm khác</div>
                <div class="product-price">đ120.000</div>
            </div>
        </div>'
        """
        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system", "content": "Bạn là một trợ lý tạo truy vấn MySQL chính xác dựa trên yêu cầu tìm kiếm sản phẩm."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.5,
            max_tokens=4000
        )

        result = response.choices[0].message.content.strip()
        return result
    except Exception as e:
        print(f"Lỗi tạo thẻ sản phẩm: {e}")
        return "Xin lỗi, hệ thống gặp sự cố khi hiển thị sản phẩm."


def lay_doanh_thu_loi_nhuan(ngay=None, thang=None, nam=None):
    """
    Lấy doanh thu và lợi nhuận theo ngày, tháng, năm (có thể truyền 1, 2 hoặc cả 3 tham số).
    """
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        if not conn:
            return {'doanh_thu': 0, 'loi_nhuan': 0}

        query = """
            SELECT 
                COALESCE(SUM(cd.gia * cd.soluong), 0) as doanh_thu,
                COALESCE(SUM((cd.gia - sp.gianhap) * cd.soluong), 0) as loi_nhuan
            FROM donhang d
            JOIN chitietdonhang cd ON d.donhangid = cd.madonhang
            JOIN sanpham sp ON cd.masanpham = sp.sanphamid
            WHERE d.trangthai = 'Hoàn thành'
        """
        params = []

        if nam:
            query += " AND YEAR(d.ngaydat) = %s"
            params.append(nam)
        if thang:
            query += " AND MONTH(d.ngaydat) = %s"
            params.append(thang)
        if ngay:
            query += " AND DAY(d.ngaydat) = %s"
            params.append(ngay)

        cursor = conn.cursor(dictionary=True)
        cursor.execute(query, tuple(params))
        ket_qua = cursor.fetchone()
        cursor.close()
        conn.close()

        return ket_qua or {'doanh_thu': 0, 'loi_nhuan': 0}
    except Exception as e:
        print(f"Lỗi trong lay_doanh_thu_loi_nhuan: {e}")
        return {'doanh_thu': 0, 'loi_nhuan': 0}
def clean_response(text):
    # Bỏ ký tự ** (markdown bold) và các ký tự không mong muốn
    text = re.sub(r"\*\*", "", text)
    return text

def check_admin_joined_status(session_id):
    """Kiểm tra trạng thái is_admin_joined của session"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT is_admin_joined 
            FROM chat_history 
            WHERE session_id = %s 
            ORDER BY created_at DESC 
            LIMIT 1
        """
        cursor.execute(query, (session_id,))
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        
        return bool(result['is_admin_joined']) if result else False
    except Exception as e:
        print(f"Lỗi kiểm tra admin status: {e}")
        return False

@app.route('/api/chat', methods=['POST'])
def chat():
    try:
        data = request.json
        message_text = data.get("message", "").strip()
        session_id = data.get("session_id")
        if not session_id:
            session_id = str(uuid.uuid4())

        # Kiểm tra trạng thái admin đã tham gia chưa
        is_admin_joined = check_admin_joined_status(session_id)

        # Lưu tin nhắn user, giữ đúng trạng thái admin
        save_chat_history(session_id, "user", message_text, is_admin_joined=is_admin_joined)

        if not message_text:
            return jsonify({"status": "error", "message": "Vui lòng nhập tin nhắn"}), 400

        if is_admin_joined:
            # Nếu admin đã tham gia thì bot im lặng
            return jsonify({"status": "success", "response": "", "session_id": session_id}), 200

        classification = classify_user_request(message_text)
        print(f"Phân loại yêu cầu: {classification}")

        # Xử lý trường hợp cần admin phản hồi
        if classification.get("request_type") == "need_admin":
            admin_notify = classification.get("additional_info_needed", "Yêu cầu của bạn đã được chuyển tới admin. Vui lòng đợi phản hồi từ admin.")
            save_conversation(message_text, admin_notify, "need_admin")
            save_chat_history(session_id, "bot", admin_notify)
            return jsonify({"status": "success", "response": admin_notify, "session_id": session_id}), 200

        if classification["request_type"] == "consultation":
            # Xử lý câu hỏi tư vấn
            query = handle_consultation_query(message_text)
            print(f"Câu truy vấn cho tư vấn: {query}")
            data = execute_query(query)
            print(f"Dữ liệu tìm được: {data}")
            response = generate_answer(data, message_text)
            print(f"Trả về câu trả lời: {response}")

            # Nếu không trả lời được HOẶC trả lời có chứa các từ khóa xin lỗi, không có thông tin, v.v.
            if (
                response == "Xin lỗi, tôi gặp khó khăn khi xử lý yêu cầu."
                or "Xin lỗi" in response
                or "không có thông tin" in response
                or "không thể giúp" in response
                or "không thể trả lời" in response
                or "không có sản phẩm phù hợp" in response
            ):
                # Thông báo đợi admin phản hồi
                admin_notify = "Xin lỗi, câu hỏi của bạn khá chi tiết hoặc ngoài phạm vi dữ liệu. Vui lòng đợi phản hồi từ admin."
                save_conversation(message_text, response, classification["request_type"])
                response = clean_response(response)
                return jsonify({"status": "success", "response": admin_notify}), 200

            # Lưu câu trả lời của bot
            save_chat_history(session_id, "bot", response)
            return jsonify({"status": "success", "response": response, "session_id": session_id}), 200

        elif classification["request_type"] == "product_search":
            # Xử lý tìm kiếm sản phẩm
            query = handle_product_search_query(message_text)
            print(f"Câu truy vấn cho tìm kiếm sản phẩm: {query}")
            data = execute_query(query)
            data = filter_results_by_keywords(data, message_text)

            print(f"Dữ liệu tìm được: {data}")
            for item in data:
                if 'duongdan' not in item or not item['duongdan']:
                    item['duongdan'] = 'picture/no-image.png'
                else:
                    duongdan_str = str(item['duongdan'])
                    if not duongdan_str.startswith('picture/'):
                        item['duongdan'] = f"picture/{duongdan_str}"
            response = generate_product_card(data, message_text)
            print(f"Trả về thẻ sản phẩm: {response}")
            # Nếu không trả lời được HOẶC trả lời có chứa các từ khóa xin lỗi, không có thông tin, v.v.
            if (
                response == "Xin lỗi, tôi gặp khó khăn khi xử lý yêu cầu tìm kiếm sản phẩm."
                or "Xin lỗi" in response
                or "không có thông tin" in response
                or "không thể giúp" in response
                or "không thể trả lời" in response
                or "không có sản phẩm phù hợp" in response
            ):
                # Thông báo đợi admin phản hồi
                admin_notify = "Xin lỗi, câu hỏi của bạn khá chi tiết hoặc ngoài phạm vi dữ liệu. Vui lòng đợi phản hồi từ admin."
                save_conversation(message_text, response, classification["request_type"])
                return jsonify({"status": "success", "response": admin_notify}), 200

            save_chat_history(session_id, "bot", response)
            return jsonify({"status": "success", "response": response, "session_id": session_id}), 200

        else:  # need_more_info
            response_text = classification["additional_info_needed"]
            return jsonify({"status": "success", "response": response_text}), 200

    except Exception as e:
        print(f"Lỗi xử lý: {e}")
        traceback.print_exc()
        return jsonify({"status": "error", "message": f"Đã xảy ra lỗi: {e}"}), 500


def save_conversation(user_message, bot_response, request_type, status='waiting_for_admin'):
    """Lưu cuộc trò chuyện vào bảng conversations với trạng thái mặc định là 'waiting_for_admin'"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor()
        query = """
            INSERT INTO conversations (user_message, bot_response, user_id, status)
            VALUES (%s, %s, %s, %s)
        """
        cursor.execute(query, (user_message, bot_response, 1, status))  # Lưu với trạng thái được truyền vào
        conn.commit()
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Lỗi lưu cuộc trò chuyện: {e}")


@app.route('/api/admin/reply', methods=['POST'])
def admin_reply():
    try:
        data = request.json
        conversation_id = data.get("conversation_id")
        admin_message = data.get("admin_message")

        if not conversation_id or not admin_message:
            return jsonify({"status": "error", "message": "Vui lòng cung cấp thông tin đầy đủ"}), 400

        # Cập nhật trạng thái và câu trả lời của admin
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor()
        query = """
            UPDATE conversations
            SET admin_message = %s, status = 'admin_responded'
            WHERE id = %s
        """
        cursor.execute(query, (admin_message, conversation_id))
        conn.commit()
        cursor.close()
        conn.close()

        return jsonify({"status": "success", "message": "Trả lời của admin đã được lưu"}), 200

    except Exception as e:
        print(f"Lỗi trả lời của admin: {e}")
        return jsonify({"status": "error", "message": "Đã xảy ra lỗi khi trả lời câu hỏi"}), 500



@app.route('/api/admin/conversations', methods=['GET'])
def admin_conversations():
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor(dictionary=True)

        # Lấy tất cả tin nhắn từ chat_history
        query = """
            SELECT id, session_id, sender, message, created_at
            FROM chat_history
            ORDER BY created_at DESC
        """
        cursor.execute(query)
        conversations = cursor.fetchall()

        cursor.close()
        conn.close()

        return jsonify({"status": "success", "conversations": conversations}), 200
    except Exception as e:
        print(f"Lỗi xem cuộc trò chuyện: {e}")
        return jsonify({"status": "error", "message": "Đã xảy ra lỗi khi xem cuộc trò chuyện"}), 500




# @app.route('/api/admin/conversation/<int:conversation_id>', methods=['GET'])
# def get_conversation(conversation_id):
#     try:
#         conn = mysql.connector.connect(
#             host="localhost",
#             user="root",
#             password="",
#             database="shoplinhkien"
#         )
#         cursor = conn.cursor(dictionary=True)
#         query = "SELECT * FROM conversations WHERE id = %s"
#         cursor.execute(query, (conversation_id,))
#         conversation = cursor.fetchone()
#         cursor.close()
#         conn.close()
#
#         if conversation:
#             return jsonify({"status": "success", "conversation": conversation}), 200
#         else:
#             return jsonify({"status": "error", "message": "Cuộc trò chuyện không tồn tại"}), 404
#
#     except Exception as e:
#         print(f"Lỗi lấy cuộc trò chuyện: {e}")
#         return jsonify({"status": "error", "message": "Đã xảy ra lỗi khi lấy cuộc trò chuyện"}), 500


# Hàm nhỏ: Lấy doanh thu và lợi nhuận
def lay_doanh_thu_loi_nhuan_theo_thang_nam(thang, nam):
    """Lấy doanh thu và lợi nhuận cho một tháng và năm cụ thể"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        if not conn:
            return {'doanh_thu': 0, 'loi_nhuan': 0}
        query = """ 
            SELECT 
                COALESCE(SUM(cd.gia * cd.soluong), 0) as doanh_thu,
                COALESCE(SUM((cd.gia - sp.gianhap) * cd.soluong), 0) as loi_nhuan
            FROM donhang d
            JOIN chitietdonhang cd ON d.donhangid = cd.madonhang
            JOIN sanpham sp ON cd.masanpham = sp.sanphamid
            WHERE MONTH(d.ngaydat) = %s 
            AND YEAR(d.ngaydat) = %s
            AND d.trangthai = 'Hoàn thành'
        """
        cursor = conn.cursor(dictionary=True)
        cursor.execute(query, (thang, nam))
        ket_qua = cursor.fetchone()
        cursor.close()
        conn.close()
        return ket_qua or {'doanh_thu': 0, 'loi_nhuan': 0}
    except Exception as e:
        print(f"Lỗi trong lay_doanh_thu_loi_nhuan_theo_thang_nam: {e}")
        return {'doanh_thu': 0, 'loi_nhuan': 0}

# Hàm nhỏ: Lấy top 3 sản phẩm bán chạy

def lay_san_pham_ban_chay(thang, nam):
    """Lấy top 3 sản phẩm bán chạy nhất trong tháng"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        if not conn:
            return []
        query = """
            SELECT 
                sp.tensanpham,
                SUM(cd.soluong) as tong_so_luong
            FROM donhang d
            JOIN chitietdonhang cd ON d.donhangid = cd.madonhang
            JOIN sanpham sp ON cd.masanpham = sp.sanphamid
            WHERE MONTH(d.ngaydat) = %s 
            AND YEAR(d.ngaydat) = %s
            AND d.trangthai = 'Hoàn thành'
            GROUP BY sp.sanphamid, sp.tensanpham
            ORDER BY tong_so_luong DESC
            LIMIT 3
        """
        cursor = conn.cursor(dictionary=True)
        cursor.execute(query, (thang, nam))
        ket_qua = cursor.fetchall()
        cursor.close()
        conn.close()
        return ket_qua
    except Exception as e:
        print(f"Lỗi trong lay_san_pham_ban_chay: {e}")
        return []

# Hàm nhỏ: Thống kê đơn hàng thành công/thất bại

def lay_thong_ke_don_hang(thang, nam):
    """Lấy thống kê đơn hàng thành công và không thành công"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        if not conn:
            return {'thanh_cong': 0, 'that_bai': 0}
        query = """
            SELECT 
                SUM(CASE WHEN trangthai = 'Hoàn thành' THEN 1 ELSE 0 END) as thanh_cong,
                SUM(CASE WHEN trangthai != 'Hoàn thành' THEN 1 ELSE 0 END) as that_bai
            FROM donhang
            WHERE MONTH(ngaydat) = %s 
            AND YEAR(ngaydat) = %s
        """
        cursor = conn.cursor(dictionary=True)
        cursor.execute(query, (thang, nam))
        ket_qua = cursor.fetchone()
        cursor.close()
        conn.close()
        return ket_qua or {'thanh_cong': 0, 'that_bai': 0}
    except Exception as e:
        print(f"Lỗi trong lay_thong_ke_don_hang: {e}")
        return {'thanh_cong': 0, 'that_bai': 0}

def extract_month_year_from_text(text):
    """
    Trích xuất tháng và năm từ câu hỏi tiếng Việt.
    Trả về (thang, nam) hoặc (None, None) nếu không tìm thấy.
    """
    now = datetime.now()
    text = text.lower()
    # Tìm "tháng X" (X là số)
    match = re.search(r"tháng\s*(\d{1,2})", text)
    thang = None
    nam = None
    if match:
        thang = int(match.group(1))
        # Tìm năm nếu có
        match_nam = re.search(r"năm\s*(\d{4})", text)
        if match_nam:
            nam = int(match_nam.group(1))
        else:
            nam = now.year
        return thang, nam
    # "tháng này"
    if "tháng này" in text:
        return now.month, now.year
    # "tháng trước"
    if "tháng trước" in text:
        prev_month = now.month - 1 if now.month > 1 else 12
        prev_year = now.year if now.month > 1 else now.year - 1
        return prev_month, prev_year
    # "tháng sau"
    if "tháng sau" in text:
        next_month = now.month + 1 if now.month < 12 else 1
        next_year = now.year if now.month < 12 else now.year + 1
        return next_month, next_year
    return None, None

# Sửa hàm này để nhận thêm thang, nam

def tao_chien_luoc_kinh_doanh(ngay_hien_tai=None, thang=None, nam=None):
    if thang is not None and nam is not None:
        dt = datetime(year=nam, month=thang, day=1)
    elif ngay_hien_tai is not None:
        dt = ngay_hien_tai
    else:
        dt = datetime.now()
    thang = dt.month
    su_kien = {
        1: ["Tết Dương lịch",
            "Giảm giá 10-20% cho quần áo mùa đông và túi xách cao cấp. Chạy quảng cáo Google Ads và Instagram với hình ảnh lễ hội. Tặng lịch năm mới cho đơn từ 1 triệu."],
        2: ["Tết Nguyên Đán",
            "Khuyến mãi combo quần áo và túi xách thời trang. Tặng bao lì xì cho đơn từ 800k. Chạy quảng cáo TikTok với video phong cách Tết."],
        3: ["Mùa xuân hè",
            "Giảm giá 15% cho bộ sưu tập xuân hè. Hợp tác KOLs trên Instagram để quảng bá váy và túi xách. Ưu đãi mua 1 tặng 1 cho phụ kiện."],
        4: ["Lễ 30/4 - 1/5",
            "Flash sale 20% cho quần áo nam nữ và túi xách. Chạy quảng cáo TikTok và Zalo với chủ đề du lịch. Tặng voucher cho đơn hàng tiếp theo."],
        5: ["Mùa hè",
            "Khuyến mãi 25% cho áo thun, váy đi biển, bikini. Quảng cáo Instagram Reels với hình ảnh năng động. Tặng mũ du lịch cho đơn từ 700k."],
        6: ["Quốc tế Thiếu Nhi",
            "Giảm giá 20% cho quần áo nữ và túi xách thời trang. Tặng phụ kiện cho đơn từ 600k. Chạy chiến dịch quảng cáo TikTok nhắm đến giới trẻ."],
        7: ["Mùa du lịch",
            "Ưu đãi 15% cho set đồ du lịch và túi xách. Chạy quảng cáo Facebook với nội dung 'Sẵn sàng cho kỳ nghỉ'. Tích điểm gấp đôi cho khách hàng thân thiết."],
        8: ["Back to school",
            "Giảm giá 20% cho balo và quần áo phong cách học sinh, sinh viên. Chạy quảng cáo TikTok và Instagram với chủ đề trẻ trung. Tặng sổ tay cho đơn từ 500k."],
        9: ["Lễ Quốc khánh 2/9",
            "Khuyến mãi 15% cho áo sơ mi công sở và túi xách. Tặng voucher 100k cho khách hàng thân thiết. Chạy quảng cáo Zalo với nội dung yêu nước."],
        10: ["Lễ Halloween",
             "Giảm giá 20% cho trang phục phong cách Halloween và phụ kiện. Chạy quảng cáo TikTok với video cosplay. Tặng mặt nạ cho đơn từ 600k."],
        11: ["Black Friday",
             "Flash sale 30% toàn bộ sản phẩm. Chạy quảng cáo đa kênh (Facebook, TikTok, Google). Tặng quà ngẫu nhiên cho đơn từ 1 triệu."],
        12: ["Giáng sinh & Năm mới",
             "Giảm giá 15-25% toàn bộ sản phẩm. Chạy quảng cáo Instagram với hình ảnh Giáng sinh. Tặng hộp quà hoặc phụ kiện cho đơn từ 800k."]
    }
    chien_luoc = su_kien.get(thang, ["Không có sự kiện đặc biệt", "Tập trung quảng cáo các sản phẩm bán chạy, đẩy mạnh SEO, email marketing và chăm sóc khách hàng thân thiết."])
    return {
        'su_kien': chien_luoc[0],
        'chien_luoc': chien_luoc[1]
    }

def luu_bao_cao(noi_dung):
    """Lưu báo cáo văn bản vào cơ sở dữ liệu"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        if not conn:
            print("Không thể kết nối cơ sở dữ liệu để lưu báo cáo")
            return False
        query = """
            INSERT INTO baocao (noi_dung, ngaytao)
            VALUES (%s, NOW())
        """
        cursor = conn.cursor()
        cursor.execute(query, (noi_dung,))
        conn.commit()
        cursor.close()
        conn.close()
        return True
    except Exception as e:
        print(f"Lỗi khi lưu báo cáo: {e}")
        return False

def tao_bao_cao_thang():
    """Tạo báo cáo toàn diện cho tháng hiện tại"""
    try:
        ngay_hien_tai = datetime.now()
        thang_hien_tai = ngay_hien_tai.month
        nam_hien_tai = ngay_hien_tai.year
        # Tính tháng và năm trước đó
        if thang_hien_tai == 1:
            thang_truoc = 12
            nam_truoc = nam_hien_tai - 1
        else:
            thang_truoc = thang_hien_tai - 1
            nam_truoc = nam_hien_tai
        # Lấy dữ liệu tháng hiện tại
        thong_ke_hien_tai = lay_doanh_thu_loi_nhuan_theo_thang_nam(thang_hien_tai, nam_hien_tai)
        if thong_ke_hien_tai is not None and not isinstance(thong_ke_hien_tai, dict):
            thong_ke_hien_tai = dict(thong_ke_hien_tai)
        san_pham_ban_chay = lay_san_pham_ban_chay(thang_hien_tai, nam_hien_tai)
        thong_ke_don_hang = lay_thong_ke_don_hang(thang_hien_tai, nam_hien_tai)
        thong_ke_truoc = lay_doanh_thu_loi_nhuan_theo_thang_nam(thang_truoc, nam_truoc)
        if thong_ke_truoc is not None and not isinstance(thong_ke_truoc, dict):
            thong_ke_truoc = dict(thong_ke_truoc)
        ti_le_doanh_thu = ((float(thong_ke_hien_tai['doanh_thu']) - float(thong_ke_truoc['doanh_thu'])) / float(thong_ke_truoc['doanh_thu']) * 100) if float(thong_ke_truoc['doanh_thu']) > 0 else 0
        ti_le_loi_nhuan = ((float(thong_ke_hien_tai['loi_nhuan']) - float(thong_ke_truoc['loi_nhuan'])) / float(thong_ke_truoc['loi_nhuan']) * 100) if float(thong_ke_truoc['loi_nhuan']) > 0 else 0
        so_sanh = {
            'ti_le_doanh_thu': round(ti_le_doanh_thu, 2),
            'ti_le_loi_nhuan': round(ti_le_loi_nhuan, 2),
            'nhan_xet': (
                f"Doanh thu {'tăng' if ti_le_doanh_thu > 0 else 'giảm'} {abs(round(ti_le_doanh_thu, 2))}% "
                f"và lợi nhuận {'tăng' if ti_le_loi_nhuan > 0 else 'giảm'} {abs(round(ti_le_loi_nhuan, 2))}% "
                "so với tháng trước."
            )
        }
        chien_luoc = tao_chien_luoc_kinh_doanh(ngay_hien_tai)
        bao_cao = {
            'thang': thang_hien_tai,
            'nam': nam_hien_tai,
            'doanh_thu': float(thong_ke_hien_tai['doanh_thu']),
            'loi_nhuan': float(thong_ke_hien_tai['loi_nhuan']),
            'so_sanh': so_sanh,
            'san_pham_ban_chay': [
                {'ten': sp['tensanpham'], 'so_luong': int(sp['tong_so_luong'])}
                for sp in san_pham_ban_chay
            ],
            'thong_ke_don_hang': {
                'thanh_cong': int(thong_ke_don_hang['thanh_cong'] or 0),
                'that_bai': int(thong_ke_don_hang['that_bai'] or 0)
            },
            'chien_luoc_kinh_doanh': chien_luoc
        }
        return bao_cao
    except Exception as e:
        print(f"Lỗi trong tao_bao_cao_thang: {e}")
        return {}

def tao_bao_cao_van_ban(bao_cao_du_lieu):
    """Tạo báo cáo dạng văn bản bằng API OpenAI từ dữ liệu báo cáo"""
    try:
        prompt = f"""
        Bạn là một chuyên gia phân tích kinh doanh, hãy viết một bài báo cáo kinh doanh chuyên nghiệp bằng tiếng Việt cho cửa hàng Shop Linh Kiện. Dựa trên dữ liệu dưới đây, tạo một bài báo cáo có cấu trúc rõ ràng, dễ hiểu, và mang tính chuyên nghiệp. Bài báo cáo cần có các phần sau:

        1. **Tổng quan hoạt động kinh doanh**: Nêu rõ doanh thu, lợi nhuận, số đơn hàng thành công và không thành công của tháng {bao_cao_du_lieu['thang']}/{bao_cao_du_lieu['nam']}.
        2. **So sánh với tháng trước**: Đưa ra nhận xét về sự tăng/giảm doanh thu và lợi nhuận so với tháng trước, kèm theo số liệu cụ thể.
        3. **Sản phẩm bán chạy**: Liệt kê top 3 sản phẩm bán chạy nhất, kèm số lượng bán được và nhận xét về xu hướng thị trường.
        4. **Định hướng kinh doanh**: Đưa ra chiến lược kinh doanh cho tháng tới, dựa trên sự kiện {bao_cao_du_lieu['chien_luoc_kinh_doanh']['su_kien']} và gợi ý chiến lược {bao_cao_du_lieu['chien_luoc_kinh_doanh']['chien_luoc']}.

        **Dữ liệu báo cáo**:
        - Doanh thu: {bao_cao_du_lieu['doanh_thu']:,} VND
        - Lợi nhuận: {bao_cao_du_lieu['loi_nhuan']:,} VND
        - So sánh với tháng trước: {bao_cao_du_lieu['so_sanh']['nhan_xet']}
        - Sản phẩm bán chạy: {bao_cao_du_lieu['san_pham_ban_chay']}
        - Đơn hàng: {bao_cao_du_lieu['thong_ke_don_hang']['thanh_cong']} thành công, {bao_cao_du_lieu['thong_ke_don_hang']['that_bai']} không thành công
        - Chiến lược kinh doanh: Sự kiện {bao_cao_du_lieu['chien_luoc_kinh_doanh']['su_kien']}, gợi ý {bao_cao_du_lieu['chien_luoc_kinh_doanh']['chien_luoc']}

        **Yêu cầu**:
        - Bài báo cáo dài khoảng 300-500 từ.
        - Ngôn ngữ trang trọng, phù hợp với báo cáo kinh doanh.
        - Sử dụng số liệu chính xác từ dữ liệu cung cấp.
        - Tránh thêm thông tin không có trong dữ liệu.
        """
        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system",
                 "content": "Bạn là một chuyên gia phân tích kinh doanh, viết báo cáo bằng tiếng Việt chuyên nghiệp."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.5,
            max_tokens=1000
        )
        return response.choices[0].message.content.strip()
    except Exception as e:
        print(f"Lỗi trong tao_bao_cao_van_ban: {e}")
        return "Xin lỗi, không thể tạo báo cáo văn bản. Vui lòng thử lại."


def lay_san_pham_dang_khuyen_mai(thang=None, nam=None):
    """
    Lấy các sản phẩm đang có khuyến mãi hiệu lực.
    Trả về list dict: [ {sanphamid, tensanpham, gia, giakhuyenmai, duongdan, mota, tendanhmuc, tenkhuyenmai, ngaybatdau, ngayketthuc} ]
    """
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor(dictionary=True)

        # Lấy ngày giờ hiện tại nếu thang và nam không được truyền vào
        now = datetime.now()
        if thang is None:
            thang = now.month
        if nam is None:
            nam = now.year

        # Truy vấn để lấy các sản phẩm có khuyến mãi đang diễn ra
        query = '''
            SELECT 
                sp.sanphamid, 
                sp.tensanpham, 
                sp.gia, 
                km.giatri AS giakhuyenmai, 
                MIN(ha.duongdan) AS duongdan,  -- Lấy ảnh đầu tiên của sản phẩm
                sp.mota, 
                dm.tendanhmuc, 
                km.tenkhuyenmai, 
                km.ngaybatdau, 
                km.ngayketthuc
            FROM khuyenmai km
            JOIN sanpham_khuyenmai skm ON km.khuyenmaiid = skm.khuyenmai_id
            JOIN sanpham sp ON skm.sanpham_id = sp.sanphamid
            LEFT JOIN hinhanhsanpham ha ON sp.sanphamid = ha.masanpham
            LEFT JOIN danhmuc dm ON sp.madanhmuc = dm.danhmucid
            WHERE NOW() BETWEEN km.ngaybatdau AND km.ngayketthuc
            GROUP BY sp.sanphamid
        '''

        cursor.execute(query)  # Không cần tham số
        result = cursor.fetchall()
        cursor.close()
        conn.close()

        # Lấy 1 ảnh đại diện duy nhất cho mỗi sản phẩm
        sanpham_dict = {}
        for row in result:
            d = dict(row) if not isinstance(row, dict) else row
            if d['sanphamid'] not in sanpham_dict:
                if not d.get('duongdan'):
                    d['duongdan'] = 'picture/no-image.png'
                elif not str(d['duongdan']).startswith('picture/'):
                    d['duongdan'] = f"picture/{d['duongdan']}"
                sanpham_dict[d['sanphamid']] = d

        return list(sanpham_dict.values())

    except Exception as e:
        print(f"Lỗi trong lay_san_pham_dang_khuyen_mai: {e}")
        return []


# Gọi hàm và kiểm tra kết quả
result = lay_san_pham_dang_khuyen_mai()
print(result)
# Hàm gợi ý sản phẩm nên khuyến mãi theo sự kiện

def goi_y_san_pham_khuyen_mai_theo_su_kien(su_kien, thang=None, nam=None, limit=5):
    """
    Dựa vào nội dung sự kiện (chuỗi), tìm các sản phẩm phù hợp (tên, mô tả, danh mục chứa từ khóa sự kiện).
    Trả về list dict sản phẩm.
    """
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor(dictionary=True)
        now = datetime.now()
        if thang is None:
            thang = now.month
        if nam is None:
            nam = now.year
        # Tách từ khóa từ nội dung sự kiện
        keywords = [w.strip().lower() for w in re.split(r'[,.\-;: ]', su_kien) if len(w.strip()) > 2]
        # Tạo điều kiện LIKE cho tên, mô tả, danh mục
        like_clauses = []
        for kw in keywords:
            like_clauses.append(f"(LOWER(sp.tensanpham) LIKE '%{kw}%' OR LOWER(sp.mota) LIKE '%{kw}%' OR LOWER(dm.tendanhmuc) LIKE '%{kw}%')")
        where_like = ' OR '.join(like_clauses) if like_clauses else '1=1'
        query = f'''
            SELECT sp.sanphamid, sp.tensanpham, sp.gia, ha.duongdan, sp.mota, dm.tendanhmuc
            FROM sanpham sp
            LEFT JOIN hinhanhsanpham ha ON sp.sanphamid = ha.masanpham
            LEFT JOIN danhmuc dm ON sp.madanhmuc = dm.danhmucid
            WHERE {where_like}
            GROUP BY sp.sanphamid
            LIMIT %s
        '''
        cursor.execute(query, (limit,))
        result = cursor.fetchall()
        cursor.close()
        conn.close()
        sanpham_list = []
        for row in result:
            d = dict(row) if not isinstance(row, dict) else row
            if not d.get('duongdan'):
                d['duongdan'] = 'picture/no-image.png'
            elif not str(d['duongdan']).startswith('picture/'):
                d['duongdan'] = f"picture/{d['duongdan']}"
            sanpham_list.append(d)
        return sanpham_list
    except Exception as e:
        print(f"Lỗi trong goi_y_san_pham_khuyen_mai_theo_su_kien: {e}")
        return []

# Prompt cho AI (nếu cần):
# """
# Bạn là trợ lý quản trị shop. Khi admin hỏi "sản phẩm nào đang khuyến mãi", hãy lấy danh sách các sản phẩm có khuyến mãi hiệu lực trong tháng được hỏi (hoặc tháng hiện tại nếu không chỉ định). Trả về thẻ sản phẩm gồm: ảnh, tên, giá gốc, giá khuyến mãi, tên khuyến mãi, thời gian áp dụng.
# Nếu admin hỏi "tháng X nên khuyến mãi sản phẩm nào", hãy dựa vào nội dung sự kiện tháng X, tìm các sản phẩm phù hợp với chủ đề sự kiện (dựa vào tên, mô tả, danh mục), trả về thẻ sản phẩm phù hợp.
# """
def render_admin_product_cards(data, title=None):
    if not data:
        return "Không có sản phẩm phù hợp."
    html = '<div class="product-list" style="display:flex;flex-wrap:wrap;gap:6px;">'
    for sp in data:
        html += (
            '<div class="product-card" style="width:140px;margin:6px 3px 6px 0;padding:6px;border-radius:10px;box-shadow:0 1px 6px rgba(0,0,0,0.07);background:#fff;display:inline-block;vertical-align:top;cursor:pointer;text-align:center;" '
            f'onclick="window.location.href=\'ProductDetail.php?id={sp.get("sanphamid", "")}\';">'
            f'<img src="{sp.get("duongdan", "picture/no-image.png")}" class="product-image" style="width:100%;height:90px;object-fit:cover;border-radius:7px;">'
            f'<div class="product-name" style="font-size:0.95em;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:4px 0 2px 0;">{sp.get("tensanpham", "")}</div>'
            f'<div class="product-price" style="color:#388e3c;font-size:0.97em;font-weight:bold">đ{sp.get("gia", "")}</div>'
            + (f'<div class="product-price" style="color:#e53935;font-size:0.95em;">Giá KM: đ{sp.get("giakhuyenmai", "")}</div>' if sp.get("giakhuyenmai") else '')
            + '</div>'
        )
    html += "</div>"
    return html

@app.route('/api/adminchat', methods=['POST'])
def admin_chat():
    try:
        data = request.json or {}
        message = data.get('message', '').strip().lower()
        if not message:
            return jsonify({"status": "error", "message": "Vui lòng nhập câu hỏi."}), 400

        now = datetime.now()
        thang_hoi, nam_hoi = extract_month_year_from_text(message)
        thang = thang_hoi if thang_hoi is not None else now.month
        nam = nam_hoi if nam_hoi is not None else now.year

        # Nhận diện câu hỏi về sản phẩm đang khuyến mãi
        if any(kw in message for kw in ['đang khuyến mãi', 'sản phẩm khuyến mãi', 'đang giảm giá', 'đang ưu đãi']):
            sanpham_km = lay_san_pham_dang_khuyen_mai(thang, nam)
            if sanpham_km:
                html = render_admin_product_cards(sanpham_km, title=f"Sản phẩm đang khuyến mãi tháng {thang}/{nam}")
                return jsonify({"status": "success", "bao_cao": html}), 200
            else:
                return jsonify({"status": "success", "bao_cao": f"Không có sản phẩm nào đang khuyến mãi trong tháng {thang}/{nam}."}), 200

        # Nhận diện câu hỏi về sản phẩm nên khuyến mãi theo sự kiện
        if any(kw in message for kw in ['nên khuyến mãi', 'nên giảm giá', 'nên ưu đãi']):
            # Lấy nội dung sự kiện tháng được hỏi
            chien_luoc = tao_chien_luoc_kinh_doanh(None, thang, nam)
            su_kien = chien_luoc.get('su_kien','') + ' ' + chien_luoc.get('chien_luoc','')
            sanpham_goiy = goi_y_san_pham_khuyen_mai_theo_su_kien(su_kien, thang, nam, limit=8)
            if sanpham_goiy:
                html = render_admin_product_cards(sanpham_goiy, title=f"Gợi ý sản phẩm nên khuyến mãi tháng {thang}/{nam} (theo sự kiện: {chien_luoc.get('su_kien','')})")
                return jsonify({"status": "success", "bao_cao": html}), 200
            else:
                return jsonify({"status": "success", "bao_cao": f"Không tìm thấy sản phẩm phù hợp với sự kiện/tháng {thang}/{nam}."}), 200

        doanh_thu_loi_nhuan = lay_doanh_thu_loi_nhuan_theo_thang_nam(thang, nam)
        san_pham_ban_chay = lay_san_pham_ban_chay(thang, nam)
        thong_ke_don_hang = lay_thong_ke_don_hang(thang, nam)
        chien_luoc = tao_chien_luoc_kinh_doanh(None, thang, nam)
        # Có thể lấy thêm các số liệu khác nếu muốn

        prompt = f"""
Bạn là trợ lý quản trị shop. Dưới đây là dữ liệu kinh doanh tháng {thang}/{nam}:
- Doanh thu: {doanh_thu_loi_nhuan['doanh_thu']:,} VND
- Lợi nhuận: {doanh_thu_loi_nhuan['loi_nhuan']:,} VND
- Top sản phẩm bán chạy: {san_pham_ban_chay}
- Đơn hàng: {thong_ke_don_hang}
- Chiến lược kinh doanh: {chien_luoc}

Câu hỏi của admin: \"{message}\"

Dựa vào dữ liệu trên, hãy trả lời ngắn gọn, đúng trọng tâm, bằng tiếng Việt.
Nếu câu hỏi không liên quan đến dữ liệu, hãy trả lời "Xin lỗi, tôi không có thông tin về yêu cầu này.".
"""

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system", "content": "Bạn là trợ lý quản trị shop, trả lời ngắn gọn, đúng trọng tâm, bằng tiếng Việt."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.3,
            max_tokens=600
        )
        answer = response.choices[0].message.content.strip()
        return jsonify({"status": "success", "bao_cao": answer}), 200

    except Exception as e:
        print(f"Lỗi trong adminchat: {e}")
        return jsonify({"status": "error", "message": "Đã xảy ra lỗi khi tạo báo cáo. Vui lòng thử lại."}), 500

@app.route('/api/chat/history', methods=['GET'])
def get_chat_history():
    session_id = request.args.get('session_id')
    if not session_id:
        return jsonify({"status": "error", "message": "Thiếu session_id"}), 400
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor(dictionary=True)
        query = "SELECT sender, message, created_at FROM chat_history WHERE session_id = %s ORDER BY created_at ASC"
        cursor.execute(query, (session_id,))
        history = cursor.fetchall()
        cursor.close()
        conn.close()
        return jsonify({"status": "success", "history": history}), 200
    except Exception as e:
        print(f"Lỗi lấy chat_history: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500

def save_chat_history(session_id, sender, message, is_admin_joined=False):
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor()
        query = "INSERT INTO chat_history (session_id, sender, message, is_admin_joined) VALUES (%s, %s, %s, %s)"
        cursor.execute(query, (session_id, sender, message, is_admin_joined))
        conn.commit()
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Lỗi lưu chat_history: {e}")

@app.route('/api/admin/send_message', methods=['POST'])
def admin_send_message():
    try:
        data = request.json
        session_id = data.get("session_id")
        message = data.get("message", "").strip()
        if not session_id or not message:
            return jsonify({"status": "error", "message": "Thiếu session_id hoặc message"}), 400
        
        # Khi admin gửi tin nhắn, tự động bật trạng thái is_admin_joined
        save_chat_history(session_id, "admin", message, is_admin_joined=True)
        
        # Cập nhật tất cả tin nhắn trong session này thành is_admin_joined = True
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor()
        query = "UPDATE chat_history SET is_admin_joined = 1 WHERE session_id = %s"
        cursor.execute(query, (session_id,))
        conn.commit()
        cursor.close()
        conn.close()
        
        return jsonify({"status": "success", "message": "Đã gửi tin nhắn admin"}), 200
    except Exception as e:
        print(f"Lỗi gửi tin nhắn admin: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500

@app.route('/api/admin/sessions', methods=['GET'])
def get_sessions():
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor()
        # Sửa lại truy vấn cho đúng cú pháp
        cursor.execute("""
            SELECT session_id, MAX(created_at) as last_time
            FROM chat_history
            GROUP BY session_id
            ORDER BY last_time DESC
        """)
        sessions = [{"session_id": row[0]} for row in cursor.fetchall()]
        cursor.close()
        conn.close()
        return jsonify({"sessions": sessions})
    except Exception as e:
        return jsonify({"sessions": [], "error": str(e)}), 500
@app.route('/api/admin/pending_sessions', methods=['GET'])
def get_pending_sessions():
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor(dictionary=True)
        # Lấy các session có tin nhắn cuối cùng là thông báo chờ admin
        query = """
            SELECT ch.session_id, ch.message, ch.created_at
            FROM chat_history ch
            INNER JOIN (
                SELECT session_id, MAX(created_at) as last_time
                FROM chat_history
                GROUP BY session_id
            ) last_msg
            ON ch.session_id = last_msg.session_id AND ch.created_at = last_msg.last_time
            WHERE ch.sender = 'bot'
            AND ch.message LIKE '%Vui lòng đợi phản hồi từ admin%'
            ORDER BY ch.created_at DESC
        """
        cursor.execute(query)
        sessions = cursor.fetchall()
        cursor.close()
        conn.close()
        return jsonify({"status": "success", "pending_sessions": sessions}), 200
    except Exception as e:
        print(f"Lỗi lấy pending_sessions: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500

@app.route('/api/admin/conversations_overview', methods=['GET'])
def get_conversations_overview():
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor(dictionary=True)
        # Lấy session_id và tin nhắn cuối cùng của mỗi session
        query = """
            SELECT ch.session_id, ch.sender, ch.message, ch.created_at
            FROM chat_history ch
            INNER JOIN (
                SELECT session_id, MAX(created_at) as last_time
                FROM chat_history
                GROUP BY session_id
            ) last_msg
            ON ch.session_id = last_msg.session_id AND ch.created_at = last_msg.last_time
            ORDER BY ch.created_at DESC
        """
        cursor.execute(query)
        sessions = cursor.fetchall()
        cursor.close()
        conn.close()
        return jsonify({"status": "success", "sessions": sessions}), 200
    except Exception as e:
        print(f"Lỗi lấy conversations_overview: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500

@app.route('/api/admin/toggle_bot', methods=['POST'])
def toggle_bot():
    """API để admin bật/tắt bot cho một session cụ thể"""
    import time
    try:
        data = request.get_json(silent=True) or {}
        session_id = data.get("session_id")
        raw_is_admin_joined = data.get("is_admin_joined", True)

        if not session_id:
            return jsonify({"status": "error", "message": "Thiếu session_id"}), 400

        # Ép kiểu rõ ràng
        if isinstance(raw_is_admin_joined, str):
            raw_is_admin_joined = raw_is_admin_joined.strip().lower() in ("1", "true", "yes", "on")
        is_admin_joined = bool(raw_is_admin_joined)
        is_admin_int = 1 if is_admin_joined else 0

        print(f"[toggle_bot] session_id={session_id} received raw={data.get('is_admin_joined')} coerced={is_admin_int}")

        # Mở kết nối DB
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor()

        # 1) Lấy id của bản ghi mới nhất cho session (an toàn, tránh UPDATE với ORDER BY)
        cursor.execute("SELECT id FROM chat_history WHERE session_id = %s ORDER BY created_at DESC LIMIT 1", (session_id,))
        row = cursor.fetchone()

        if row:
            latest_id = row[0]
            cursor.execute("UPDATE chat_history SET is_admin_joined = %s WHERE id = %s", (is_admin_int, latest_id))
            print(f"[toggle_bot] updated chat_history.id={latest_id} set is_admin_joined={is_admin_int}")
        else:
            # Nếu không có bản ghi nào cho session này thì chèn 1 bản ghi thông báo trạng thái
            cursor.execute(
                "INSERT INTO chat_history (session_id, sender, message, is_admin_joined) VALUES (%s, %s, %s, %s)",
                (session_id, "bot", f"Trạng thái admin được đặt thành {is_admin_int}", is_admin_int)
            )
            print(f"[toggle_bot] inserted new chat_history row for session {session_id}")

        # Nếu admin tắt bot (is_admin_joined = 0), thêm tin nhắn notify cho user
        if is_admin_int == 0:
            # dùng save_chat_history để giữ consistency (hàm này tự commit)
            save_chat_history(session_id, "bot", "Bot đã trở lại! Tôi có thể giúp gì cho bạn?", is_admin_joined=0)

        conn.commit()
        cursor.close()
        conn.close()

        status_text = "bật" if is_admin_int == 1 else "tắt"
        return jsonify({
            "status": "success",
            "message": f"Đã {status_text} bot cho session {session_id}",
            "is_admin_joined": is_admin_joined
        }), 200

    except mysql.connector.Error as db_e:
        print(f"Lỗi DB toggle bot: {db_e}")
        return jsonify({"status": "error", "message": f"Lỗi DB: {db_e}"}), 500
    except Exception as e:
        print(f"Lỗi toggle bot: {e}")
        traceback.print_exc()
        return jsonify({"status": "error", "message": "Lỗi server"}), 500



@app.route('/api/admin/session_status/<session_id>', methods=['GET'])
def get_session_status(session_id):
    """Lấy trạng thái is_admin_joined của một session"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoplinhkien"
        )
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT is_admin_joined 
            FROM chat_history 
            WHERE session_id = %s 
            ORDER BY created_at DESC 
            LIMIT 1
        """
        cursor.execute(query, (session_id,))
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        
        is_admin_joined = result['is_admin_joined'] if result else False
        return jsonify({
            "status": "success", 
            "is_admin_joined": bool(is_admin_joined)
        }), 200
        
    except Exception as e:
        print(f"Lỗi lấy session status: {e}")
        return jsonify({"status": "error", "message": "Lỗi server"}), 500
if __name__ == "__main__":
    app.run(debug=True)