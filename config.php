<?php
// ===== CẤU HÌNH DATABASE =====
// Phát hiện môi trường (localhost hoặc hosting)
$is_localhost = ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0);

if ($is_localhost) {
    // Cấu hình cho LOCALHOST (WAMP)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'todo_app');
} else {
    // Cấu hình cho HOSTING (Infinity Free)
    define('DB_HOST', 'sql307.infinityfree.com');
    define('DB_USER', 'if0_40674741');
    define('DB_PASS', 'l7LBXzbfrOxm6k');
    define('DB_NAME', 'if0_40674741_todo_app');
}

// Tạo kết nối
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Kiểm tra kết nối
if ($conn->connect_error) {
    http_response_code(500);
    echo "❌ <strong>Lỗi kết nối database:</strong><br>";
    echo htmlspecialchars($conn->connect_error) . "<br><br>";
    
    if ($is_localhost) {
        echo "<strong>📝 Hướng dẫn cho LOCALHOST:</strong><br>";
        echo "1. Bật WAMP (click icon ở System Tray → Start All Services)<br>";
        echo "2. Mở http://localhost/phpmyadmin<br>";
        echo "3. Click tab 'SQL' → Copy nội dung file database.sql → Execute<br><br>";
        echo "<a href='http://localhost/ftp_project/database.sql'>📄 Xem file database.sql</a>";
    } else {
        echo "<strong>📝 Hướng dẫn cho HOSTING:</strong><br>";
        echo "Kiểm tra thông tin kết nối hosting.";
    }
    
    exit();
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
function get_current_user() {
    global $conn;
    if (!is_logged_in()) {
        return null;
    }
    
    try {
        $user_id = intval($_SESSION['user_id']);
        $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
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
    } catch (Exception $e) {
        error_log("Error in get_current_user: " . $e->getMessage());
        return null;
    }
}
?>
