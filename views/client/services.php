<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối CSDL lấy danh sách dịch vụ và thông tin khách hàng nếu đã đăng nhập
$host = '127.0.0.1';
$dbname = 'babyboss';
$username = 'root';
$password = '';

$services = [];
$customerInfo = ['fullname' => '', 'phone' => ''];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lấy danh sách dịch vụ
  $stmt = $pdo->query("
    SELECT s.*, 
           COALESCE(AVG(r.rating), 5.0) AS avg_rating, 
           COUNT(r.id) AS total_reviews 
    FROM services s 
    LEFT JOIN reviews r ON s.id = r.service_id 
    GROUP BY s.id
");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nếu khách hàng đã đăng nhập, lấy thêm số điện thoại từ CSDL
    if (isset($_SESSION['customer_id'])) {
        $c_stmt = $pdo->prepare("SELECT fullname, phone FROM customers WHERE id = ?");
        $c_stmt->execute([$_SESSION['customer_id']]);
        $fetchedUser = $c_stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetchedUser) {
            $customerInfo['fullname'] = $fetchedUser['fullname'];
            $customerInfo['phone'] = $fetchedUser['phone'] ?? '';
        }
    }
} catch (PDOException $e) {
    $services = [
        ['id' => 1, 'name' => 'Tắm dưỡng thú cưng trọn gói', 'price' => 150000, 'description' => 'Tắm sấy, cắt tỉa móng và vệ sinh tai chuyên sâu.'],
        ['id' => 2, 'name' => 'Massage & Thư giãn chó mèo', 'price' => 200000, 'description' => 'Liệu pháp tinh dầu hữu cơ giúp thú cưng giảm stress.']
    ];
}

