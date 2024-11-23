<?php
include 'connect.php'; // Connect to the database
session_start(); 

// Check if the user is Admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Initialize search variables
$searchRoomNumber = '';

// Handle search request
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['search_room_number'])) {
    $searchRoomNumber = $_POST['search_room_number'];
} else {
    $searchRoomNumber = ''; // Reset the search field if no search is performed
}

// Handle Delete Room request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_room'])) {
    $roomId = $_POST['room_id'];
    
    // Delete the room from the database
    $deleteStmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $deleteStmt->bind_param("i", $roomId);
    
    if ($deleteStmt->execute()) {
        $success = "Room deleted successfully.";
    } else {
        $error = "Failed to delete room.";
    }
    
    $deleteStmt->close();
}

// Fetch room data from the database with user info if rented
$sql = "SELECT rooms.*, registration.firstName, registration.lastName, registration.id as user_id
        FROM rooms 
        LEFT JOIN registration ON rooms.rented_by = registration.id";

if (!empty($searchRoomNumber)) {
    $sql .= " WHERE rooms.room_number LIKE ?";
}

$sql .= " ORDER BY rooms.room_number"; // Order by room number

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if searching
if (!empty($searchRoomNumber)) {
    $searchRoomNumber = "%$searchRoomNumber%";
    $stmt->bind_param("s", $searchRoomNumber);
}

$stmt->execute();
$result = $stmt->get_result();

// Handle Cancel Booking request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $roomId = $_POST['room_id'];
    
    // Update the room status to available and clear the rented_by field
    $cancelStmt = $conn->prepare("UPDATE rooms SET status = 'available', rented_by = NULL WHERE id = ?");
    $cancelStmt->bind_param("i", $roomId);
    
    if ($cancelStmt->execute()) {
        $success = "Booking canceled successfully.";
    } else {
        $error = "Failed to cancel booking.";
    }
    
    $cancelStmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room List</title>
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
            <h1 class="text-center">Room List</h1>
            <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addRoomModal">Add New Room</button>
            <form action="show_rooms.php" method="POST" class="mb-4">
                <div class="form-row">
                    <div class="form-group col-md-10">
                        <label for="search_room_number">Search Room Number</label>
                        <input type="text" class="form-control" id="search_room_number" name="search_room_number" value="<?php echo htmlspecialchars($searchRoomNumber); ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
                    </div>
                </div>
            </form>

            <!-- Display success or error messages -->
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>Price (฿)</th>
                        <th>Furniture</th>
                        <th>Status</th>
                        <th>User Rented (ID)</th>
                        <th>Images</th>
                        <th>Floor Plan</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['room_type']) . "</td>";
                        echo "<td>" . number_format($row['price'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($row['furniture']) . "</td>";
                        echo "<td>" . ucfirst($row['status']) . "</td>";

                        // Show user name if rented
                        if ($row['status'] == 'rented' && !empty($row['firstName'])) {
                            echo "<td>" . htmlspecialchars($row['firstName']) . " " . htmlspecialchars($row['lastName']) . " (ID: " . htmlspecialchars($row['user_id']) . ")</td>";
                        } else {
                            echo "<td>-</td>"; // Display - if not rented
                        }

                        echo "<td>";
                        for ($i = 1; $i <= 4; $i++) {
                            $imageUrl = $row['image_url_' . $i];
                            if (!empty($imageUrl)) {
                                echo "<img src='" . htmlspecialchars($imageUrl) . "' alt='Room Image' style='width:100px;height:auto; margin-right:5px;'>";
                            }
                        }
                        echo "</td>";

                        // Display the floor plan image if it exists
                        echo "<td>";
                        if (!empty($row['floor_plan_url'])) {
                            echo "<img src='" . htmlspecialchars($row['floor_plan_url']) . "' alt='Floor Plan' style='width:100px;height:auto;'>";
                        } else {
                            echo "-"; // Display - if no floor plan exists
                        }
                        echo "</td>";

                        echo "<td>" . htmlspecialchars($row['details']) . "</td>";
                        echo "<td>";
                        echo "<a href='edit_room.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-warning mb-2'>Edit</a>";
                        
                        // Show "Cancel Booking" button if room is rented
                        if ($row['status'] == 'rented') {
                            echo "<form method='post' style='display:inline-block;'>"; 
                            echo "<input type='hidden' name='room_id' value='" . htmlspecialchars($row['id']) . "'>";
                            echo "<button type='submit' name='cancel_booking' class='btn btn-danger mb-2' onclick='return confirm(\"Are you sure you want to cancel the booking?\");'>Cancel Booking</button>";
                            echo "</form>";
                        }

                        // Add Delete Room button
                        echo "<form method='post' style='display:inline-block;'>"; 
                        echo "<input type='hidden' name='room_id' value='" . htmlspecialchars($row['id']) . "'>";
                        echo "<button type='submit' name='delete_room' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this room?\");'>Delete Room</button>";
                        echo "</form>";

                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No rooms found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <!-- Modal for adding new room -->
        <div class="modal fade" id="addRoomModal" tabindex="-1" role="dialog" aria-labelledby="addRoomModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRoomModalLabel">Add New Room</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="add_room.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Room Number</label>
                                <input type="text" class="form-control" name="room_number" required>
                            </div>
                            <div class="form-group">
                                <label>Room Type</label>
                                <select class="form-control" name="room_type" required>
                                    <option value="One Bedroom, One Bathroom, One Living Room">One Bedroom, One Bathroom, One Living Room</option>
                                    <option value="Two Bedrooms, One Bathroom, One Living Room">Two Bedrooms, One Bathroom, One Living Room</option>
                                    <option value="One Hall, One Bathroom">One Hall, One Bathroom</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Price (฿)</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Furniture</label>
                                <textarea class="form-control" name="furniture" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="status">
                                    <option value="available">Available</option>
                                    <option value="rented">Rented</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Room Images (Upload up to 4)</label>
                                <input type="file" class="form-control" name="room_image[]" multiple required>
                            </div>
                            <div class="form-group">
                                <label>Floor Plan Image</label>
                                <input type="file" class="form-control" name="floor_plan" required>
                            </div>
                            <div class="form-group">
                                <label>Details</label>
                                <textarea class="form-control" name="details" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Room</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
