<?php
include 'connect.php';
session_start(); 

// Check if user is logged in
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit();
// }

// Fetch room data from the database
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

    <!-- Add Room Button -->
    <div class="mb-3">
        <a href="add_room.php" class="btn btn-primary">Add Room</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Room Number</th>
                <th>Room Type</th>
                <th>Price (฿)</th>
                <th>Furniture</th>
                <th>Status</th>
                <th>Images</th>
                <th>Details</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['room_number'] . "</td>";
                echo "<td>" . $row['room_type'] . "</td>";
                echo "<td>" . number_format($row['price'], 2) . "</td>";
                echo "<td>" . $row['furniture'] . "</td>";
                echo "<td>" . ucfirst($row['status']) . "</td>";
                echo "<td>";
                for ($i = 1; $i <= 4; $i++) {
                    $imageUrl = $row['image_url_' . $i];
                    if (!empty($imageUrl)) {
                        echo "<img src='" . $imageUrl . "' alt='Room Image' style='width:100px;height:auto; margin-right:5px;'>";
                    }
                }
                echo "</td>";
                echo "<td>" . $row['details'] . "</td>";
                echo "<td><a href='edit_room.php?id=" . $row['id'] . "' class='btn btn-warning'>Edit</a></td>";
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
