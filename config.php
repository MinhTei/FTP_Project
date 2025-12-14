<?php
// Thông tin database
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'todo_app';

// Tạo kết nối
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối database thất bại: " . $conn->connect_error);
}

// Thiết lập charset
$conn->set_charset("utf8mb4");

// Bắt đầu session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hàm kiểm tra đăng nhập
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Hàm chuyển hướng nếu chưa đăng nhập
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Hàm lấy thông tin user hiện tại
function get_logged_in_user() {
    global $conn;
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return null;
    }
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}
?>
