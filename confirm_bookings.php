<?php
include 'connect.php';
session_start();

// Check if user is admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle confirmation of booking
if (isset($_POST['confirm_booking'])) {
    $roomId = $_POST['room_id'];

    // Update the room status to 'rented'
    $stmt = $conn->prepare("UPDATE rooms SET status = 'rented' WHERE id = ?");
    $stmt->bind_param("i", $roomId);

    if ($stmt->execute()) {
        $success = "Booking confirmed successfully!";
    } else {
        $error = "Error confirming the booking.";
    }
}

// Handle cancellation of booking
if (isset($_POST['cancel_booking'])) {
    $roomId = $_POST['room_id'];

    // Update the room status back to 'available' and clear renter info
    $stmt = $conn->prepare("UPDATE rooms SET status = 'available', rented_by = NULL, check_in_date = NULL, expiration_date = NULL WHERE id = ?");
    $stmt->bind_param("i", $roomId);

    if ($stmt->execute()) {
        $success = "Booking canceled successfully!";
    } else {
        $error = "Error canceling the booking.";
    }
}

// Fetch rooms that are pending confirmation
$sql = "SELECT rooms.id, rooms.room_number, registration.firstName, registration.lastName, rooms.check_in_date 
        FROM rooms
        JOIN registration ON rooms.rented_by = registration.id
        WHERE rooms.status = 'pending'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm or Cancel Bookings</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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

<!-- Sidebar -->
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

<div class="container mt-4">
    <h1>Confirm or Cancel Bookings</h1>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Room Number</th>
                <th>Renter Name</th>
                <th>Check-in Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></td>
                <td><?php echo htmlspecialchars($row['check_in_date']); ?></td>
                <td>
                    <form action="confirm_bookings.php" method="post" style="display:inline;">
                        <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <button type="submit" name="confirm_booking" class="btn btn-success">Confirm Booking</button>
                    </form>
                    <form action="confirm_bookings.php" method="post" style="display:inline;">
                        <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <button type="submit" name="cancel_booking" class="btn btn-danger">Cancel Booking</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
