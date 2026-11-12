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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password_input = $_POST['password'];

    if (empty($email) || empty($password_input)) {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu!';
    } else {
        // Truy vấn tìm kiếm khách hàng theo email
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Trường hợp email không tồn tại trong hệ thống
        if (!$user) {
            $error = 'Khách hàng chưa đăng ký tài khoản! <a href="register.php" style="color: #0284c7; font-weight: bold; text-decoration: underline;">Đăng ký ngay</a>';
        } else {
            // Trường hợp có email, tiến hành kiểm tra mật khẩu đã mã hóa
            if (password_verify($password_input, $user['password'])) {
                // Lưu trạng thái đăng nhập vào SESSION
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer_name'] = $user['fullname'];
                $_SESSION['customer_email'] = $user['email'];

                // Chuyển hướng về trang chủ
                header('Location: /BabyBoss-spa/index.php');
                exit();
            } else {
                $error = 'Mật khẩu không chính xác!';
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
    <title>Đăng nhập tài khoản - BabyBoss Spa</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/BabyBoss-spa/public/css/style.css">
    <style>
        .auth-container { max-width: 450px; margin: 60px auto; background: #fff; padding: 35px; border-radius: 20px; box-shadow: 0 10px 25px rgba(2,132,199,0.08); border: 2px solid #e0f2fe; }
        .auth-container h2 { color: #0369a1; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b; }
        .form-group input { width: 100%; padding: 12px 15px; border: 2px solid #e0f2fe; border-radius: 12px; outline: none; font-family: inherit; }
        .form-group input:focus { border-color: #0284c7; }
        .btn-submit { width: 100%; background: #0284c7; color: #fff; padding: 14px; border: none; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; }
        .btn-submit:hover { background: #0369a1; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #fecaca; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 0.95rem; color: #64748b; }
        .auth-footer a { color: #0284c7; text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>Đăng Nhập Tài Khoản</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Nhập email của bạn" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <button type="submit" class="btn-submit">Đăng Nhập</button>
        </form>
        
        <div class="auth-footer">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
    </div>
</body>
</html>