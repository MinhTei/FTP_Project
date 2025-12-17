<?php
require_once 'config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    
    if (empty($title)) {
        $_SESSION['error'] = 'Vui lòng nhập tên công việc';
    } else {
        // Thêm todo
        $stmt = $conn->prepare("INSERT INTO todos (user_id, title, description, due_date, priority) 
                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $title, $description, $due_date, $priority);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Thêm công việc thành công!';
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Lỗi: ' . $stmt->error;
            $stmt->close();
        }
    }
}

header("Location: dashboard.php");
exit();
?>
