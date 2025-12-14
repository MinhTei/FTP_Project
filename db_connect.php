<?php
$servername = getenv('sql307.infinityfree.com');
$username = getenv('if0_40674741');
$password = getenv('l7LBXzbfrOxm6k');
$dbname = getenv('if0_40674741_organic_db');

// Tao ket noi
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiem tra ket noi
if ($conn->connect_error) {
    die("Ket noi that bai: " . $conn->connect_error);
}
?>