<?php
include 'connect.php';

// Fetch all room data from the database
$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);
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
            overflow: hidden; /* Ensures that the corners of the card are rounded */
            transition: transform 0.2s; /* Smooth hover effect */
        }
        .card:hover {
            transform: scale(1.05); /* Slightly scale the card on hover */
        }
        .card-img-top {
            height: 200px; /* Fixed height for the image */
            object-fit: cover; /* Ensures the image covers the area */
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
        }
        .navbar {
            margin-bottom: 30px; /* Space below navbar */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="#">The Brick Place </a>
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
$conn->close();
?>
