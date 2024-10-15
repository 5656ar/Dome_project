<?php
include 'connect.php';
session_start(); // Start the session

// Check if session variables are set
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];

// Fetch room booking details
$roomStmt = $conn->prepare("SELECT id, room_number, price FROM rooms WHERE rented_by = ?");
$roomStmt->bind_param("i", $userId);
$roomStmt->execute();
$roomResult = $roomStmt->get_result();
$bookedRoom = $roomResult->fetch_assoc();

if (!$bookedRoom) {
    die("You have not booked any rooms.");
}

// Fetch utility bills for the booked room
$roomId = $bookedRoom['id'];
$sql = "SELECT month_year, water_bill, electricity_bill, (water_bill + electricity_bill + ?) AS total_bill
        FROM utility_bills 
        WHERE room_id = ? 
        ORDER BY month_year DESC";

$stmt = $conn->prepare($sql);
$price = $bookedRoom['price']; // Get the room price
$stmt->bind_param("di", $price, $roomId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Utility Bills</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .hero {
            background-color: #007bff;
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .table {
            margin-top: 20px;
            border-radius: 5px;
            overflow: hidden;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-back {
            margin-top: 20px;
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="hero">
        <h2>Utility Bills for Room Number: <?php echo htmlspecialchars($bookedRoom['room_number']); ?></h2>
    </div>

    <table class="table table-bordered table-striped">
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
                echo "<td>" . htmlspecialchars($row['water_bill']) . "</td>";
                echo "<td>" . htmlspecialchars($row['electricity_bill']) . "</td>";
                echo "<td>" . htmlspecialchars($price) . "</td>"; // Show room price
                echo "<td>" . htmlspecialchars($row['total_bill']) . "</td>"; // Display calculated total bill
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No utility bills found for this room.</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <a href="index2.php" class="btn btn-back">Back to Dashboard</a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
