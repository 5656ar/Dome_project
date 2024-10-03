<?php
include 'connect.php';
session_start(); 

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit();
// }

// ดึงข้อมูลห้องพักจากฐานข้อมูล
$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Room List</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Room List</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Room Number</th>
                <th>Room Type</th>
                <th>Price (฿)</th>
                <th>Furniture</th>
                <th>Status</th>
                <th>Image</th>
                <th>Details</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            // แสดงข้อมูลห้องแต่ละห้อง
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['room_number'] . "</td>";
                echo "<td>" . $row['room_type'] . "</td>";
                echo "<td>" . number_format($row['price'], 2) . "</td>";
                echo "<td>" . $row['furniture'] . "</td>";
                echo "<td>" . ucfirst($row['status']) . "</td>";
                echo "<td><img src='" . $row['image_url'] . "' alt='Room Image' style='width:100px;height:auto;'></td>";
                echo "<td>" . $row['details'] . "</td>";
                echo "<td><a href='edit_room.php?id=" . $row['id'] . "' class='btn btn-warning'>Edit</a></td>"; // ปุ่ม Edit
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No rooms found</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
$conn->close();
?>
