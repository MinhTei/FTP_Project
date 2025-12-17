<?php
require_once 'config.php';
require_login();

if (isset($_GET['id'])) {
    $todo_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra todo thuộc về user hiện tại
    $check_stmt = $conn->prepare("SELECT id FROM todos WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $todo_id, $user_id);
    $check_stmt->execute();
    $check = $check_stmt->get_result();
    
    if ($check->num_rows === 1) {
        $stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->bind_param("i", $todo_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Xóa công việc thành công!';
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Lỗi: ' . $stmt->error;
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = 'Công việc không tồn tại';
    }
    $check_stmt->close();
}

header("Location: dashboard.php");
exit();
?>
