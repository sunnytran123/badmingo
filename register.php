<?php
include 'config/database.php';
session_start();

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username   = trim($_POST['username']);
    $password   = trim($_POST['password']);
    $full_name  = trim($_POST['full_name']);
    $phone      = trim($_POST['phone']);
    $email      = trim($_POST['email']);

    // Kiểm tra username/email đã tồn tại
    $check = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "⚠️ Tên đăng nhập hoặc email đã tồn tại!";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, phone, email, role) VALUES (?, ?, ?, ?, ?, 'client')");
        $stmt->bind_param("sssss", $username, $hashedPassword, $full_name, $phone, $email);
        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $message = "❌ Có lỗi xảy ra. Vui lòng thử lại.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .form-container {
            width: 420px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            backdrop-filter: blur(14px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.5);
            color: #fff;
            animation: fadeIn 0.8s ease-in-out;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #007bff, #ff6200);

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
        }
        input, button {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }
        input {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }
        input::placeholder {
            color: #ddd;
        }
        button {
            background: linear-gradient(135deg, #007bff, #ff6200);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        button:hover {
            transform: scale(1.05);
        }
        .message {
            text-align: center;
            color: #ff6b6b;
            margin-bottom: 10px;
        }
        .switch-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .switch-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Đăng ký tài khoản</h2>
        <div class="message"><?= $message ?></div>
        <form method="post">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="text" name="full_name" placeholder="Họ và tên" required>
            <input type="text" name="phone" placeholder="Số điện thoại" required>
            <input type="email" name="email" placeholder="Email">
            <button type="submit">Đăng ký</button>
        </form>
        <div class="switch-link">
            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
        </div>
    </div>
</body>
</html>
