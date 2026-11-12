<?php
session_start();

// Kiểm tra xem khách hàng đã đăng nhập chưa, nếu chưa bắt buộc chuyển hướng về trang đăng nhập
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

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

$error = '';
$success = '';

// 1. XỬ LÝ THÊM HOẶC SỬA THÚ CƯNG
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = isset($_POST['pet_id']) ? $_POST['pet_id'] : '';
    $name = trim($_POST['name']);
    $species = trim($_POST['species']);
    $weight = trim($_POST['weight']);
    $age = trim($_POST['age']);

    if (empty($name) || empty($species) || empty($weight) || empty($age)) {
        $error = 'Vui lòng điền đầy đủ thông tin thú cưng!';
    } else {
        $avatarName = '';
        $uploadOk = true;

        // Xử lý upload file ảnh avatar nếu có chọn file mới
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                $error = 'Chỉ chấp nhận định dạng file ảnh: .jpg, .jpeg, .png, .webp!';
                $uploadOk = false;
            } else {
                // Đổi tên file bằng uniqid() để tránh trùng lặp
                $avatarName = uniqid('pet_', true) . '.' . $fileExtension;
                $uploadDir = '../../public/uploads/';
                
                // Tự động tạo thư mục uploads nếu chưa tồn tại
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatarName)) {
                    $error = 'Không thể tải lên hình ảnh, vui lòng thử lại.';
                    $uploadOk = false;
                }
            }
        }

        if ($uploadOk) {
            if (empty($pet_id)) {
                // --- THÊM MỚI THÚ CƯNG ---
                $stmt = $pdo->prepare("INSERT INTO pets (customer_id, name, species, weight, age, avatar) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$customer_id, $name, $species, $weight, $age, $avatarName])) {
                    $success = 'Thêm thú cưng thành công!';
                } else {
                    $error = 'Đã có lỗi xảy ra khi thêm thú cưng.';
                }
            } else {
                // --- CẬP NHẬT THÚ CƯNG ---
                if (!empty($avatarName)) {
                    // Nếu có upload ảnh mới, cập nhật cả avatar
                    $stmt = $pdo->prepare("UPDATE pets SET name = ?, species = ?, weight = ?, age = ?, avatar = ? WHERE id = ? AND customer_id = ?");
                    $stmt->execute([$name, $species, $weight, $age, $avatarName, $pet_id, $customer_id]);
                } else {
                    // Nếu không đổi ảnh, giữ nguyên ảnh cũ
                    $stmt = $pdo->prepare("UPDATE pets SET name = ?, species = ?, weight = ?, age = ? WHERE id = ? AND customer_id = ?");
                    $stmt->execute([$name, $species, $weight, $age, $pet_id, $customer_id]);
                }
                $success = 'Cập nhật thông tin thú cưng thành công!';
            }
        }
    }
}

// 2. XỬ LÝ XÓA THÚ CƯNG
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ? AND customer_id = ?");
    if ($stmt->execute([$delete_id, $customer_id])) {
        header('Location: pets.php');
        exit();
    }
}

// 3. LẤY DỮ LIỆU ĐỂ SỬA (NẾU CÓ PARAMETER ?edit=id)
$editPet = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ? AND customer_id = ?");
    $stmt->execute([$edit_id, $customer_id]);
    $editPet = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 4. LẤY DANH SÁCH THÚ CƯNG CỦA KHÁCH HÀNG NÀY
