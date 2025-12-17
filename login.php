<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng điền tên đăng nhập và mật khẩu';
    } else {
        // Tìm user
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                $stmt->close();
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
            }
        } else {
            $error = 'Tên đăng nhập không tồn tại';
        }
        $stmt->close();
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
    <title>Đăng nhập - Quản lý Todo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <h1>Đăng nhập</h1>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Tên đăng nhập:</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Mật khẩu:</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Đăng nhập</button>
            </form>
            
            <p>Chưa có tài khoản? <a href="register.php">Đăng ký tại đây</a></p>
        </div>
    </div>
</body>
</html>
