<?php
// Khởi động session để giữ trạng thái đăng nhập trên menu
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Về chúng tôi - BabyBoss Pet Spa & Care</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/BabyBoss-spa/public/css/style.css">
    <style>
        .page-header { background: linear-gradient(120deg, #e0f2fe 0%, #bae6fd 100%); padding: 50px 5%; text-align: center; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 50px; }
        .page-header h1 { color: #0369a1; font-size: 2.5rem; margin-bottom: 10px; }
        .about-content { max-width: 1000px; margin: 0 auto; padding: 0 20px 60px; line-height: 1.8; color: #1e293b; }
        .about-content h3 { color: #0369a1; margin-top: 30px; margin-bottom: 15px; font-size: 1.5rem; }
        .about-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-top: 40px; }
        .about-card { background: #fff; padding: 25px; border-radius: 20px; box-shadow: 0 5px 15px rgba(2,132,199,0.06); border: 2px solid #e0f2fe; text-align: center; }
    </style>
</head>
<body>

    <!-- THANH MENU (GIỮ NGUYÊN ĐỒNG BỘ) -->
    <nav class="navbar">
        <a href="/BabyBoss-spa/index.php" class="logo">🐾 BabyBoss Spa</a>
        <ul class="nav-links">
            <li><a href="/BabyBoss-spa/index.php">Trang chủ</a></li>
            <li><a href="/BabyBoss-spa/views/client/about.php" style="color: #0284c7; font-weight: bold;">Về chúng tôi</a></li>
            <li><a href="/BabyBoss-spa/index.php#services">Dịch vụ</a></li>
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

    <!-- TIÊU ĐỀ TRANG -->
    <div class="page-header">
        <h1>Về BabyBoss Pet Spa & Care</h1>
        <p>Hành trình mang lại sự yêu thương và chăm sóc tốt nhất cho thú cưng của bạn</p>
    </div>

    <!-- NỘI DUNG GIỚI THIỆU -->
    <div class="about-content">
        <p>Được thành lập với tình yêu thương vô bờ bến dành cho các "Boss", <strong>BabyBoss Pet Spa</strong> tự hào là hệ thống chăm sóc và làm đẹp thú cưng hàng đầu. Chúng tôi hiểu rằng chó mèo không chỉ là vật nuôi mà còn là những người bạn tri kỷ, là thành viên quan trọng trong gia đình của bạn.</p>
        
        <h3>Sứ Mệnh & Tầm Nhìn</h3>
        <p>Sứ mệnh của chúng tôi là tạo ra một môi trường thư giãn tuyệt đối, an toàn và chuyên nghiệp. Mỗi dịch vụ tại BabyBoss đều được thiết kế tỉ mỉ để giảm thiểu căng thẳng tối đa cho thú cưng, giúp các bé luôn khỏe mạnh, sạch sẽ và rạng rỡ.</p>

        <div class="about-grid">
            <div class="about-card">
                <div style="font-size: 2.5rem; margin-bottom: 10px;">🏆</div>
                <h4 style="color:#0369a1; margin-bottom: 10px;">Chất Lượng 5 Sao</h4>
                <p style="color: #64748b; font-size: 0.95rem;">Trang thiết bị hiện đại, không gian tiệt trùng bằng tia UV sau mỗi lượt khách.</p>
            </div>
            <div class="about-card">
                <div style="font-size: 2.5rem; margin-bottom: 10px;">🌿</div>
                <h4 style="color:#0369a1; margin-bottom: 10px;">100% Hữu Cơ</h4>
                <p style="color: #64748b; font-size: 0.95rem;">Sử dụng dòng mỹ phẩm sữa tắm cao cấp chiết xuất thiên nhiên, an toàn cho da nhạy cảm.</p>
            </div>
            <div class="about-card">
                <div style="font-size: 2.5rem; margin-bottom: 10px;">❤️</div>
                <h4 style="color:#0369a1; margin-bottom: 10px;">Yêu Thương Tận Tâm</h4>
                <p style="color: #64748b; font-size: 0.95rem;">Đội ngũ Groomer giàu kinh nghiệm, thấu hiểu tâm lý và nâng niu từng bé cưng.</p>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
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
                <a href="/BabyBoss-spa/index.php#services">Bảng giá dịch vụ</a>
                <a href="/BabyBoss-spa/views/client/orders.php">Đơn hàng của tôi</a>
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