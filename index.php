<?php
// Ket noi database
include 'db_connect.php';

// Truy van du lieu
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head><title>PHP Fullstack Demo</title></head>
<body>
    <h1>Danh sach User tu MySQL (Shared Hosting)</h1>
    <ul>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<li>" . $row["name"] . " - Email: " . $row["email"] . "</li>";
            }
        } else {
            echo "Chua co du lieu";
        }
        ?>
    </ul>
</body>
</html>