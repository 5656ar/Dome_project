<?php
include 'connect.php'; // Connect to the database
session_start(); 

// Check if the user is logged in as Admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch all rooms for dropdown
$roomsSql = "SELECT id, room_number FROM rooms";
$roomsResult = $conn->query($roomsSql);

// Initialize variables for filtering
$selectedRoomId = isset($_POST['room_id']) ? $_POST['room_id'] : '';
$selectedUserId = isset($_POST['user_id']) ? $_POST['user_id'] : '';
$selectedMonthYear = isset($_POST['month_year']) ? $_POST['month_year'] : '';

// Start building SQL query for utility bills
$sql = "SELECT r.room_number, u.id AS utility_id, u.month_year, u.water_bill, u.electricity_bill, r.price, u.user_id, u.payment_status
        FROM utility_bills u
        JOIN rooms r ON u.room_id = r.id";

// Only add conditions if filters are applied
$conditions = [];
$bindParams = [];
$bindTypes = '';

if ($selectedRoomId) {
    $conditions[] = "r.id = ?";
    $bindParams[] = $selectedRoomId;
    $bindTypes .= 'i';
}
if ($selectedUserId) {
    $conditions[] = "u.user_id = ?";
    $bindParams[] = $selectedUserId;
    $bindTypes .= 'i';
}
if ($selectedMonthYear) {
    $conditions[] = "u.month_year = ?";
    $bindParams[] = $selectedMonthYear;
    $bindTypes .= 's';
}

// Append conditions to SQL query if any are present
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY u.month_year DESC";

// Prepare and execute statement
$stmt = $conn->prepare($sql);

if ($bindParams) {
    $stmt->bind_param($bindTypes, ...$bindParams);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Utility Bills</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
        <h2>Utility Bills for All Rooms</h2>

        <form action="view_utilities.php" method="post">
            <div class="form-group">
                <label for="room_id">Select Room:</label>
                <select name="room_id" id="room_id" class="form-control">
                    <option value="">-- Select Room --</option>
                    <?php while ($room = $roomsResult->fetch_assoc()): ?>
                        <option value="<?php echo $room['id']; ?>" <?php echo $selectedRoomId == $room['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($room['room_number']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="user_id">Filter by User ID:</label>
                <input type="text" name="user_id" id="user_id" class="form-control" value="<?php echo htmlspecialchars($selectedUserId); ?>">
            </div>

            <div class="form-group">
                <label for="month_year">Filter by Month & Year:</label>
                <input type="month" name="month_year" id="month_year" class="form-control" value="<?php echo htmlspecialchars($selectedMonthYear); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Show Utility Bills</button>
        </form>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Month & Year</th>
                    <th>Water Bill (฿)</th>
                    <th>Electricity Bill (฿)</th>
                    <th>Room Price (฿)</th>
                    <th>Total Bill (฿)</th>
                    <th>User ID</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Format Total Bill with two decimal places
                    $totalBill = number_format($row['water_bill'] + $row['electricity_bill'] + $row['price'], 2);
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['month_year']) . "</td>";
                    echo "<td>" . htmlspecialchars(number_format($row['water_bill'], 2)) . "</td>";
                    echo "<td>" . htmlspecialchars(number_format($row['electricity_bill'], 2)) . "</td>";
                    echo "<td>" . htmlspecialchars(number_format($row['price'], 2)) . "</td>";
                    echo "<td>" . htmlspecialchars($totalBill) . "</td>";
                    echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['payment_status']) . "</td>";
                    echo "<td>
                            <button class='btn btn-warning' data-toggle='modal' data-target='#editModal' data-id='" . htmlspecialchars($row['utility_id']) . "' data-water='" . htmlspecialchars($row['water_bill']) . "' data-electricity='" . htmlspecialchars($row['electricity_bill']) . "'>Edit</button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No utility bills found</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for editing utility bills -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Utility Bill</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="update_utilities.php" method="post">
                    <input type="hidden" name="utility_id" id="utility_id">
                    <div class="form-group">
                        <label for="water_bill">Water Bill (฿)</label>
                        <input type="number" step="0.01" class="form-control" id="water_bill" name="water_bill" required>
                    </div>
                    <div class="form-group">
                        <label for="electricity_bill">Electricity Bill (฿)</label>
                        <input type="number" step="0.01" class="form-control" id="electricity_bill" name="electricity_bill" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); 
        var utilityId = button.data('id');
        var waterBill = button.data('water');
        var electricityBill = button.data('electricity');

        var modal = $(this);
        modal.find('#utility_id').val(utilityId);
        modal.find('#water_bill').val(waterBill);
        modal.find('#electricity_bill').val(electricityBill);
    });
</script>
</body>
</html>

<?php
$conn->close();
?>
