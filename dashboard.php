<?php
require_once 'config.php';
require_login();

$user = get_current_user();
if (!$user) {
    // Database chưa được tạo hoặc kết nối lỗi
    die("Lỗi: Database chưa được cấu hình. Vui lòng tạo database 'todo_app' từ file database.sql");
}
$user_id = $_SESSION['user_id'];

// Lấy danh sách todos
$todos = [];
$stmt = $conn->prepare("SELECT * FROM todos WHERE user_id = ? ORDER BY due_date ASC, created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $todos[] = $row;
    }
}

// Thống kê
$pending_stmt = $conn->prepare("SELECT COUNT(*) as count FROM todos WHERE user_id = ? AND status = 'pending'");
$pending_stmt->bind_param("i", $user_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result()->fetch_assoc();
$pending = $pending_result['count'] ?? 0;
$pending_stmt->close();

$completed_stmt = $conn->prepare("SELECT COUNT(*) as count FROM todos WHERE user_id = ? AND status = 'completed'");
$completed_stmt->bind_param("i", $user_id);
$completed_stmt->execute();
$completed_result = $completed_stmt->get_result()->fetch_assoc();
$completed = $completed_result['count'] ?? 0;
$completed_stmt->close();
$total = count($todos);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quản lý Todo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>📋 Quản lý Todo cá nhân</h1>
                <div class="user-info">
                    <span>👤 <?php echo htmlspecialchars(is_array($user) && isset($user['username']) ? $user['username'] : 'User'); ?></span>
                    <a href="logout.php" class="btn btn-logout">Đăng xuất</a>
                </div>
            </div>
        </header>
        
        <div class="student-info">
            <p><strong>Sinh viên:</strong> DH52201380 - Bùi Minh Tài</p>
        </div>
        
        <div class="stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo $total; ?></span>
                <span class="stat-label">Tổng công việc</span>
            </div>
            <div class="stat-item">
                <span class="stat-number pending"><?php echo $pending; ?></span>
                <span class="stat-label">Chưa hoàn thành</span>
            </div>
            <div class="stat-item">
                <span class="stat-number completed"><?php echo $completed; ?></span>
                <span class="stat-label">Đã hoàn thành</span>
            </div>
        </div>
        
        <div class="main-content">
            <div class="add-todo-section">
                <h2>➕ Thêm công việc mới</h2>
                <form action="add_todo.php" method="POST" class="add-todo-form">
                    <input type="text" name="title" placeholder="Tên công việc..." required>
                    <textarea name="description" placeholder="Mô tả chi tiết (không bắt buộc)"></textarea>
                    <input type="datetime-local" name="due_date">
                    <select name="priority">
                        <option value="low">Ưu tiên thấp</option>
                        <option value="medium" selected>Ưu tiên trung bình</option>
                        <option value="high">Ưu tiên cao</option>
                    </select>
                    <button type="submit" class="btn">Thêm công việc</button>
                </form>
            </div>
            
            <div class="todos-section">
                <h2>📝 Danh sách công việc</h2>
                
                <?php if (empty($todos)): ?>
                    <div class="no-todos">
                        <p>Bạn chưa có công việc nào. Hãy thêm một công việc mới!</p>
                    </div>
                <?php else: ?>
                    <div class="todos-list">
                        <?php foreach ($todos as $todo): ?>
                            <div class="todo-item <?php echo $todo['status']; ?> priority-<?php echo $todo['priority']; ?>">
                                <div class="todo-content">
                                    <div class="todo-header">
                                        <input type="checkbox" class="todo-checkbox" 
                                            onchange="completeTodo(<?php echo $todo['id']; ?>, this.checked)"
                                            <?php echo $todo['status'] === 'completed' ? 'checked' : ''; ?>>
                                        <h3><?php echo htmlspecialchars($todo['title']); ?></h3>
                                        <span class="priority-badge <?php echo $todo['priority']; ?>">
                                            <?php
                                                $priority_text = [
                                                    'low' => 'Thấp',
                                                    'medium' => 'Trung bình',
                                                    'high' => 'Cao'
                                                ];
                                                echo $priority_text[$todo['priority']];
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($todo['description'])): ?>
                                        <p class="todo-description"><?php echo htmlspecialchars($todo['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="todo-footer">
                                        <?php if (!empty($todo['due_date'])): ?>
                                            <span class="due-date">
                                                📅 <?php echo date('d/m/Y H:i', strtotime($todo['due_date'])); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="created-date">
                                            Tạo: <?php echo date('d/m/Y', strtotime($todo['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="todo-actions">
                                    <a href="edit_todo.php?id=<?php echo $todo['id']; ?>" class="btn btn-edit">Sửa</a>
                                    <button onclick="deleteTodo(<?php echo $todo['id']; ?>)" class="btn btn-delete">Xóa</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function completeTodo(id, completed) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', completed ? 'completed' : 'pending');
            
            fetch('complete_todo.php', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (response.ok) {
                    location.reload();
                }
            });
        }
        
        function deleteTodo(id) {
            if (confirm('Bạn có chắc chắn muốn xóa công việc này?')) {
                window.location.href = 'delete_todo.php?id=' + id;
            }
        }
    </script>
</body>
</html>
