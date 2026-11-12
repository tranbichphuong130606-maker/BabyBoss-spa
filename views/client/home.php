<?php
// Khởi động session để quản lý trạng thái đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối CSDL bằng PDO để lấy danh sách dịch vụ từ bảng 'services'
$host = '127.0.0.1';
$dbname = 'babyboss';
$username = 'root';
$password = '';

$services = [];
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT * FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Nếu chưa có bảng hoặc lỗi nhẹ thì dùng mảng dữ liệu dự phòng để web không bị sập giao diện
    $services = [
        ['name' => 'Tắm dưỡng thú cưng trọn gói', 'price' => 150000, 'description' => 'Tắm sấy, cắt tỉa móng và vệ sinh tai chuyên sâu.'],
        ['name' => 'Massage & Thư giãn chó mèo', 'price' => 200000, 'description' => 'Liệu pháp tinh dầu hữu cơ giúp thú cưng giảm stress.']
    ];
}

// Mảng hình ảnh thú cưng cute để gán tự động cho từng thẻ dịch vụ
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
    <title>BabyBoss Pet Spa & Care - Chăm Sóc Thú Cưng Chuyên Nghiệp</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/BabyBoss-spa/public/css/style.css">
</head>
<body>

    <!-- 1. THANH MENU ĐIỀU HƯỚNG (Đã liên kết các trang riêng biệt) -->
   <nav class="navbar">
        <a href="/BabyBoss-spa/index.php" class="logo">🐾 BabyBoss Spa</a>
        <ul class="nav-links">
            <li><a href="/BabyBoss-spa/index.php" style="color: #0284c7; font-weight: bold;">Trang chủ</a></li>
            <li><a href="/BabyBoss-spa/views/client/about.php">Về chúng tôi</a></li>
            <li><a href="/BabyBoss-spa/views/client/services.php">Dịch vụ</a></li>
            <li><a href="/BabyBoss-spa/views/client/pets.php">Thú cưng của tôi</a></li>
            <li><a href="/BabyBoss-spa/views/client/orders.php">Đơn hàng của tôi</a></li>

            <?php if (isset($_SESSION['customer_id'])): ?>
                <li><span style="font-weight: 600; color: #0369a1;">👤 Chào, <?= htmlspecialchars($_SESSION['customer_name']) ?></span></li>
                <li><a href="/BabyBoss-spa/views/client/logout.php" style="background: #fee2e2; color: #991b1b; padding: 6px 14px; border-radius: 20px; text-decoration: none;">Đăng xuất</a></li>
            <?php else: ?>
                <li><a href="/BabyBoss-spa/views/client/login.php" style="color: #0284c7; font-weight: 600;">Đăng nhập</a></li>
                <li><a href="/BabyBoss-spa/views/client/register.php" style="background: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 20px; text-decoration: none; font-weight: 600;">Đăng ký</a></li>
            <?php endif; ?>
        </ul>
        <a href="/BabyBoss-spa/index.php#booking" class="btn-nav">Đặt lịch ngay</a>
    </nav>

    <!-- 2. BANNER CHÍNH -->
    <section class="hero">
        <div class="hero-text">
            <span class="badge">✨ Chăm sóc thú cưng chuẩn 5 sao</span>
            <h1>Yêu Thương & Làm Đẹp Cho Boss Của Bạn</h1>
            <p>Không gian thư giãn xanh mát, đội ngũ chuyên viên tận tâm cùng các dòng mỹ phẩm hữu cơ an toàn tuyệt đối cho làn da của chó mèo.</p>
            <div class="hero-buttons">
                <a href="#booking" class="btn-primary">Đặt lịch hẹn ngay</a>
                <a href="/BabyBoss-spa/views/client/services.php" class="btn-outline">Xem bảng giá</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=600" alt="Thú cưng dễ thương tại BabyBoss Spa">
        </div>
    </section>

    <!-- 3. ĐIỂM NỔI BẬT -->
    <section class="section">
        <div class="section-title">
            <h2>Tại Sao Chọn BabyBoss Pet Spa?</h2>
            <p>Chúng tôi nâng niu thú cưng của bạn như chính thành viên trong gia đình</p>
        </div>
        <div class="features-grid">
            <div class="feature-box">
                <div class="feature-icon">🛁</div>
                <h3>Sữa Tắm Hữu Cơ</h3>
                <p>100% chiết xuất thiên nhiên, không gây kích ứng da và giữ mùi thơm dịu nhẹ suốt cả tuần.</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">✂️</div>
                <h3>Groomer Tay Nghề Cao</h3>
                <p>Đội ngũ tạo kiểu được đào tạo bài bản, nhẹ nhàng và thấu hiểu tâm lý từng bé.</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">🏥</div>
                <h3>Khử Khuẩn Tuyệt Đối</h3>
                <p>Dụng cụ và lồng sấy luôn được tiệt trùng bằng tia UV sau mỗi lượt khách hàng.</p>
            </div>
        </div>
    </section>

    <!-- 4. DANH SÁCH DỊCH VỤ NỔI BẬT TRÊN TRANG CHỦ -->
    <section class="section">
        <div class="section-title">
            <h2>Dịch Vụ Nổi Bật Tại Spa</h2>
            <p>Lựa chọn gói chăm sóc phù hợp nhất cho người bạn nhỏ của bạn</p>
        </div>
        <div class="services-grid">
            <?php foreach (array_slice($services, 0, 3) as $index => $service): ?>
                <?php $imgUrl = $petImages[$index % count($petImages)]; ?>
                <div class="service-card">
                    <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="service-img">
                    <div class="service-body">
                        <div>
                            <h3><?= htmlspecialchars($service['name']) ?></h3>
                            <p><?= htmlspecialchars($service['description']) ?></p>
                        </div>
                        <div class="service-footer">
                            <span class="price"><?= number_format($service['price'], 0, ',', '.') ?> đ</span>
                            <a href="#booking" class="btn-book">Đặt lịch</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Nút xem tất cả dịch vụ dẫn sang trang services.php -->
        <div style="text-align: center; margin-top: 40px;">
            <a href="/BabyBoss-spa/views/client/services.php" style="display: inline-block; background: #e0f2fe; color: #0369a1; padding: 12px 25px; border-radius: 30px; font-weight: 700; text-decoration: none; transition: background 0.3s;">Xem tất cả dịch vụ &rarr;</a>
        </div>
    </section>

    <!-- 5. KHUNG ĐẶT LỊCH NHANH -->
    <section id="booking" class="section">
        <div class="booking-banner">
            <div class="booking-info">
                <h2>Đặt Lịch Hẹn Cho Bé Ngay Hôm Nay! 🐶🐱</h2>
                <p>Để lại thông tin bên cạnh, nhân viên tư vấn của BabyBoss sẽ gọi điện xác nhận khung giờ phục vụ tốt nhất cho bạn.</p>
            </div>
            <form class="booking-form" onsubmit="alert('Cảm ơn bạn! Yêu cầu đặt lịch đã được ghi nhận.'); return false;">
                <input type="text" placeholder="Tên của bạn (Sen)" required>
                <input type="tel" placeholder="Số điện thoại liên hệ" required>
                <select required>
                    <option value="">-- Chọn dịch vụ quan tâm --</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id'] ?? '' ?>"><?= htmlspecialchars($service['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Xác Nhận Đặt Lịch</button>
            </form>
        </div>
    </section>

    <!-- 6. CHÂN TRANG (FOOTER) -->
    <footer id="contact">
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