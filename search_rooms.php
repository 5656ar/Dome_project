<?php
include 'auth.php'; // Check if user is logged in
include 'connect.php';

// Function to send Line notification
function sendLineNotification($userId, $room_id, $check_in_date, $expiration_date) {
    $line_token = 'rI5knzZDWvPwbfZnbj6cXHZmZRBPrQVe7ZxTKxI5BlK'; 
    $message = "User ID: $userId has booked Room ID: $room_id.\nCheck-in Date: $check_in_date.\nExpiration Date: $expiration_date.";

    $data = [
        'message' => $message,
    ];

    $ch = curl_init("https://notify-api.line.me/api/notify");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Bearer ' . $line_token,
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

$userId = $_SESSION['userId']; // Get userId from session

// Check if user has already booked a room
$checkBookingStmt = $conn->prepare("SELECT * FROM rooms WHERE rented_by = ? AND status IN ('pending', 'rented')");
$checkBookingStmt->bind_param("i", $userId);
$checkBookingStmt->execute();
$checkBookingResult = $checkBookingStmt->get_result();
$hasBookedRoom = $checkBookingResult->num_rows > 0;

// Handle room booking request
if (isset($_POST['book_room']) && !$hasBookedRoom) {
    $room_id = $_POST['room_id'];
    $check_in_date = $_POST['check_in_date']; // Get check-in date
    $expiration_date = date('Y-m-d', strtotime($check_in_date . ' +1 year')); // Set expiration date to 1 year from check-in date

    // Check if room is available
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ? AND status = 'available'");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Room is available, proceed with booking (set status to 'pending')
        $stmt = $conn->prepare("UPDATE rooms SET status = 'pending', rented_by = ?, check_in_date = ?, expiration_date = ? WHERE id = ?");
        $stmt->bind_param("issi", $userId, $check_in_date, $expiration_date, $room_id);

        if ($stmt->execute()) {
            $success = "Room booking is pending. Admin will confirm your booking soon.";
            $hasBookedRoom = true;

            // Send Line notification
            sendLineNotification($userId, $room_id, $check_in_date, $expiration_date);
        } else {
            $error = "Error booking the room. Please try again.";
        }
    } else {
        $error = "Room is no longer available.";
    }
} elseif (isset($_POST['book_room']) && $hasBookedRoom) {
    $error = "You have already booked a room. You cannot book another one.";
}

// Handle the search query
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

// Prepare the statement and bind parameters dynamically
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Rooms</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
<a class="navbar-brand" href="admin_dashboard.php">The Brick Place </a>
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
    <h1>Search Rooms</h1>

    <!-- Display success or error messages -->
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Button Menu for Filters -->
    <form action="search_rooms.php" method="post" class="mb-4">
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

    <!-- Results -->
    <h2 class="mt-5">Available Rooms</h2>
    <div class="row">
        <?php while ($room = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <img src="<?php echo htmlspecialchars($room['image_url_1']); ?>" class="card-img-top" alt="Room Image">
                    <div class="card-body">
                        <h5 class="card-title">Room <?php echo htmlspecialchars($room['room_number']); ?></h5>
                        <p class="card-text">Type: <?php echo htmlspecialchars($room['room_type']); ?></p>
                        <p class="card-text">Price: ฿<?php echo htmlspecialchars($room['price']); ?></p>
                        <p class="card-text">Status: <?php echo htmlspecialchars($room['status']); ?></p>
                        <a href="room_detailsuser.php?id=<?php echo htmlspecialchars($room['id']); ?>" class="btn btn-info">View Details</a>

                        <!-- Show "Book" button only if room is available and the user hasn't booked another room -->
                        <?php if ($room['status'] == 'available' && !$hasBookedRoom): ?>
                            <form action="search_rooms.php" method="post">
                                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['id']); ?>">
                                <div class="form-group mt-2">
                                    <label for="check_in_date">Check-in Date</label>
                                    <input type="date" name="check_in_date" class="form-control" required>
                                </div>
                                <button type="submit" name="book_room" class="btn btn-success mt-3">Book</button>
                            </form>
                        <?php elseif ($room['status'] == 'rented' && $room['rented_by'] == $userId): ?>
                            <p class="text-success">You have booked this room.</p>
                        <?php elseif ($room['status'] == 'pending' && $room['rented_by'] == $userId): ?>
                            <p class="text-warning">Your booking is pending confirmation by admin.</p>
                        <?php endif; ?>
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
$conn->close();
?>
