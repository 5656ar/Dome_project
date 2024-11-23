<?php
include 'connect.php'; // เชื่อมต่อกับฐานข้อมูล

session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบในฐานะ Admin หรือไม่
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลผลรวมค่าไฟฟ้าและค่าน้ำรายเดือน
$sql = "SELECT month_year, SUM(electricity_bill) AS total_electricity, SUM(water_bill) AS total_water
        FROM utility_bills
        GROUP BY month_year
        ORDER BY month_year ASC";
$result = $conn->query($sql);

// เตรียมข้อมูลสำหรับกราฟ
$months = [];
$totalElectricity = [];
$totalWater = [];

while ($row = $result->fetch_assoc()) {
    $months[] = $row['month_year'];
    $totalElectricity[] = $row['total_electricity'];
    $totalWater[] = $row['total_water'];
}

// ดึงข้อมูลจำนวนห้องที่ว่าง, ห้องที่ถูกเช่า และห้องที่อยู่ในสถานะ pending
$sqlAvailableRooms = "SELECT COUNT(*) AS available_rooms FROM rooms WHERE status = 'available'";
$sqlRentedRooms = "SELECT COUNT(*) AS rented_rooms FROM rooms WHERE status = 'rented'";
$sqlPendingRooms = "SELECT COUNT(*) AS pending_rooms FROM rooms WHERE status = 'pending'";

$resultAvailableRooms = $conn->query($sqlAvailableRooms);
$resultRentedRooms = $conn->query($sqlRentedRooms);
$resultPendingRooms = $conn->query($sqlPendingRooms);

$availableRooms = $resultAvailableRooms->fetch_assoc()['available_rooms'];
$rentedRooms = $resultRentedRooms->fetch_assoc()['rented_rooms'];
$pendingRooms = $resultPendingRooms->fetch_assoc()['pending_rooms'];

// ดึงข้อมูลผู้ใช้ที่เช่าห้อง
$sqlRentedUsers = "SELECT rooms.room_number, registration.id AS user_id, registration.firstName, registration.lastName, registration.email
                   FROM rooms
                   JOIN registration ON rooms.rented_by = registration.id
                   WHERE rooms.status = 'rented'";
$resultRentedUsers = $conn->query($sqlRentedUsers);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Utility Usage Chart</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .sidebar {
            height: 100%;
            background-color: #343a40;
            padding: 20px;
            position: fixed;
        }
        .sidebar h2 {
            color: white;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 240px;
            padding: 20px;
            flex: 1;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .stat-box {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            font-size: 1.2em;
            flex: 1;
            margin: 0 10px;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Admin Menu</h2>
    <a href="index.php">Back To Home Page</a>
    <a href="revenue_chart.php">Dashboard</a>
    <a href="show_users.php">Manage Users</a>
    <a href="show_rooms.php">Manage Rooms</a>
    <a href="confirm_bookings.php">Confirm bookings</a>
    <a href="view_utilities.php">View Utilities</a>
    <a href="set_utilities.php">Set Utilities</a>
    <a href="create_notification.php">Manage Notifications</a>
    <a href="view_notifications.php">View Notifications</a>
    <a href="admin_dashboard.php">Confirm Payment</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
</div>

<div class="content">
    <!-- Container แรกสำหรับแสดงจำนวนห้อง -->
    <div class="container">
        <h2>Room Status Summary</h2>
        <div class="stats">
            <div class="stat-box">
                <p>Available Rooms</p>
                <p><?php echo $availableRooms; ?></p>
            </div>
            <div class="stat-box">
                <p>Rented Rooms</p>
                <p><?php echo $rentedRooms; ?></p>
            </div>
            <div class="stat-box">
                <p>Pending Rooms</p>
                <p><?php echo $pendingRooms; ?></p>
            </div>
        </div>
    </div>

    <!-- Container ที่สองสำหรับแสดงกราฟ -->
    <div class="container">
        <h2>Monthly Utility Usage (Total Electricity and Water)</h2>
        <canvas id="utilityChart"></canvas>
    </div>

    <!-- Container ที่สามสำหรับแสดงตารางผู้ใช้ที่เช่าห้อง -->
    <div class="container">
        <h2>Users Who Rented Rooms</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultRentedUsers->num_rows > 0): ?>
                    <?php while ($row = $resultRentedUsers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['firstName']); ?></td>
                            <td><?php echo htmlspecialchars($row['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No rented rooms found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// ข้อมูลจาก PHP
const months = <?php echo json_encode($months); ?>;
const totalElectricity = <?php echo json_encode($totalElectricity); ?>;
const totalWater = <?php echo json_encode($totalWater); ?>;

// สร้างกราฟโดยใช้ Chart.js
const ctx = document.getElementById('utilityChart').getContext('2d');
const utilityChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [
            {
                label: 'Total Electricity Bill (฿)',
                data: totalElectricity,
                fill: false,
                borderColor: 'rgba(54, 162, 235, 1)',
                tension: 0.1
            },
            {
                label: 'Total Water Bill (฿)',
                data: totalWater,
                fill: false,
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Month'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Total Bill (฿)'
                },
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>
