<?php
include 'connect.php';
session_start();

// Handle the search query
$searchQuery = '';
if (isset($_POST['search'])) {
    $roomType = $_POST['room_type'] ?? '';
    $availability = $_POST['availability'] ?? '';

    // Construct the SQL query
    $sql = "SELECT * FROM rooms WHERE 1=1"; // Base query
    if (!empty($roomType)) {
        $sql .= " AND room_type LIKE ?";
        $searchQuery .= "%$roomType%";
    }
    if ($availability === 'available' || $availability === 'rented') {
        $sql .= " AND status = ?";
        $searchQuery .= $availability;
    }

    $stmt = $conn->prepare($sql);
    if (!empty($roomType) && ($availability === 'available' || $availability === 'rented')) {
        $stmt->bind_param("ss", $searchQuery, $searchQuery);
    } elseif (!empty($roomType)) {
        $stmt->bind_param("s", $searchQuery);
    } elseif ($availability === 'available' || $availability === 'rented') {
        $stmt->bind_param("s", $searchQuery);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Default case: fetch all rooms
    $sql = "SELECT * FROM rooms";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Search Rooms</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Search Rooms</h1>
    <form action="search_rooms.php" method="post">
    <div class="form-group">
            <label>Room Type</label>
            <select class="form-control" name="room_type" required>
                <option value="One Bedroom, One Bathroom, One Living Room">One Bedroom, One Bathroom, One Living Room</option>
                <option value="Two Bedrooms, One Bathroom, One Living Room">Two Bedrooms, One Bathroom, One Living Room</option>
                <option value="One Hall, One Bathroom">One Hall, One Bathroom</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="availability">
                <option value="">Any</option>
                <option value="available">Available</option>
                <option value="rented">Rented</option>
            </select>
        </div>
        <button type="submit" name="search" class="btn btn-primary">Search</button>
    </form>

    <h2>Available Rooms</h2>
    <div class="row">
        <?php while ($room = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card">
                    <img src="<?php echo $room['image_url_1']; ?>" class="card-img-top" alt="Room Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $room['room_number']; ?></h5>
                        <p class="card-text">Type: <?php echo $room['room_type']; ?></p>
                        <p class="card-text">Price: ฿<?php echo $room['price']; ?></p>
                        <p class="card-text">Status: <?php echo $room['status']; ?></p>
                        <a href="room_details.php?id=<?php echo $room['id']; ?>" class="btn btn-info">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
