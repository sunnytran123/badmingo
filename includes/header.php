<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sân Cầu Lông SportPro</title>
    <style>
    * {margin:0; padding:0; box-sizing: border-box;}
    body {font-family: "Segoe UI", sans-serif; background: #f8f9fa; color: #333;}
    .sidebar {
        background: linear-gradient(90deg, #007bff, #ff6200); /* Gradient xanh dương và cam */
        padding: 15px; 
        display: flex;
        align-items: center;
        color: white;
        font-size: 24px; 
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .sidebar-content {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: nowrap;
        justify-content: flex-start;
        padding-left: 20px;
        width: 100%;
        max-width: 950px;
        margin: 0 auto;
    }
    .sidebar-content a {
        text-decoration: none;
    }
    .header-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .header-content img {
        max-width: 50px;
        height: auto;
    }
    .header-content span {
        font-size: 24px;
        font-weight: bold;
        color: white;
    }
    .sidebar-menu {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-left: auto;
    }
    .sidebar-menu a {
        padding: 10px 20px;
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: 0.3s;
        border-radius: 5px;
    }
    .sidebar-menu a:hover {
        background: #ff8c00; /* Cam nhạt khi hover */
        color: white;
    }
    .container {
        max-width: 1200px;
        margin: auto;
        padding: 20px;
    }
    .section-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #007bff; /* Xanh dương đậm */
        border-left: 5px solid #007bff; /* Xanh dương đậm */
        padding-left: 10px;
    }
    .news {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    .news-item {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .news-item img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .news-content {
        padding: 15px;
    }
    .booking-form {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    input, select, button {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }
    button {
        background: #007bff; /* Xanh dương đậm */
        color: white;
        border: none;
        cursor: pointer;
    }
    button:hover {
        background: #0056b3; /* Xanh dương tối hơn khi hover */
    }
    .products {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    .product {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        text-align: center;
    }
    .product img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .product-info {
        padding: 15px;
    }
    .price {
        color: #dc3545; /* Giữ màu đỏ cho giá */
        font-weight: bold;
    }
    .chatbox {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        height: 400px;
    }
    .chat-messages {
        border: 1px solid #ddd;
        height: 300px;
        padding: 10px;
        overflow-y: auto;
        margin-bottom: 10px;
        border-radius: 6px;
        font-size: 14px;
    }
    .chat-input {
        display: flex;
        gap: 10px;
    }
    .chat-input input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }
    .chat-input button {
        padding: 10px 15px;
        background: #007bff; /* Xanh dương đậm */
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .chat-input button:hover {
        background: #0056b3; /* Xanh dương tối hơn */
    }
    .shop-intro {
        background: linear-gradient(135deg, #007bff, #ff6200); /* Gradient xanh dương và cam */
        color: #fff;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 40px;
        display: flex;
        justify-content: center;
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
        text-align: center;
    }
    .shop-intro h2 {
        font-size: 2em;
        margin-bottom: 15px;
    }
    .shop-intro p {
        font-size: 1.1em;
        line-height: 1.6;
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
        }
        .shop-intro img {
            max-width: 100%;
        }
        .sidebar-content {
            flex-direction: column;
            align-items: center;
        }
        .sidebar-menu {
            flex-direction: column;
            gap: 10px;
        }
    }
</style><style>
    * {margin:0; padding:0; box-sizing: border-box;}
    body {font-family: "Segoe UI", sans-serif; background: #f8f9fa; color: #333;}
    .sidebar {
        background: linear-gradient(90deg, #007bff, #ff6200); /* Gradient xanh dương và cam */
        padding: 15px; 
        display: flex;
        align-items: center;
        color: white;
        font-size: 24px; 
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .sidebar-content {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: nowrap;
        justify-content: flex-start;
        padding-left: 20px;
        width: 100%;
        max-width: 950px;
        margin: 0 auto;
    }
    .sidebar-content a {
        text-decoration: none;
    }
    .header-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .header-content img {
        max-width: 50px;
        height: auto;
    }
    .header-content span {
        font-size: 24px;
        font-weight: bold;
        color: white;
    }
    .sidebar-menu {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-left: auto;
    }
    .sidebar-menu a {
        padding: 10px 20px;
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: 0.3s;
        border-radius: 5px;
    }
    .sidebar-menu a:hover {
        background: #ff8c00; /* Cam nhạt khi hover */
        color: white;
    }
    .container {
        max-width: 1200px;
        margin: auto;
        padding: 20px;
    }
    .section-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #007bff; /* Xanh dương đậm */
        border-left: 5px solid #007bff; /* Xanh dương đậm */
        padding-left: 10px;
    }
    .news {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    .news-item {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .news-item img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .news-content {
        padding: 15px;
    }
    .booking-form {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    input, select, button {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }
    button {
        background: #007bff; /* Xanh dương đậm */
        color: white;
        border: none;
        cursor: pointer;
    }
    button:hover {
        background: #0056b3; /* Xanh dương tối hơn khi hover */
    }
    .products {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    .product {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        text-align: center;
    }
    .product img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .product-info {
        padding: 15px;
    }
    .price {
        color: #dc3545; /* Giữ màu đỏ cho giá */
        font-weight: bold;
    }
    .chatbox {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        height: 400px;
    }
    .chat-messages {
        border: 1px solid #ddd;
        height: 300px;
        padding: 10px;
        overflow-y: auto;
        margin-bottom: 10px;
        border-radius: 6px;
        font-size: 14px;
    }
    .chat-input {
        display: flex;
        gap: 10px;
    }
    .chat-input input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }
    .chat-input button {
        padding: 10px 15px;
        background: #007bff; /* Xanh dương đậm */
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .chat-input button:hover {
        background: #0056b3; /* Xanh dương tối hơn */
    }
    .shop-intro {
        background: linear-gradient(135deg, #007bff, #ff6200); /* Gradient xanh dương và cam */
        color: #fff;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 40px;
        display: flex;
        justify-content: center;
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
        text-align: center;
    }
    .shop-intro h2 {
        font-size: 2em;
        margin-bottom: 15px;
    }
    .shop-intro p {
        font-size: 1.1em;
        line-height: 1.6;
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
        }
        .shop-intro img {
            max-width: 100%;
        }
        .sidebar-content {
            flex-direction: column;
            align-items: center;
        }
        .sidebar-menu {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-content">
        <a href="index.php">
            <div class="header-content">
                <img src="images/Olypic.png" alt="Sunny Logo">
                <span>Sunny Sport</span>
            </div>
        </a>
        <div class="sidebar-menu">
            <a href="index.php">Trang chủ</a>
            <a href="booking.php">Đặt sân</a>
            <a href="shop.php">Cửa hàng</a>
            <a href="contact.php">Liên hệ</a>
        </div>
    </div>
</div>

<div class="container">