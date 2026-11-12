<?php
session_start();
// Hủy bỏ toàn bộ session
session_unset();
session_destroy();

// Quay về trang chủ
header('Location: /BabyBoss-spa/index.php');
exit();
?>