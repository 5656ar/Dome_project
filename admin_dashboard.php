<?php
include 'connect.php'; // Connect to the database
session_start();

// Check if user is logged in as Admin
if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit();
}

// Initialize search variables
$searchRoomNumber = '';
$searchRenterName = '';
$paymentStatusFilter = 'all'; // Default value for payment status filter

// Prepare the SQL query with optional search filters
$sql = "
    SELECT rooms.room_number, registration.firstName, registration.lastName, registration.id AS user_id, rooms.check_in_date, rooms.id as room_id,
           (SELECT water_bill FROM utility_bills WHERE room_id = rooms.id AND user_id = rooms.rented_by ORDER BY month_year DESC LIMIT 1) AS latest_water_bill,
           (SELECT electricity_bill FROM utility_bills WHERE room_id = rooms.id AND user_id = rooms.rented_by ORDER BY month_year DESC LIMIT 1) AS latest_electricity_bill,
           (SELECT month_year FROM utility_bills WHERE room_id = rooms.id AND user_id = rooms.rented_by ORDER BY month_year DESC LIMIT 1) AS latest_bill_month,
           (SELECT payment_status FROM utility_bills WHERE room_id = rooms.id AND user_id = rooms.rented_by ORDER BY month_year DESC LIMIT 1) AS latest_payment_status,
           rooms.price AS room_price
    FROM rooms 
    LEFT JOIN registration ON rooms.rented_by = registration.id
    WHERE rooms.status = 'rented'";

// Handle search
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['room_number'])) {
        $searchRoomNumber = $_POST['room_number'];
        $sql .= " AND rooms.room_number LIKE ?";
    }
    if (!empty($_POST['renter_name'])) {
        $searchRenterName = $_POST['renter_name'];
        $sql .= " AND (registration.firstName LIKE ? OR registration.lastName LIKE ?)";
    }
    
    // Handle payment status filter
    if (!empty($_POST['payment_status']) && $_POST['payment_status'] !== 'all') {
        $paymentStatusFilter = $_POST['payment_status'];
        $sql .= " AND (SELECT payment_status FROM utility_bills WHERE room_id = rooms.id AND user_id = rooms.rented_by ORDER BY month_year DESC LIMIT 1) = ?";
    }

    // Handle payment confirmation
    if (isset($_POST['confirm_payment'])) {
        $roomId = $_POST['room_id'];
        $updateStmt = $conn->prepare("UPDATE utility_bills SET payment_status = 'paid' WHERE room_id = ? AND month_year = (SELECT month_year FROM utility_bills WHERE room_id = ? ORDER BY month_year DESC LIMIT 1)");
        $updateStmt->bind_param("ii", $roomId, $roomId);
        $updateStmt->execute();
        $updateStmt->close();
    }

    // Handle unconfirm payment
    if (isset($_POST['unconfirm_payment'])) {
        $roomId = $_POST['room_id'];
        $updateStmt = $conn->prepare("UPDATE utility_bills SET payment_status = 'unpaid' WHERE room_id = ? AND month_year = (SELECT month_year FROM utility_bills WHERE room_id = ? ORDER BY month_year DESC LIMIT 1)");
        $updateStmt->bind_param("ii", $roomId, $roomId);
        $updateStmt->execute();
        $updateStmt->close();
    }
}

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if search fields are set
if (!empty($searchRoomNumber) && !empty($searchRenterName)) {
    $searchRoomNumber = "%$searchRoomNumber%";
    $searchRenterName = "%$searchRenterName%";
    if ($paymentStatusFilter !== 'all') {
        $stmt->bind_param("ssss", $searchRoomNumber, $searchRenterName, $searchRenterName, $paymentStatusFilter);
    } else {
        $stmt->bind_param("ss", $searchRoomNumber, $searchRenterName);
    }
} elseif (!empty($searchRoomNumber)) {
    $searchRoomNumber = "%$searchRoomNumber%";
    if ($paymentStatusFilter !== 'all') {
        $stmt->bind_param("sss", $searchRoomNumber, $paymentStatusFilter);
    } else {
        $stmt->bind_param("s", $searchRoomNumber);
    }
} elseif (!empty($searchRenterName)) {
    $searchRenterName = "%$searchRenterName%";
    if ($paymentStatusFilter !== 'all') {
        $stmt->bind_param("sss", $searchRenterName, $paymentStatusFilter);
    } else {
        $stmt->bind_param("s", $searchRenterName);
    }
} elseif ($paymentStatusFilter !== 'all') {
    $stmt->bind_param("s", $paymentStatusFilter);
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .sidebar {
            height: 100%; /* Full height for sidebar */
            background-color: #343a40;
            padding: 20px;
            position: fixed; /* Fix to the left */
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
            margin-left: 240px; /* Leave space for the sidebar */
            padding: 20px;
            flex: 1;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-top: 20px;
        }
        table {
            margin-top: 20px;
        }
        th, td {
            vertical-align: middle;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Menu</h2>
    <a href="home.php">Back To Home Page</a>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="show_users.php">Manage Users</a>
    <a href="show_rooms.php">Manage Rooms</a>
    <a href="confirm_bookings.php">Confirm bookings</a>
    <a href="view_utilities.php">View Utilities</a>
    <a href="set_utilities.php">Set Utilities</a>
    <a href="create_notification.php">Manage Notifications</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
</div>

<div class="content">
    <div class="container">
        <h1 class="text-center">Dashboard Admin</h1>

        <form action="admin_dashboard.php" method="POST" class="mb-4">
            <div class="form-row">
                <div class="form-group col-md-5">
                    <label for="room_number">Search Room Number</label>
                    <input type="text" class="form-control" id="room_number" name="room_number" value="">
                </div>
                <div class="form-group col-md-2">
                    <label for="payment_status">Payment Status</label>
                    <select class="form-control" id="payment_status" name="payment_status">
                        <option value="all">All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">Search</button>
                </div>
            </div>
        </form>

        <table class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Renter's Name (ID)</th>
                    <th>Latest Water Bill (฿)</th>
                    <th>Latest Electricity Bill (฿)</th>
                    <th>Latest Bill Month/Year</th>
                    <th>Room Price (฿)</th>
                    <th>Total Bill (฿)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        // Calculate total bill
                        $latestWaterBill = $row['latest_water_bill'] ?? 0;
                        $latestElectricityBill = $row['latest_electricity_bill'] ?? 0;
                        $roomPrice = $row['room_price'] ?? 0;
                        $totalBill = $latestWaterBill + $latestElectricityBill + $roomPrice;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['firstName'] . " " . $row['lastName'] . " (ID: " . $row['user_id'] . ")"); ?></td>
                            <td><?php echo htmlspecialchars($latestWaterBill); ?></td>
                            <td><?php echo htmlspecialchars($latestElectricityBill); ?></td>
                            <td><?php echo htmlspecialchars($row['latest_bill_month'] ?: 'No Bill'); ?></td>
                            <td><?php echo htmlspecialchars($roomPrice); ?></td>
                            <td><?php echo htmlspecialchars($totalBill); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="room_id" value="<?php echo $row['room_id']; ?>">
                                    <?php if ($row['latest_payment_status'] == 'unpaid'): ?>
                                        <button type="submit" name="confirm_payment" class="btn btn-success">Confirm Payment</button>
                                    <?php else: ?>
                                        <button type="submit" name="unconfirm_payment" class="btn btn-warning">Unconfirm Payment</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
