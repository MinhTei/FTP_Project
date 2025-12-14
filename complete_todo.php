<?php
require_once 'config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $todo_id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra todo thuộc về user hiện tại
    $check_stmt = $conn->prepare("SELECT id FROM todos WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $todo_id, $user_id);
    $check_stmt->execute();
    $check = $check_stmt->get_result();
    
    if ($check->num_rows === 1) {
        $stmt = $conn->prepare("UPDATE todos SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $todo_id);
        
        if (!$stmt->execute()) {
            $_SESSION['error'] = 'Lỗi: ' . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}

header("Location: dashboard.php");
exit();
?>
