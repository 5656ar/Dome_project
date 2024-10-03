<?php
include 'connect.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

// เริ่มต้นเซสชัน
session_start();

// ดึงข้อมูลผู้ใช้ทั้งหมดจากตาราง registration
$query = "SELECT * FROM registration";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// เริ่มสร้าง HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users</title>
    <link rel="stylesheet" href="styles.css"> <!-- ไฟล์ CSS ที่เลือกใช้ -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<h1>Registered Users</h1>

<table>
    <thead>
        <tr>
            <th>ID</th> <!-- คอลัมน์ ID -->
            <th>First Name</th>
            <th>Last Name</th>
            <th>Gender</th>
            <th>Email</th>
            <th>Number</th>
            <th>Password</th> <!-- เพิ่มคอลัมน์ Password -->
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // วนลูปเพื่อแสดงผลผู้ใช้แต่ละคน
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>"; // แสดง ID
            echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['password']) . "</td>"; // แสดง Password
            echo "<td><a href='edit_user.php?id=" . $row['id'] . "'>Edit</a></td>"; // ลิงก์ไปยังหน้าฟอร์มแก้ไข
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>

</body>
</html>
