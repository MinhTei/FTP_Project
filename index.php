<?php
require_once 'config.php';

// Nếu đã đăng nhập, chuyển hướng sang dashboard
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
} else {
    // Nếu chưa đăng nhập, chuyển hướng sang login
    header("Location: login.php");
    exit();
}
?>
