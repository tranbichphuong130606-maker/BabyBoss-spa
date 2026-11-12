<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit();
}

$service_id = $_GET['service_id'] ?? 0;
$customer_id = $_SESSION['customer_id'];

$host = '127.0.0.1';
$dbname = 'babyboss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lấy thông tin dịch vụ
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        header('Location: services.php');
        exit();
    }

    // Kiểm tra xem khách hàng này đã có đơn hàng nào HOÀN THÀNH đối với dịch vụ này chưa
    $check_stmt = $pdo->prepare("
        SELECT * FROM bookings 
        WHERE customer_id = ? AND service_id = ? AND status = 'Hoàn thành'
    ");
    $check_stmt->execute([$customer_id, $service_id]);
    $completedOrder = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$completedOrder) {
        echo "<script>alert('Đơn hàng cần hoàn thành để đánh giá!'); window.location.href='orders.php';</script>";
        exit();
    }

    $error = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);

        if ($rating < 1 || $rating > 5 || empty($comment)) {
            $error = 'Vui lòng chọn số sao từ 1 đến 5 và viết nhận xét!';
        } else {
            $rev_stmt = $pdo->prepare("INSERT INTO reviews (service_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
            $rev_stmt->execute([$service_id, $customer_id, $rating, $comment]);
            header('Location: services.php');
            exit();
        }
    }
} catch (PDOException $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá dịch vụ - BabyBoss Pet Spa</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/BabyBoss-spa/public/css/style.css">
    <style>
        .review-wrapper {
            max-width: 650px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }
        .review-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.06);
            border: 2px solid #e0f2fe;
        }
        .service-tag {
            background: #f0fdf4;
            color: #15803d;
            padding: 10px 15px;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 25px;
            border: 1px solid #bbf7d0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #334155;
        }
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0f2fe;
            border-radius: 12px;
            outline: none;
            font-family: inherit;
            font-size: 0.95rem;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .form-group select:focus, 
        .form-group textarea:focus {
            border-color: #0284c7;
            background: #fff;
        }
        .btn-submit {
            background: #0284c7;
            color: #fff;
            padding: 14px;
            width: 100%;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: #0369a1;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .back-link a:hover {
            color: #0284c7;
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
            <li><a href="orders.php">Đơn hàng của tôi</a></li>

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
        <h1>⭐ Đánh Giá Dịch Vụ</h1>
        <p>Chia sẻ cảm nhận chân thực để giúp BabyBoss phục vụ tốt hơn</p>
    </div>

    <!-- NỘI DUNG FORM ĐÁNH GIÁ -->
    <div class="review-wrapper">
        <div class="review-card">
            <div class="service-tag">
                Dịch vụ đang đánh giá: <strong><?= htmlspecialchars($service['name']) ?></strong>
            </div>
            
            <?php if (!empty($error)): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: 600;"><?= $error ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Mức độ hài lòng (Số sao)</label>
                    <select name="rating" required>
                        <option value="5">⭐⭐⭐⭐⭐ (5/5 - Tuyệt vời)</option>
                        <option value="4">⭐⭐⭐⭐ (4/5 - Rất tốt)</option>
                        <option value="3">⭐⭐⭐ (3/5 - Bình thường)</option>
                        <option value="2">⭐⭐ (2/5 - Tạm được)</option>
                        <option value="1">⭐ (1/5 - Không hài lòng)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nhận xét chi tiết của bạn:</label>
                    <textarea name="comment" rows="4" placeholder="Hãy chia sẻ trải nghiệm dịch vụ của bé tại cửa hàng..." required></textarea>
                </div>
                <button type="submit" class="btn-submit">Gửi Đánh Giá Ngay</button>
                <div class="back-link">
                    <a href="orders.php">← Quay lại danh sách đơn hàng</a>
                </div>
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