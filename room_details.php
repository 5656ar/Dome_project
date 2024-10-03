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
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
    <style>
        #map {
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo $room['room_number']; ?> Details</h1>

    <!-- Show all images -->
    <div class="row">
        <?php
        for ($i = 1; $i <= 4; $i++) {
            $imageUrl = $room['image_url_' . $i];
            if (!empty($imageUrl)) {
                echo '<div class="col-md-3">
                        <img src="' . $imageUrl . '" alt="Room Image ' . $i . '" class="img-fluid" style="margin-bottom: 15px;">
                      </div>';
            }
        }
        ?>
    </div>

    <p><strong>Type:</strong> <?php echo $room['room_type']; ?></p>
    <p><strong>Price:</strong> ฿<?php echo $room['price']; ?></p>
    <p><strong>Status:</strong> <?php echo $room['status']; ?></p>
    <p><strong>Furniture:</strong> <?php echo $room['furniture']; ?></p>
    <p><strong>Details:</strong> <?php echo $room['details']; ?></p>

    <h3>Location</h3>
    <div id="map"></div>

    <script>
        function initMap() {
            // Define the location (replace with actual coordinates if available)
            var roomLocation = { lat: <?php echo $room['latitude']; ?>, lng: <?php echo $room['longitude']; ?> }; // Replace with actual coordinates
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: roomLocation
            });
            var marker = new google.maps.Marker({
                position: roomLocation,
                map: map
            });
        }
        window.onload = initMap;
    </script>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="search_rooms.php" class="btn btn-secondary">Back to Room List</a>
    </div>
</div>
</body>
</html>
