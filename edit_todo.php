<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$todo_id = intval($_GET['id'] ?? 0);
$error = '';
$success = '';
$todo = null;

// Lấy todo
$stmt = $conn->prepare("SELECT * FROM todos WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $todo_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: dashboard.php");
    exit();
}

$todo = $result->fetch_assoc();

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    
    if (empty($title)) {
        $error = 'Vui lòng nhập tên công việc';
    } else {
        $stmt = $conn->prepare("UPDATE todos SET title = ?, description = ?, 
                due_date = ?, priority = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $description, $due_date, $priority, $todo_id);
        
        if ($stmt->execute()) {
            $success = 'Cập nhật công việc thành công!';
            // Cập nhật $todo
            $todo['title'] = $title;
            $todo['description'] = $description;
            $todo['due_date'] = $due_date;
            $todo['priority'] = $priority;
            $stmt->close();
        } else {
            $error = 'Lỗi: ' . $stmt->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa công việc - Quản lý Todo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>✏️ Sửa công việc</h1>
                <a href="dashboard.php" class="btn">← Quay lại</a>
            </div>
        </header>
        
        <div class="main-content">
            <div class="edit-todo-section">
                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="edit-todo-form">
                    <div class="form-group">
                        <label>Tên công việc:</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($todo['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Mô tả:</label>
                        <textarea name="description"><?php echo htmlspecialchars($todo['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Hạn chót:</label>
                        <input type="datetime-local" name="due_date" 
                            value="<?php echo $todo['due_date'] ? str_replace(' ', 'T', $todo['due_date']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Ưu tiên:</label>
                        <select name="priority">
                            <option value="low" <?php echo $todo['priority'] === 'low' ? 'selected' : ''; ?>>Ưu tiên thấp</option>
                            <option value="medium" <?php echo $todo['priority'] === 'medium' ? 'selected' : ''; ?>>Ưu tiên trung bình</option>
                            <option value="high" <?php echo $todo['priority'] === 'high' ? 'selected' : ''; ?>>Ưu tiên cao</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Cập nhật công việc</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