$stmt = $pdo->prepare("SELECT * FROM pets WHERE customer_id = ? ORDER BY id DESC");
$stmt->execute([$customer_id]);
$myPets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thú cưng của tôi - BabyBoss Spa</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/BabyBoss-spa/public/css/style.css">
    <style>
        .page-header { background: linear-gradient(120deg, #e0f2fe 0%, #bae6fd 100%); padding: 40px 5%; text-align: center; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 40px; }
        .page-header h1 { color: #0369a1; font-size: 2.2rem; margin-bottom: 10px; }
        .pets-container { max-width: 1000px; margin: 0 auto 60px; padding: 0 20px; }
        .pet-form-card, .pet-list-card { background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(2,132,199,0.06); border: 2px solid #e0f2fe; margin-bottom: 30px; }
        .pet-form-card h3 { color: #0369a1; margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b; }
        .form-group input { width: 100%; padding: 10px 14px; border: 2px solid #e0f2fe; border-radius: 10px; outline: none; font-family: inherit; }
        .form-group input:focus { border-color: #0284c7; }
        .btn-submit { background: #0284c7; color: #fff; padding: 12px 25px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; }
        .btn-submit:hover { background: #0369a1; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.9rem; }
        .alert-success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.9rem; }
        
        .pet-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .pet-card { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 15px; padding: 20px; display: flex; align-items: center; gap: 20px; position: relative; }
        .pet-avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #0284c7; background: #e0f2fe; }
        .pet-info h4 { color: #0369a1; font-size: 1.2rem; margin-bottom: 5px; }
        .pet-info p { color: #64748b; font-size: 0.9rem; margin-bottom: 3px; }
        .pet-actions { position: absolute; top: 15px; right: 15px; display: flex; gap: 8px; }
        .btn-action { padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; text-decoration: none; font-weight: 600; }
        .btn-edit { background: #e0f2fe; color: #0369a1; }
        .btn-delete { background: #fee2e2; color: #991b1b; }
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
            <li><a href="pets.php" style="color: #0284c7; font-weight: bold;">Thú cưng của tôi</a></li>
            <li><a href="/BabyBoss-spa/views/client/orders.php">Đơn hàng của tôi</a></li>

            <?php if (isset($_SESSION['customer_id'])): ?>
                <li><span style="font-weight: 600; color: #0369a1;">👤 Chào, <?= htmlspecialchars($_SESSION['customer_name']) ?></span></li>
                <li><a href="logout.php" style="background: #fee2e2; color: #991b1b; padding: 6px 14px; border-radius: 20px; text-decoration: none;">Đăng xuất</a></li>
            <?php else: ?>
                <li><a href="login.php" style="color: #0284c7; font-weight: 600;">Đăng nhập</a></li>
                <li><a href="register.php" style="background: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 20px; text-decoration: none; font-weight: 600;">Đăng ký</a></li>
            <?php endif; ?>
        </ul>
        <a href="/BabyBoss-spa/index.php#booking" class="btn-nav">Đặt lịch ngay</a>
    </nav>

    <!-- TIÊU ĐỀ TRANG -->
    <div class="page-header">
        <h1>Quản Lý Thú Cưng Của Bạn</h1>
        <p>Thêm mới và quản lý hồ sơ các bé cưng để thuận tiện khi đặt lịch chăm sóc</p>
    </div>

    <div class="pets-container">

        <!-- THÔNG BÁO LỖI / THÀNH CÔNG -->
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert-success"><?= $success ?></div>
        <?php endif; ?>

        <!-- FORM THÊM / SỬA THÚ CƯNG -->
        <div class="pet-form-card">
            <h3><?= $editPet ? '✏️ Chỉnh Sửa Thông Tin Thú Cưng' : '➕ Thêm Thú Cưng Mới' ?></h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="pet_id" value="<?= $editPet['id'] ?? '' ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tên bé cưng</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($editPet['name'] ?? '') ?>" placeholder="Ví dụ: Miu Miu" required>
                    </div>
                    <div class="form-group">
                        <label>Giống loài</label>
                        <input type="text" name="species" value="<?= htmlspecialchars($editPet['species'] ?? '') ?>" placeholder="Ví dụ: Chó Poodle, Mèo Anh..." required>
                    </div>
                    <div class="form-group">
                        <label>Cân nặng (kg)</label>
                        <input type="number" step="0.1" name="weight" value="<?= htmlspecialchars($editPet['weight'] ?? '') ?>" placeholder="Ví dụ: 3.5" required>
                    </div>
                    <div class="form-group">
                        <label>Tuổi</label>
                        <input type="text" name="age" value="<?= htmlspecialchars($editPet['age'] ?? '') ?>" placeholder="Ví dụ: 1 tuổi" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Ảnh đại diện (Avatar - .jpg, .png, .webp)</label>
                    <input type="file" name="avatar" accept=".jpg, .jpeg, .png, .webp">
                    <?php if (!empty($editPet['avatar'])): ?>
                        <small style="color: #64748b; display: block; margin-top: 5px;">Đang có ảnh cũ: <?= $editPet['avatar'] ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-submit"><?= $editPet ? 'Cập Nhật Thú Cưng' : 'Thêm Thú Cưng' ?></button>
                <?php if ($editPet): ?>
                    <a href="pets.php" style="margin-left: 15px; color: #64748b; text-decoration: none; font-weight: 600;">Hủy bỏ</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- DANH SÁCH THÚ CƯNG ĐÃ ĐĂNG KÝ -->
        <div class="pet-list-card">
            <h3 style="color: #0369a1; margin-bottom: 20px;">🐾 Danh Sách Thú Cưng Của Bạn (<?= count($myPets) ?>)</h3>
            
            <?php if (empty($myPets)): ?>
                <p style="color: #64748b; text-align: center; padding: 20px;">Bạn chưa thêm bé thú cưng nào. Hãy thêm bé ở form bên trên nhé!</p>
            <?php else: ?>
                <div class="pet-grid">
                    <?php foreach ($myPets as $pet): ?>
                        <div class="pet-card">
                            <div class="pet-actions">
                                <a href="pets.php?edit=<?= $pet['id'] ?>" class="btn-action btn-edit">Sửa</a>
                                <a href="pets.php?delete=<?= $pet['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa thú cưng này không?');">Xóa</a>
                            </div>
                            
                            <?php 
                                $avatarPath = !empty($pet['avatar']) ? '/BabyBoss-spa/public/uploads/' . $pet['avatar'] : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=200';
                            ?>
                            <img src="<?= $avatarPath ?>" alt="Avatar" class="pet-avatar">
                            
                            <div class="pet-info">
                                <h4><?= htmlspecialchars($pet['name']) ?></h4>
                                <p><strong>Giống:</strong> <?= htmlspecialchars($pet['species']) ?></p>
                                <p><strong>Cân nặng:</strong> <?= htmlspecialchars($pet['weight']) ?> kg</p>
                                <p><strong>Tuổi:</strong> <?= htmlspecialchars($pet['age']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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