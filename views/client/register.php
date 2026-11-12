<?php
session_start();

// Kết nối CSDL trực tiếp đảm bảo biến $pdo luôn sẵn sàng
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password_input = $_POST['password'];

    if (empty($fullname) || empty($email) || empty($phone) || empty($password_input)) {
        $error = 'Vui lòng điền đầy đủ tất cả các trường!';
    } else {
        // Kiểm tra xem email đã tồn tại trong hệ thống chưa
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email này đã được đăng ký trước đó!';
        } else {
            // Mã hóa mật khẩu bảo mật theo yêu cầu bằng password_hash
            $hashedPassword = password_hash($password_input, PASSWORD_DEFAULT);

            // Lưu thông tin khách hàng vào CSDL
            $insert = $pdo->prepare("INSERT INTO customers (fullname, email, phone, password) VALUES (?, ?, ?, ?)");
            if ($insert->execute([$fullname, $email, $phone, $hashedPassword])) {
                $success = 'Đăng ký thành công! Bạn có thể chuyển sang <a href="login.php" style="color: #166534; font-weight: bold; text-decoration: underline;">đăng nhập ngay</a>.';
            } else {
                $error = 'Đã có lỗi xảy ra, vui lòng thử lại.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - BabyBoss Spa</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/BabyBoss-spa/public/css/style.css">
    <style>
        .auth-container { max-width: 450px; margin: 50px auto; background: #fff; padding: 35px; border-radius: 20px; box-shadow: 0 10px 25px rgba(2,132,199,0.08); border: 2px solid #e0f2fe; }
        .auth-container h2 { color: #0369a1; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b; }
        .form-group input { width: 100%; padding: 12px 15px; border: 2px solid #e0f2fe; border-radius: 12px; outline: none; font-family: inherit; }
        .form-group input:focus { border-color: #0284c7; }
        .btn-submit { width: 100%; background: #0284c7; color: #fff; padding: 14px; border: none; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; }
        .btn-submit:hover { background: #0369a1; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #166534; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #bbf7d0; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 0.95rem; color: #64748b; }
        .auth-footer a { color: #0284c7; text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>Đăng Ký Tài Khoản</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="fullname" placeholder="Nhập họ tên của bạn" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Nhập địa chỉ email" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="tel" name="phone" placeholder="Nhập số điện thoại" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <button type="submit" class="btn-submit">Đăng Ký Ngay</button>
        </form>
        
        <div class="auth-footer">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
        </div>
    </div>
</body>
</html>