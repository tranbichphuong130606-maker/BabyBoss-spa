<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc đăng nhập mới xem được lịch sử đơn hàng
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Kết nối CSDL PDO
$host = '127.0.0.1';
$dbname = 'babyboss';
$username = 'root';
$password = '';

$orders = [];
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lấy danh sách lịch đặt lịch của khách hàng kèm thông tin tên dịch vụ và giá tiền
    $stmt = $pdo->prepare("
        SELECT b.*, s.name AS service_name, s.price AS service_price 
        FROM bookings b 
        JOIN services s ON b.service_id = s.id 
        WHERE b.customer_id = ? 
        ORDER BY b.id DESC
    ");
    $stmt->execute([$customer_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Lỗi kết nối CSDL: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - BabyBoss Pet Spa</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/BabyBoss-spa/public/css/style.css">
    <style>
        .orders-container { 
            max-width: 1000px; 
            margin: 0 auto 60px; 
            padding: 0 20px; 
        }
        .order-card { 
            background: #fff; 
            padding: 25px; 
            border-radius: 20px; 
            box-shadow: 0 5px 15px rgba(2,132,199,0.06); 
            border: 2px solid #e0f2fe; 
            margin-bottom: 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-wrap: wrap; 
            gap: 20px; 
        }
        .order-info h3 { 
            color: #0369a1; 
            font-size: 1.2rem; 
            margin-bottom: 8px; 
        }
        .order-info p { 
            color: #64748b; 
            font-size: 0.95rem; 
            margin-bottom: 5px; 
        }
        .order-status { 
            padding: 6px 14px; 
            border-radius: 20px; 
            font-weight: 700; 
            font-size: 0.85rem; 
            background: #fef9c3; 
            color: #854d0e; 
            border: 1px solid #fde047; 
            display: inline-block; 
        }
        .price-tag { 
            font-weight: 700; 
            color: #0284c7; 
            font-size: 1.1rem; 
        }
        .btn-review {
            background: #e0f2fe; 
            color: #0369a1; 
            padding: 6px 14px; 
            border-radius: 10px; 
            text-decoration: none; 
            font-size: 0.85rem; 
            font-weight: 600; 
            border: 1px solid #bae6fd;
            transition: background 0.2s;
        }
        .btn-review:hover {
            background: #bae6fd;
        }
    </style>
</head>
<body>

    <!-- THANH MENU -->
    <nav class="navbar">
        <a href="/BabyBoss-spa/index.php" class="logo">🐾 BabyBoss Spa</a>
        <ul class="nav-links">
            <li><a href="/BabyBoss-spa/index.php">Trang chủ</a></li>
            <li><a href="about.php">Về chúng tôi</a></li>
            <li><a href="services.php">Dịch vụ</a></li>
            <li><a href="pets.php">Thú cưng của tôi</a></li>
            <li><a href="orders.php" style="color: #0284c7; font-weight: bold;">Đơn hàng của tôi</a></li>

            <?php if (isset($_SESSION['customer_id'])): ?>
                <?php $shortName = explode(' ', trim($_SESSION['customer_name'])); ?>
                <li><span style="font-weight: 600; color: #0369a1;">👤 Chào, <?= htmlspecialchars(end($shortName)) ?></span></li>
                <li><a href="logout.php" style="background: #fee2e2; color: #991b1b; padding: 6px 14px; border-radius: 20px; text-decoration: none;">Đăng xuất</a></li>
            <?php else: ?>
                <li><a href="login.php" style="color: #0284c7; font-weight: 600;">Đăng nhập</a></li>
                <li><a href="register.php" style="background: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 20px; text-decoration: none; font-weight: 600;">Đăng ký</a></li>
            <?php endif; ?>
        </ul>
        <a href="services.php#booking-section" class="btn-nav">Đặt lịch ngay</a>
    </nav>

    <!-- TIÊU ĐỀ TRANG -->
    <div class="page-header">
        <h1>Lịch Sử Đơn Hàng & Lịch Hẹn</h1>
        <p>Kiểm tra chi tiết thông tin các dịch vụ bạn đã đặt lịch tại BabyBoss Spa</p>
    </div>

    <!-- DANH SÁCH ĐƠN HÀNG -->
    <div class="orders-container">
        <?php if (empty($orders)): ?>
            <div style="background: #fff; padding: 40px; border-radius: 20px; text-align: center; border: 2px solid #e0f2fe;">
                <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 20px;">Bạn chưa có lịch hẹn dịch vụ nào.</p>
                <a href="services.php" style="background: #0284c7; color: #fff; padding: 12px 25px; border-radius: 10px; font-weight: 700; text-decoration: none;">Đặt lịch dịch vụ ngay</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-info">
                        <h3><?= htmlspecialchars($order['service_name']) ?></h3>
                        <p>👤 <strong>Sen đặt:</strong> <?= htmlspecialchars($order['sen_name']) ?> (SĐT: <?= htmlspecialchars($order['sen_phone']) ?>)</p>
                        <p>⏰ <strong>Thời gian hẹn:</strong> <?= date('H:i - d/m/Y', strtotime($order['booking_time'])) ?></p>
                        <p>📅 <strong>Ngày tạo đơn:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                        
                        <div style="margin-top: 12px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span class="order-status"><?= htmlspecialchars($order['status']) ?></span>
                            <a href="add_review.php?service_id=<?= $order['service_id'] ?>" class="btn-review">⭐ Đánh giá dịch vụ</a>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div class="price-tag"><?= number_format($order['service_price'], 0, ',', '.') ?> đ</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <h4>🐾 BabyBoss Pet Spa</h4>
                <p>Hệ thống chăm sóc, làm đẹp và khách sạn thú cưng uy tín hàng đầu.</p>
            </div>
            <div class="footer-col">
                <h4>Liên Kết Nhanh</h4>
                <a href="/BabyBoss-spa/index.php">Trang chủ</a>
                <a href="about.php">Giới thiệu</a>
                <a href="services.php">Bảng giá dịch vụ</a>
                <a href="pets.php">Thú cưng của tôi</a>
                <a href="orders.php">Đơn hàng của tôi</a>
            </div>
            <div class="footer-col">
                <h4>Thông Tin Liên Hệ</h4>
                <p>📍 Địa chỉ: 79 Hồ Tùng Mậu, Cầu Giấy, Hà Nội</p>
                <p>📞 Hotline: 0988.888.999</p>
                <p>⏰ Giờ mở cửa: 08:30 - 21:00 hàng ngày</p>
            </div>
        </div>
        <div class="copyright">
            © 2026 BabyBoss Pet Spa & Care. All rights reserved.
        </div>
    </footer>
<!-- NÚT LIÊN HỆ NHANH (HOTLINE & MESSAGE) -->
<!-- ========================================================== -->
<!-- KHUNG CHAT TRỰC TIẾP & HOTLINE NỔI GÓC DƯỚI BÊN PHẢI          -->
<!-- ========================================================== -->
<div class="floating-contact">
    <!-- Hộp chat thu nhỏ (Mặc định ẩn, bấm icon 💬 để bật/tắt) -->
    <div class="chat-box-popup" id="chatPopup">
        <div class="chat-header">
            <span>🐾 Trợ lý AI & Hỗ trợ BabyBoss</span>
            <button onclick="toggleChat()">✕</button>
        </div>
        <div class="chat-body" id="chatBody">
            <div class="chat-message ai">Xin chào bạn! Mình là trợ lý ảo của BabyBoss Spa. Mình có thể giúp gì cho bạn hôm nay? (Nếu cần hỗ trợ đặc biệt, Admin sẽ tiếp quản đoạn chat này sau nhé).</div>
        </div>
        <div class="chat-footer">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn..." onkeypress="handleKeyPress(event)">
            <button onclick="sendMessage()">Gửi</button>
        </div>
    </div>

    <!-- Nút bấm mở hộp chat 💬 và Nút gọi Hotline 📞 -->
    <button class="float-btn btn-message" onclick="toggleChat()" title="Mở hộp chat hỗ trợ">💬</button>
    <a href="tel:0988888999" class="float-btn btn-hotline" title="Gọi Hotline: 0988.888.999">📞</a>
</div>

<script>
// Hàm ẩn/hiện khung chat
function toggleChat() {
    const popup = document.getElementById('chatPopup');
    popup.style.display = (popup.style.display === 'flex') ? 'none' : 'flex';
}

// Hàm gửi tin nhắn lên server PHP và nhận phản hồi từ AI
function sendMessage() {
    const input = document.getElementById('chatInput');
    const chatBody = document.getElementById('chatBody');
    const text = input.value.trim();
    
    if (!text) return;

    // 1. Hiển thị ngay tin nhắn của khách lên giao diện chat
    chatBody.innerHTML += `<div class="chat-message customer">${escapeHtml(text)}</div>`;
    input.value = '';
    chatBody.scrollTop = chatBody.scrollHeight;

    // 2. Gửi ngầm dữ liệu tới file send_message.php bằng Fetch API
    let formData = new FormData();
    formData.append('message', text);

    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // 3. Sau 0.5 giây, hiển thị câu trả lời tự động của AI cho mượt mà
            setTimeout(() => {
                chatBody.innerHTML += `<div class="chat-message ai">${escapeHtml(data.ai_reply)}</div>`;
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 500);
        }
    })
    .catch(err => console.error('Lỗi kết nối chat:', err));
}

// Bắt sự kiện bấm phím Enter để gửi nhanh
function handleKeyPress(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
}

// Bảo mật chống lỗi hiển thị HTML ký tự đặc biệt
function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
</script>
</body>
</html>