<?php
include 'connect.php'; // Include database connection

session_start();

// Check if user is Admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch all rented rooms and users
$sql = "SELECT r.id AS room_id, r.room_number, r.rented_by, u.firstName, u.lastName, u.email 
        FROM rooms r
        LEFT JOIN registration u ON r.rented_by = u.id
        WHERE r.status = 'rented'";
$result = $conn->query($sql);

// Define the LINE Notify token
$lineToken = 'ZNyhAfnGat1mPbz0PMJLnnYMLefB1QcV56B200ndGgE'; // Replace with your actual LINE token

// Function to send a message to LINE
function sendLineNotification($message, $token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "message=" . urlencode($message));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $headers = [
        "Content-type: application/x-www-form-urlencoded",
        "Authorization: Bearer " . $token,
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $monthYear = $_POST['month_year'];
    $updatedRooms = []; // Track room numbers that have updated bills

    foreach ($_POST['water_bill'] as $roomId => $waterBill) {
        if (isset($_POST['electricity_bill'][$roomId]) && isset($_POST['user_id'][$roomId])) {
            $electricityBill = $_POST['electricity_bill'][$roomId];
            $userId = $_POST['user_id'][$roomId];

            // Get the room number for the notification message
            $roomNumber = $_POST['room_number'][$roomId];

            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO utility_bills (room_id, user_id, month_year, water_bill, electricity_bill) 
                                    VALUES (?, ?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE water_bill = ?, electricity_bill = ?");
            $stmt->bind_param("iissddd", $roomId, $userId, $monthYear, $waterBill, $electricityBill, $waterBill, $electricityBill);
            $stmt->execute();

            // Track updated room numbers for notification
            $updatedRooms[] = $roomNumber;
        } else {
            echo "Error: Missing data for room ID $roomId.";
        }
    }

    if (!empty($updatedRooms)) {
        $success = "Utility bills updated successfully!";
        
        // Send a notification to LINE
        $message = "New utility bills have been updated for $monthYear. Updated rooms: " . implode(", ", $updatedRooms) . ".";
        sendLineNotification($message, $lineToken);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Utility Bills</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .sidebar {
            height: 100vh;
            width: 220px;
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
            border-radius: 8px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .alert {
            margin-top: 20px;
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
    <div class="container">
        <h2>Set Utility Bills for Rooms</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="set_utilities.php" method="post">
            <div class="form-group">
                <label for="month_year">Month & Year</label>
                <input type="month" class="form-control" id="month_year" name="month_year" required>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Water Bill (฿)</th>
                        <th>Electricity Bill (฿)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['rented_by']); ?></td>
                            <input type="hidden" name="user_id[<?php echo $row['room_id']; ?>]" value="<?php echo $row['rented_by']; ?>">
                            <input type="hidden" name="room_number[<?php echo $row['room_id']; ?>]" value="<?php echo htmlspecialchars($row['room_number']); ?>">
                            <td><?php echo htmlspecialchars($row['firstName']); ?></td>
                            <td><?php echo htmlspecialchars($row['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <input type="number" step="0.01" name="water_bill[<?php echo $row['room_id']; ?>]" value="80" class="form-control" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="electricity_bill[<?php echo $row['room_id']; ?>]" value="0" class="form-control" required>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary btn-block">Update Utility Bills</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
