<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit();
}

$customer_id = $_SESSION['customer_id'];
$msg = trim($_POST['message'] ?? '');

if (empty($msg)) {
    echo json_encode(['status' => 'error', 'message' => 'Tin nhắn trống']);
    exit();
}

$host = '127.0.0.1';
$dbname = 'babyboss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // 1. Lưu tin nhắn của khách hàng vào CSDL
    $stmt = $pdo->prepare("INSERT INTO messages (customer_id, sender_type, message) VALUES (?, 'customer', ?)");
    $stmt->execute([$customer_id, $msg]);

    // 2. Logic AI tự động trả lời thông minh dựa trên từ khóa hoặc câu hỏi thường gặp
    $ai_reply = "Cảm ơn bạn đã nhắn tin cho BabyBoss Pet Spa! Nhân viên và hệ thống AI đã ghi nhận yêu cầu của bạn và sẽ phản hồi sớm nhất. Bạn có cần hỗ trợ gì về dịch vụ spa hay đặt lịch không ạ?";
    
    $lowerMsg = mb_strtolower($msg);
    if (strpos($lowerMsg, 'giá') !== false || strpos($lowerMsg, 'chi phí') !== false || strpos($lowerMsg, 'bảng giá') !== false) {
        $ai_reply = "Dạ, bạn có thể tham khảo chi tiết bảng giá các gói tắm và cắt tỉa lông cho thú cưng tại mục 'Dịch vụ' trên thanh menu nha!";
    } elseif (strpos($lowerMsg, 'đặt lịch') !== false || strpos($lowerMsg, 'lịch hẹn') !== false) {
        $ai_reply = "Dạ để đặt lịch, bạn chỉ cần vào trang 'Dịch vụ', chọn gói dịch vụ mong muốn và bấm 'Chọn dịch vụ này' ở phía dưới là được ạ!";
    } elseif (strpos($lowerMsg, 'địa chỉ') !== false || strpos($lowerMsg, 'ở đâu') !== false) {
        $ai_reply = "Dạ BabyBoss Pet Spa tọa lạc tại số 79 Hồ Tùng Mậu, Cầu Giấy, Hà Nội. Rất hân hạnh được đón tiếp các bé ạ!";
    }

    // 3. Lưu câu trả lời của AI vào CSDL để hiển thị lên khung chat
    $stmtAI = $pdo->prepare("INSERT INTO messages (customer_id, sender_type, message) VALUES (?, 'ai', ?)");
    $stmtAI->execute([$customer_id, $ai_reply]);

    echo json_encode([
        'status' => 'success', 
        'ai_reply' => $ai_reply
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>