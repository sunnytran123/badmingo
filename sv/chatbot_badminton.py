from flask import Flask, request, jsonify
from openai import OpenAI
import mysql.connector
from datetime import datetime, timedelta
import json
import uuid

app = Flask(__name__)

# Cấu hình CORS thủ công
@app.after_request
def after_request(response):
    response.headers.add('Access-Control-Allow-Origin', '*')
    response.headers.add('Access-Control-Allow-Headers', 'Content-Type,Authorization')
    response.headers.add('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS')
    return response

# Khởi tạo client OpenAI

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
                {"role": "system", "content": "Bạn là một trợ lý phân loại yêu cầu người dùng chính xác và chuyên nghiệp."},
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

        Bạn dựa vào câu hỏi của người dùng để tạo ra câu truy vấn mysql tìm kiếm thông tin sân cầu lông phù hợp.
        # Chú ý: không cần trả lời câu hỏi của khách hàng mà chỉ cần tạo ra câu truy vấn mysql select tìm kiếm các thông tin theo yêu cầu của khách hàng.
        # Lưu ý thêm phần giới hạn top 5 kết quả
        CHỈ TRẢ VỀ CÂU TRUY VẤN MYSQL KHÔNG ĐƯỢC CÓ CÁC KÝ TỰ ĐẶT BIỆT GÌ HẾT.
        
        Cơ sở dữ liệu `sunny_sport` bao gồm các bảng chính liên quan đến sân cầu lông:

        - *courts*: Thông tin sân cầu lông, gồm `court_id` (ID, khóa chính), `court_name` (tên sân, text), `description` (mô tả, text), `price_per_hour` (giá mỗi giờ, decimal).
        - *bookings*: Lịch đặt sân, gồm `booking_id` (ID), `court_id` (ID sân), `booking_date` (ngày đặt, date), `start_time` (giờ bắt đầu, time), `end_time` (giờ kết thúc, time), `status` (trạng thái: pending/confirmed/cancelled).
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
                {"role": "system", "content": "Bạn là một trợ lý tư vấn trả lời câu hỏi về shop cầu lông Sunny Sport. Bạn tạo ra đoạn text trả lời yêu cầu người dùng. Nếu không có thông tin có thể yêu cầu người dùng hỏi lại để có đủ thông tin"},
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
        prompt = f"""Yêu cầu của người dùng là: '{query}'.
        Bạn dựa vào yêu cầu và dữ liệu tôi cung cấp để tạo ra câu trả lời về các sân cầu lông cần tìm kiếm
        Dữ liệu tìm kiếm được là: '{data}'
        
        # YÊU CẦU
        - Trả lời dạng văn bản thông thường, KHÔNG sử dụng HTML
        - Nếu tìm thấy sân: liệt kê tên sân, giá, mô tả
        - Nếu không có sân: thông báo không có sân phù hợp
        - Sử dụng định dạng dễ đọc với dấu gạch đầu dòng hoặc số thứ tự
        
        Ví dụ trả lời:
        'Tìm thấy 3 sân còn trống:
        1. Sân 1 - Giá: 150,000 VNĐ/giờ
           Mô tả: Sàn gỗ tiêu chuẩn, phù hợp tập luyện và thi đấu
        2. Sân 2 - Giá: 150,000 VNĐ/giờ  
           Mô tả: Trang bị điều hòa, tạo không gian thoải mái
        3. Sân 3 - Giá: 150,000 VNĐ/giờ
           Mô tả: Hệ thống chiếu sáng hiện đại, đảm bảo chất lượng trận đấu'
        """

        response = client.chat.completions.create(
            model="gpt-4o-mini-2024-07-18",
            messages=[
                {"role": "system", "content": "Bạn là một trợ lý tư vấn sân cầu lông, trả lời dạng văn bản thông thường."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.5,
            max_tokens=4000
        )
        result = response.choices[0].message.content.strip()
        return result
    except Exception as e:
        print(f"Lỗi tạo court answer: {e}")
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
        - Khi nhấn vào toàn bộ thẻ product-card thì chuyển hướng đến t.php?product_id=... (dùng thuộc tính onclick cho div).
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
                {"role": "system", "content": "Bạn là một trợ lý tạo HTML card hiển thị sản phẩm cầu lông với style đẹp."},
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

def save_chat_history(user_id, role, message):
    """Lưu lịch sử chat vào database"""
    try:
        conn = ket_noi_db()
        if not conn:
            return False
        
        cursor = conn.cursor()
        query = "INSERT INTO chat_history (user_id, role, message) VALUES (%s, %s, %s)"
        cursor.execute(query, (user_id, role, message))
        conn.commit()
        cursor.close()
        conn.close()
        return True
    except Exception as e:
        print(f"Lỗi lưu chat history: {e}")
        return False

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

        # Lưu tin nhắn của user
        save_chat_history(user_id, "user", message_text)

        # Phân loại yêu cầu
        classification = classify_user_request(message_text)
        print("Classification:", classification)
        
        if classification["request_type"] == "court_booking":
            query = handle_court_booking_query(message_text)
            print("Court query:", query)
            data = execute_query(query)
            print("Court data:", data)
            response = generate_court_answer(data, message_text)
            print("Court response:", response)
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
            response_text = classification.get("additional_info_needed", "Xin lỗi, tôi chưa hiểu yêu cầu của bạn. Bạn có thể nói rõ đặt sân hay tìm sản phẩm?")
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
        WHERE user_id = %s 
        ORDER BY created_at DESC 
        LIMIT 20
        """
        data = execute_query(query, (user_id,))
        return jsonify({"status": "success", "history": data}), 200
    except Exception as e:
        print(f"Lỗi lấy chat history: {e}")
        return jsonify({"status": "error", "message": "Không thể lấy lịch sử chat"}), 500

if __name__ == '__main__':
    print("🚀 Đang khởi động server chatbot cầu lông...")
    print("📍 Server: http://localhost:5000")
    print("🔗 API: http://localhost:5000/api/chat")
    print("⏹️  Nhấn Ctrl+C để dừng")
    print("-" * 50)
    try:
        app.run(host="0.0.0.0", port=5000, debug=True)
    except Exception as e:
        print(f"❌ Lỗi khởi động server: {e}")
        print("💡 Thử chạy: python chatbot_badminton.py")
