<?php
include 'connect.php';
session_start(); // Start the session

// Check if session variables are set
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];

// Fetch user data from the database to get the latest information
$stmt = $conn->prepare("SELECT firstName, lastName, gender, email, number FROM registration WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$row = $result->fetch_assoc();

// Store data in session variables for easy access
$_SESSION['firstName'] = $row['firstName'];
$_SESSION['lastName'] = $row['lastName'];
$_SESSION['email'] = $row['email'];
$_SESSION['number'] = $row['number'];

// Fetch room booking details
$roomStmt = $conn->prepare("SELECT * FROM rooms WHERE rented_by = ?");
$roomStmt->bind_param("i", $userId);
$roomStmt->execute();
$roomResult = $roomStmt->get_result();
$bookedRoom = $roomResult->fetch_assoc();

// Fetch latest utility bill details with payment status
$latestUtilityStmt = $conn->prepare("SELECT month_year, water_bill, electricity_bill, payment_status
                                      FROM utility_bills 
                                      WHERE room_id = ? 
                                      ORDER BY month_year DESC 
                                      LIMIT 1");
if ($bookedRoom) {
    $latestUtilityStmt->bind_param("i", $bookedRoom['id']);
    $latestUtilityStmt->execute();
    $utilityResult = $latestUtilityStmt->get_result();
    $latestUtility = $utilityResult->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover {
            background-color: #007bff;
        }
        .container {
            margin-left: 260px; /* Adjust for sidebar */
            margin-top: 20px;
        }
        .hero {
            background-color: #007bff;
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .welcome-message {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .room-info, .utility-table {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-danger {
            margin-top: 15px;
        }
        .tooltip-inner {
            background-color: #dc3545; /* Red background for tooltip */
            color: white; /* White text color */
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="text-white text-center">LOGO</h2>
        <a href="index.php">Home</a> 
        <a href="search_rooms.php">Search Rooms</a>
        <a href="#" data-toggle="modal" data-target="#editProfileModal">Edit Profile</a>
        <a href="#" data-toggle="modal" data-target="#utilityModal">View Utility Bills</a>
        <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
    </div>

    <div class="container">
        <div class="hero text-center">
            <h1 class="welcome-message">Welcome to Your Dashboard!</h1>
            <h3><?php echo htmlspecialchars($_SESSION['firstName']) . " " . htmlspecialchars($_SESSION['lastName']); ?></h3>
            <h5><?php echo "Your email is: " . htmlspecialchars($_SESSION['email']); ?></h5>
            <h5><?php echo "Your ID is: " . htmlspecialchars($userId); ?></h5>
            <p>You are logged in.</p>
        </div>

        <!-- Display latest utility bill on the right side of the hero -->
        <div class="utility-table" style="margin-bottom: 40px;">
            <h5>Latest Utility Bill</h5>
            <?php if (isset($latestUtility) && $latestUtility): ?>
                <p><strong>Month & Year:</strong> <?php echo htmlspecialchars($latestUtility['month_year']); ?></p>
                <p><strong>Water Bill:</strong> ฿<?php echo number_format($latestUtility['water_bill'], 2); ?></p>
                <p><strong>Electricity Bill:</strong> ฿<?php echo number_format($latestUtility['electricity_bill'], 2); ?></p>
                <p><strong>Room Price:</strong> ฿<?php echo number_format($bookedRoom['price'], 2); ?></p>
                <p><strong>Total Bill:</strong> ฿<?php 
                    $totalBill = ($latestUtility['water_bill'] ?? 0) + ($latestUtility['electricity_bill'] ?? 0) + $bookedRoom['price'];
                    echo number_format($totalBill, 2); 
                ?></p>
                <p><strong>Payment Status:</strong> 
                    <?php 
                    $paymentStatus = htmlspecialchars($latestUtility['payment_status']);
                    echo $paymentStatus === 'paid' ? 'Paid' : 
                    '<span class="text-danger" data-toggle="tooltip" title="Please pay your bill!" style="cursor: pointer;">Unpaid</span>'; 
                    ?>
                </p>
            <?php else: ?>
                <p>No utility bills found.</p>
            <?php endif; ?>
        </div>

        <!-- Edit Profile Modal -->
        <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="update_profile.php" method="post">
                            <input type="hidden" name="userId" value="<?php echo $userId; ?>">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($row['firstName']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($row['lastName']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="m" <?php echo $row['gender'] === 'm' ? 'selected' : ''; ?>>Male</option>
                                    <option value="f" <?php echo $row['gender'] === 'f' ? 'selected' : ''; ?>>Female</option>
                                    <option value="o" <?php echo $row['gender'] === 'o' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="number">Phone Number</label>
                                <input type="text" class="form-control" id="number" name="number" value="<?php echo htmlspecialchars($row['number']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utility Bills Modal -->
        <div class="modal fade" id="utilityModal" tabindex="-1" role="dialog" aria-labelledby="utilityModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="utilityModalLabel">Utility Bills</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Fetch and display utility bills -->
                        <?php
                        if ($bookedRoom) {
                            $roomId = $bookedRoom['id'];
                            $sql = "SELECT month_year, water_bill, electricity_bill, (water_bill + electricity_bill + ?) AS total_bill
                                    FROM utility_bills 
                                    WHERE room_id = ? 
                                    ORDER BY month_year DESC";

                            $stmt = $conn->prepare($sql);
                            $price = $bookedRoom['price']; 
                            $stmt->bind_param("di", $price, $roomId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                        ?>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Month & Year</th>
                                    <th>Water Bill (฿)</th>
                                    <th>Electricity Bill (฿)</th>
                                    <th>Room Price (฿)</th>
                                    <th>Total Bill (฿)</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['month_year']) . "</td>";
                                    echo "<td>฿" . number_format($row['water_bill'], 2) . "</td>";
                                    echo "<td>฿" . number_format($row['electricity_bill'], 2) . "</td>";
                                    echo "<td>฿" . number_format($price, 2) . "</td>";
                                    echo "<td>฿" . number_format($row['total_bill'], 2) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No utility bills found for this room.</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                            <p>You have not booked any rooms.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($bookedRoom): ?>
            <div class="room-info">
                <h3>Your Booked Room</h3>
                <p><strong>Room Number:</strong> <?php echo htmlspecialchars($bookedRoom['room_number']); ?></p>
                <p><strong>Room Type:</strong> <?php echo htmlspecialchars($bookedRoom['room_type']); ?></p>
                <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($bookedRoom['check_in_date']); ?></p>
                <p><strong>Expiration Date:</strong> <?php echo htmlspecialchars(date('Y-m-d', strtotime($bookedRoom['check_in_date'] . ' +1 year'))); ?></p> <!-- Calculate and display expiration date -->
            </div>
        <?php else: ?>
            <p class="text-danger">You haven't booked any rooms yet.</p>
        <?php endif; ?>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Initialize tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>