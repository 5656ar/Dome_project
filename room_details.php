<?php
include 'connect.php';
session_start();

// Check if room ID is provided
if (isset($_GET['id'])) {
    $roomId = $_GET['id'];

    // Fetch room data from the database
    $sql = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();

    if (!$room) {
        echo "Room not found";
        exit();
    }
} else {
    echo "No room ID provided";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Room Details</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        #floorPlan {
            height: auto; /* Allow automatic height based on content */
            width: 100%; /* Full width */
            border-radius: 5px;
            margin-top: 15px;
        }
        .room-info {
            border: 1px solid #ced4da;
            border-radius: 5px;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .btn-back {
            margin-bottom: 20px; /* Add spacing below the button */
        }

        /* Zoom effect on hover */
        .carousel-item img {
            transition: transform 0.3s ease-in-out;
            border-radius: 5px;
        }

        .carousel-item img:hover {
            transform: scale(1.1);
            cursor: pointer;
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
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="search_rooms.php">Search Rooms</a></li>
            <li class="nav-item"><a class="nav-link" href="index2.php">Dashboard</a></li>
            <?php if (isset($_SESSION['userId'])): ?>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="container">
    <h1 class="mt-5 mb-4 text-center">Room <?php echo htmlspecialchars($room['room_number']); ?> Details</h1>

    <!-- Back Button -->
    

    <div class="row">
        <div class="col-md-4">
            <!-- Carousel for Room Images -->
            <div id="roomCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    $first = true; // Flag to track the first image
                    for ($i = 1; $i <= 4; $i++) {
                        $imageUrl = $room['image_url_' . $i];
                        if (!empty($imageUrl)) {
                            echo '<div class="carousel-item ' . ($first ? 'active' : '') . '">
                                    <img src="' . htmlspecialchars($imageUrl) . '" alt="Room Image ' . $i . '" class="d-block w-100">
                                  </div>';
                            $first = false; // Set the flag to false after the first image
                        }
                    }
                    ?>
                </div>
                <a class="carousel-control-prev" href="#roomCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#roomCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="room-info">
                <p><strong>Type:</strong> <?php echo htmlspecialchars($room['room_type']); ?></p>
                <p><strong>Price:</strong> ฿<?php echo htmlspecialchars(number_format($room['price'], 2)); ?></p>
                <p class="card-text">
                            Status: 
                            <span class="<?php 
                                echo $room['status'] == 'available' ? 'text-success' : 
                                    ($room['status'] == 'rented' ? 'text-danger' : 
                                    ($room['status'] == 'pending' ? 'text-warning' : 'text-muted')); 
                            ?>">
                                <?php echo htmlspecialchars($room['status']); ?>
                            </span>
                        </p>
                <p><strong>Furniture:</strong> <?php echo htmlspecialchars($room['furniture']); ?></p>
                <p><strong>Details:</strong> <?php echo htmlspecialchars($room['details']); ?></p>
            </div>
            <div class="text-center">
        <a href="view_rooms.php" class="btn btn-secondary btn-back mx-5">Back to Room List</a>
        <a href="search_rooms.php" class="btn btn-secondary btn-back">Booking</a>
    </div>
        </div>
    </div>

    <h3>Floor Plan</h3>
    <div class="text-center">
        <?php if (!empty($room['floor_plan_url'])): ?>
            <img id="floorPlan" src="<?php echo htmlspecialchars($room['floor_plan_url']); ?>" alt="Floor Plan" class="img-fluid border border-secondary ">
        <?php else: ?>
            <p>No floor plan available.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
