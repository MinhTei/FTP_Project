<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Kiểm tra dữ liệu
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } else {
        // Kiểm tra username/email đã tồn tại
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check = $check_stmt->get_result();
        
        if ($check->num_rows > 0) {
            $error = 'Username hoặc email đã tồn tại';
        } else {
            // Hash mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Thêm user vào database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                // Xóa form
                $_POST = [];
                $stmt->close();
            } else {
                $error = 'Lỗi: ' . $stmt->error;
                $stmt->close();
            }
        }
        $check_stmt->close();
    }
}

// Nếu đã đăng nhập, chuyển hướng sang dashboard
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Quản lý Todo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <h1>Đăng ký tài khoản</h1>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
                <p><a href="login.php">Đi tới trang đăng nhập</a></p>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Tên đăng nhập:</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Mật khẩu:</label>
                        <input type="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Xác nhận mật khẩu:</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn">Đăng ký</button>
                </form>
                
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