// Xử lý khi khách hàng bấm nút xác nhận đặt lịch trên trang dịch vụ
$bookingSuccess = '';
$bookingError = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_booking'])) {
    $sen_name = trim($_POST['sen_name']);
    $sen_phone = trim($_POST['sen_phone']);
    $service_id = $_POST['service_id'];
    $booking_time = $_POST['booking_time'];
    $customer_id = $_SESSION['customer_id'] ?? null;

    if (empty($sen_name) || empty($sen_phone) || empty($service_id) || empty($booking_time)) {
        $bookingError = 'Vui lòng điền đầy đủ thông tin đặt lịch!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, sen_name, sen_phone, service_id, booking_time, status) VALUES (?, ?, ?, ?, ?, 'Chờ xác nhận')");
            $stmt->execute([$customer_id, $sen_name, $sen_phone, $service_id, $booking_time]);
            $bookingSuccess = '🎉 Cảm ơn <strong>' . htmlspecialchars($sen_name) . '</strong>! Lịch hẹn của bạn đã được ghi nhận thành công. Bạn có thể xem lại chi tiết tại mục <a href="orders.php" style="color: #166534; font-weight: bold; text-decoration: underline;">Đơn hàng của tôi</a>.';
        } catch (PDOException $e) {
            $bookingError = 'Lỗi lưu lịch hẹn: ' . $e->getMessage();
        }
    }
}
$petImages = [
    'https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?w=500',
    'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=500',
    'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=500',
    'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=500'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dịch Vụ & Đặt Lịch - BabyBoss Pet Spa & Care</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/BabyBoss-spa/public/css/style.css">
    <style>
        .page-header { background: linear-gradient(120deg, #e0f2fe 0%, #bae6fd 100%); padding: 50px 5%; text-align: center; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 40px; }
        .page-header h1 { color: #0369a1; font-size: 2.5rem; margin-bottom: 10px; }
        
        /* Bổ sung style cho form đặt lịch tích hợp */
        .booking-section { max-width: 1000px; margin: 40px auto 60px; padding: 0 20px; }
        .booking-banner { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 20px; padding: 40px; color: #fff; display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px; align-items: center; box-shadow: 0 10px 25px rgba(2,132,199,0.2); }
        .booking-info h2 { font-size: 2rem; margin-bottom: 15px; color: #fff; }
        .booking-info p { color: #e0f2fe; line-height: 1.6; }
        .booking-form { background: #fff; padding: 30px; border-radius: 15px; display: flex; flex-direction: column; gap: 15px; }
        .booking-form input, .booking-form select { width: 100%; padding: 12px 15px; border: 2px solid #e0f2fe; border-radius: 10px; outline: none; font-family: inherit; }
        .booking-form input:focus, .booking-form select:focus { border-color: #0284c7; }
        .booking-form button { background: #0284c7; color: #fff; padding: 14px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 1rem; }
        .booking-form button:hover { background: #0369a1; }
        .alert-success-booking { background: #dcfce7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; border: 1px solid #bbf7d0; text-align: center; }
        @media(max-width: 768px) { .booking-banner { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <!-- THANH MENU -->
    <nav class="navbar">
        <a href="/BabyBoss-spa/index.php" class="logo">🐾 BabyBoss Spa</a>
        <ul class="nav-links">
            <li><a href="/BabyBoss-spa/index.php">Trang chủ</a></li>
            <li><a href="/BabyBoss-spa/views/client/about.php">Về chúng tôi</a></li>
            <li><a href="/BabyBoss-spa/views/client/services.php" style="color: #0284c7; font-weight: bold;">Dịch vụ</a></li>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <li><a href="/BabyBoss-spa/views/client/pets.php">Thú cưng của tôi</a></li>
            <?php endif; ?>
                        <li><a href="/BabyBoss-spa/views/client/orders.php">Đơn hàng của tôi</a></li>


            <?php if (isset($_SESSION['customer_id'])): ?>
                <li><span style="font-weight: 600; color: #0369a1;">👤 Chào, <?= htmlspecialchars($_SESSION['customer_name']) ?></span></li>
                <li><a href="/BabyBoss-spa/views/client/logout.php" style="background: #fee2e2; color: #991b1b; padding: 6px 14px; border-radius: 20px; text-decoration: none;">Đăng xuất</a></li>
            <?php else: ?>
                <li><a href="/BabyBoss-spa/views/client/login.php" style="color: #0284c7; font-weight: 600;">Đăng nhập</a></li>
                <li><a href="/BabyBoss-spa/views/client/register.php" style="background: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 20px; text-decoration: none; font-weight: 600;">Đăng ký</a></li>
            <?php endif; ?>

        </ul>
        <a href="#booking-section" class="btn-nav">Đặt lịch ngay</a>
    </nav>

    <!-- TIÊU ĐỀ TRANG -->
    <div class="page-header">
        <h1>Bảng Giá Dịch Vụ & Đặt Lịch Chăm Sóc</h1>
        <p>Lựa chọn dịch vụ 5 sao và đặt lịch hẹn nhanh chóng cho thú cưng của bạn</p>
    </div>

    <!-- DANH SÁCH DỊCH VỤ VÀ GIÁ TIỀN -->
    <section class="section" style="max-width:1200px; margin:0 auto; padding:0 20px 30px;">
        <div class="services-grid">
            <?php foreach ($services as $index => $service): ?>
                <?php $imgUrl = $petImages[$index % count($petImages)]; ?>
                <div class="service-card">
                    <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="service-img">
                    <div class="service-body">
                        <div>
                            <h3><?= htmlspecialchars($service['name']) ?></h3>
                            <div style="color: #f59e0b; font-weight: 600; font-size: 0.9rem; margin: 5px 0;">
            ⭐ <?= number_format($service['avg_rating'], 1) ?> / 5 (<?= $service['total_reviews'] ?> đánh giá)
        </div>
                            <p><?= htmlspecialchars($service['description']) ?></p>
                        </div>
                        <div class="service-footer">
                            <span class="price"><?= number_format($service['price'], 0, ',', '.') ?> đ</span>
                            <a href="#booking-section" class="btn-book">Chọn dịch vụ này</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- KHUNG ĐẶT LỊCH NẰM TRỰC TIẾP TRONG TRANG DỊCH VỤ -->
    <div id="booking-section" class="booking-section">
        <?php if (!empty($bookingError)): ?>
    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; text-align: center; max-width: 1000px; margin: 20px auto;">
        <?= $bookingError ?>
    </div>
<?php endif; ?>

        <div class="booking-banner">
            <div class="booking-info">
                <h2>Đặt Lịch Hẹn Cho Bé Ngay! 🐶🐱</h2>
                <p>Điền thông tin Sen, số điện thoại, chọn dịch vụ và thời gian mong muốn. Đội ngũ BabyBoss sẽ sắp xếp và gọi điện xác nhận lịch hẹn chuẩn xác cho bạn.</p>
            </div>
            <form class="booking-form" action="" method="POST">
                <input type="hidden" name="action_booking" value="1">
                
                <div style="font-weight: 600; color: #0369a1; margin-bottom: -5px;">Thông tin liên hệ (Sen):</div>
                <input type="text" name="sen_name" value="<?= htmlspecialchars($_SESSION['customer_name'] ?? $customerInfo['fullname']) ?>" placeholder="Tên của bạn" required>
                <input type="tel" name="sen_phone" value="<?= htmlspecialchars($customerInfo['phone']) ?>" placeholder="Số điện thoại liên hệ" required>
                
                <div style="font-weight: 600; color: #0369a1; margin-bottom: -5px; margin-top: 5px;">Chi tiết lịch hẹn:</div>
                <select name="service_id" required>
                    <option value="">-- Chọn dịch vụ muốn đặt --</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?> (<?= number_format($service['price'], 0, ',', '.') ?> đ)</option>
                    <?php endforeach; ?>
                </select>
                
                <input type="datetime-local" name="booking_time" required>
                
                <button type="submit">Xác Nhận Đặt Lịch Ngay</button>
            </form>
        </div>
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
                <a href="/BabyBoss-spa/views/client/about.php">Giới thiệu</a>
                <a href="/BabyBoss-spa/views/client/services.php">Bảng giá dịch vụ</a>
                <a href="/BabyBoss-spa/views/client/pets.php">Thú cưng của tôi</a>
<li><a href="/BabyBoss-spa/views/client/orders.php">Đơn hàng của tôi</a></li>
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