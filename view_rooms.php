<?php
include 'connect.php'; // Connect to the database
session_start();

// Initialize search variables
$searchRoomType = '';
$searchStatus = '';

// Handle search request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchRoomType = $_POST['room_type'] ?? '';
    $searchStatus = $_POST['status'] ?? '';
}

// Fetch room data from the database with optional filters
$searchQuery = "";
$searchParams = [];
$sql = "SELECT * FROM rooms WHERE 1=1"; // Base query to get all rooms

// Store selected filter options
$selectedRoomType = '';
$selectedAvailability = '';

if (isset($_POST['search'])) {
    $roomType = $_POST['room_type'] ?? '';
    $availability = $_POST['availability'] ?? '';

    if (!empty($roomType)) {
        $sql .= " AND room_type = ?";
        $searchParams[] = $roomType;
        $selectedRoomType = $roomType; // Store selected room type
    }

    if (!empty($availability)) {
        $sql .= " AND status = ?";
        $searchParams[] = $availability;
        $selectedAvailability = $availability; // Store selected availability
    }
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

if (!empty($searchParams)) {
    $paramTypes = str_repeat("s", count($searchParams));
    $stmt->bind_param($paramTypes, ...$searchParams);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Rooms</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .room-card {
            margin-bottom: 30px;
        }
        .card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
        }
        .navbar {
            margin-bottom: 30px;
        }
        .filter-form {
            margin-bottom: 20px;
        }

    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="#">The Brick Place</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="home.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="search_rooms.php">Search Rooms</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index2.php">Dashboard</a>
            </li>
            <?php if (isset($_SESSION['userId'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="container mt-4">
    <h1>Available Rooms</h1>

    <!-- Filter Form -->
    <form action="view_rooms.php" method="post" class="mb-4">
        <h5>Room Type</h5>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-outline-primary <?php echo ($selectedRoomType == '') ? 'active' : ''; ?>">
                <input type="radio" name="room_type" value="" <?php echo ($selectedRoomType == '') ? 'checked' : ''; ?>> Any
            </label>
            <label class="btn btn-outline-primary <?php echo ($selectedRoomType == 'One Bedroom, One Bathroom, One Living Room') ? 'active' : ''; ?>">
                <input type="radio" name="room_type" value="One Bedroom, One Bathroom, One Living Room" <?php echo ($selectedRoomType == 'One Bedroom, One Bathroom, One Living Room') ? 'checked' : ''; ?>> One Bedroom
            </label>
            <label class="btn btn-outline-primary <?php echo ($selectedRoomType == 'Two Bedrooms, One Bathroom, One Living Room') ? 'active' : ''; ?>">
                <input type="radio" name="room_type" value="Two Bedrooms, One Bathroom, One Living Room" <?php echo ($selectedRoomType == 'Two Bedrooms, One Bathroom, One Living Room') ? 'checked' : ''; ?>> Two Bedrooms
            </label>
            <label class="btn btn-outline-primary <?php echo ($selectedRoomType == 'One Hall, One Bathroom') ? 'active' : ''; ?>">
                <input type="radio" name="room_type" value="One Hall, One Bathroom" <?php echo ($selectedRoomType == 'One Hall, One Bathroom') ? 'checked' : ''; ?>> Hall
            </label>
        </div>

        <h5 class="mt-4">Availability</h5>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-outline-primary <?php echo ($selectedAvailability == '') ? 'active' : ''; ?>">
                <input type="radio" name="availability" value="" <?php echo ($selectedAvailability == '') ? 'checked' : ''; ?>> Any
            </label>
            <label class="btn btn-outline-primary <?php echo ($selectedAvailability == 'available') ? 'active' : ''; ?>">
                <input type="radio" name="availability" value="available" <?php echo ($selectedAvailability == 'available') ? 'checked' : ''; ?>> Available
            </label>
            <label class="btn btn-outline-primary <?php echo ($selectedAvailability == 'rented') ? 'active' : ''; ?>">
                <input type="radio" name="availability" value="rented" <?php echo ($selectedAvailability == 'rented') ? 'checked' : ''; ?>> Rented
            </label>
        </div>

        <!-- Move Search Button here -->
        <div class="text-center mt-3">
            <button type="submit" name="search" class="btn btn-primary">Search</button>
        </div>
    </form>

    <div class="row">
        <?php while ($room = $result->fetch_assoc()): ?>
            <div class="col-md-4 room-card">
                <div class="card">
                    <img src="<?php echo $room['image_url_1']; ?>" class="card-img-top" alt="Room Image">
                    <div class="card-body">
                        <h5 class="card-title">Room <?php echo htmlspecialchars($room['room_number']); ?></h5>
                        <p class="card-text">Type: <?php echo htmlspecialchars($room['room_type']); ?></p>
                        <p class="card-text">Price: ฿<?php echo number_format($room['price'], 2); ?></p>
                        <p class="card-text">Status: <?php echo ucfirst($room['status']); ?></p>
                        <a href="room_details.php?id=<?php echo htmlspecialchars($room['id']); ?>" class="btn btn-info">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
