<?php include 'includes/header.php'; ?>
<style>
    p {
        font-size: 1.1em;
        line-height: 1.6;
    }
    .forum-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .section-title {
        text-align: center;
        font-size: 30px;
        margin-bottom: 30px;
        color: #333;
    }
    /* Đồng bộ kích thước chữ và khoảng cách dòng cho các đoạn nội dung */
    .shop-intro p,
    .highlight-section p,
    .category-card p,
    .threads-list p,
    .thread-meta {
        font-size: 1.1em;
        line-height: 1.7;
        margin-bottom: 10px;
        text-align: justify;
    }

    .forum-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 40px;
    }

    .category-card {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
        flex: 1 1 calc(33.333% - 20px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }

    .category-card:hover {
        transform: translateY(-5px);
    }

    .category-card h3 {
        margin: 0 0 10px;
        font-size: 1.5em;
        color: #2c3e50;
    }

    .category-card p {
        margin: 0;
        color: #7f8c8d;
    }

    .threads-list {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 20px;
    }

    .thread-item {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }

    .thread-item:last-child {
        border-bottom: none;
    }

    .thread-title {
        font-size: 1.2em;
        color: #2980b9;
        text-decoration: none;
    }

    .thread-title:hover {
        text-decoration: underline;
    }

    .thread-meta {
        color: #7f8c8d;
        font-size: 0.9em;
    }

    .shop-intro {
        background: linear-gradient(135deg, #007bff, #ff6200);
        color: #fff;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 40px;
        display: flex;
        align-items: center;
        gap: 20px;
        animation: fadeIn 1s ease-in-out;
    }

    .shop-intro img {
        max-width: 300px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        animation: slideIn 1s ease-in-out;
    }

    .shop-intro-content {
        flex: 1;
        text-align: justify;
    }

    .shop-intro h2 {
        font-size: 2em;
        margin-bottom: 15px;
    }

    .shop-intro p {
        font-size: 1.1em;
        line-height: 1.6;
    }

    .highlight-section {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 40px;
    }

    .highlight-section h3 {
        font-size: 1.8em;
        color: #000000ff;
        margin-bottom: 15px;
        text-align: center;
    }

    .highlight-section li {
        font-size: 1.1em;      /* đồng bộ kích thước chữ */
        line-height: 1.6;
        padding-left: 70px;              /* bỏ padding */
        list-style-position: inside;  /* đưa dấu chấm vào trong */
    }
    .highlight-section p {
        font-size: 1.1em;
        line-height: 1.7;
        margin-bottom: 10px;
        text-align: justify;
        padding: 10px;
    }


    .highlight-section img {
        max-width: 600px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        display: block;
        margin: 20px auto;
        animation: slideIn 1s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from { transform: translateX(-50px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @media (max-width: 768px) {
        .category-card {
            flex: 1 1 100%;
        }

        .shop-intro {
            flex-direction: column;
            text-align: center;
            justify-content: space-between;
        }

        .shop-intro img {
            max-width: 100%;
        }

        .highlight-section img {
            max-width: 100%;
        }

    }
</style>

<div class="forum-container">
    <div class="shop-intro">
        <img src="images/Olypic.png" alt="Cửa hàng SportPro">
        <div class="shop-intro-content">
            <h2>Chào mừng đến với Sunny Sport🏸</h2>
            <p>Câu lạc bộ Sunny Sport là nơi hội tụ những người đam mê thể thao, cung cấp các sản phẩm chất lượng cao và tổ chức các sự kiện thể thao sôi động. Với sứ mệnh lan tỏa tinh thần thể thao, chúng tôi mang đến những trải nghiệm tuyệt vời qua các giải đấu, sản phẩm thể thao hàng đầu và cộng đồng gắn kết. Hãy tham gia diễn đàn để chia sẻ đam mê và cập nhật những thông tin mới nhất!</p>
        </div>
    </div>

    <h3 class="section-title">Diễn đàn</h3>

    <div class="forum-categories">
        <div class="category-card">
            <h3>Thảo luận chung</h3>
            <p>Nơi thảo luận về các chủ đề liên quan đến thể thao, kỹ thuật và chiến thuật.</p>
        </div>
        <div class="category-card">
            <h3>Sự kiện & Giải đấu</h3>
            <p>Thông tin về các giải đấu, sự kiện thể thao sắp tới.</p>
        </div>
        <div class="category-card">
            <h3>Thị trường & Sản phẩm</h3>
            <p>Chia sẻ về thiết bị, dụng cụ thể thao và các ưu đãi.</p>
        </div>
    </div>

    <div class="highlight-section">
        <h2 style="text-align: center;">Tin Tức Nổi Bật</h2>
        <p style="text-indent: 30px;">Cầu lông từ lâu đã không chỉ là một môn thể thao rèn luyện thể lực, mà còn là một cách để thư giãn tinh thần, nâng cao sức khỏe tim mạch, cải thiện phản xạ và đặc biệt là gắn kết mọi người lại gần nhau hơn. Mùa giải mới của <b>Sunny Sport</b> sẽ chính thức khởi tranh với nhiều nội dung thi đấu phong phú, từ đơn nam, đơn nữ đến đôi nam, đôi nữ và đôi nam nữ phối hợp, hứa hẹn mang đến cho mọi người cơ hội tuyệt vời để thử sức, nâng cao kỹ thuật, kết nối và học hỏi từ những người chơi giỏi cũng như trải nghiệm bầu không khí thi đấu sôi động.</p>
        <p style="text-indent: 30px;">
            Ngoài ra, các trận đấu sẽ được livestream trực tiếp trên fanpage của Sunny Sport để mọi người có thể theo dõi và cổ vũ từ xa. Đừng bỏ lỡ cơ hội ghi dấu ấn của mình trên bảng thành tích và trong lòng người hâm mộ!
        </p>

        <img src="images/sport1.webp" alt="Giải đấu cầu lông">
        <p style="text-indent: 30px;"><b>Sunny Sport</b> không chỉ là nhà tổ chức sự kiện thể thao, mà còn là điểm đến tin cậy cho mọi tín đồ thể thao. Chúng tôi tự hào mang đến những sản phẩm được tuyển chọn kỹ càng từ các thương hiệu uy tín. Hãy tham gia ngay hôm nay để cùng lan tỏa tinh thần thể thao, kết nối cộng đồng và chinh phục những thử thách mới!</p>

        <img src="images/sport2.webp" alt="Sản phẩm thể thao">
    </div>

    <div class="threads-list">
    <h2 style="text-align: center;">Các chủ đề mới nhất</h2>            
        <div class="thread-item">
            <div>
                <a href="thread.php?id=1" class="thread-title">Hướng dẫn chọn giày chạy bộ phù hợp</a>
                <p class="thread-meta">Đăng bởi Admin • 25/08/2025 • 15 bình luận</p>
            </div>
            <div class="thread-meta">Lần cuối: 03:57 PM, 15/08/2025</div>
        </div>
        <div class="thread-item">
            <div>
                <a href="thread.php?id=2" class="thread-title">Kế hoạch tập luyện cho giải marathon tháng 9</a>
                <p class="thread-meta">Đăng bởi User123 • 20/08/2025 • 8 bình luận</p>
            </div>
            <div class="thread-meta">Lần cuối: 10:00 AM, 14/08/2025</div>
        </div>
        <div class="thread-item">
            <div>
                <a href="thread.php?id=3" class="thread-title">Review vợt tennis mới nhất 2025</a>
                <p class="thread-meta">Đăng bởi TennisFan • 18/08/2025 • 22 bình luận</p>
            </div>
            <div class="thread-meta">Lần cuối: 09:15 AM, 15/08/2025</div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', () => {
            alert('Chuyển hướng đến danh mục: ' + card.querySelector('h3').textContent);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>