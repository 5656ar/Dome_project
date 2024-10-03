<?php
include 'connect.php';
session_start(); 

// Check if the user is logged in
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit();
// }

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roomNumber = $_POST['room_number'];
    $roomType = $_POST['room_type'];
    $price = $_POST['price'];
    $furniture = $_POST['furniture'];
    $status = $_POST['status'];
    $details = $_POST['details'];

    // Handle image uploads
    $targetDir = "uploads/";
    $imageUrls = [];

    // Loop to handle multiple images
    for ($i = 0; $i < count($_FILES["room_image"]["name"]); $i++) {
        $targetFile = $targetDir . basename($_FILES["room_image"]["name"][$i]);
        if (move_uploaded_file($_FILES["room_image"]["tmp_name"][$i], $targetFile)) {
            $imageUrls[] = $targetFile;
        } else {
            echo "Error uploading image " . ($_FILES["room_image"]["name"][$i]) . ".<br>";
        }
    }

    // Prepare SQL statement for adding a room
    $img1 = $imageUrls[0] ?? null;
    $img2 = $imageUrls[1] ?? null;
    $img3 = $imageUrls[2] ?? null;
    $img4 = $imageUrls[3] ?? null;

    $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, price, furniture, status, details, image_url_1, image_url_2, image_url_3, image_url_4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind parameters
    $stmt->bind_param("ssdsssssii", $roomNumber, $roomType, $price, $furniture, $status, $details, $img1, $img2, $img3, $img4);

    if ($stmt->execute()) {
        echo "Room added successfully!";
    } else {
        echo "Error adding room: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Add New Room</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Add New Room</h1>
    <div class="mb-3">
        <a href="show_rooms.php" class="btn btn-primary">show Room</a>
    </div>
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
            <label>Details</label>
            <textarea class="form-control" name="details" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add Room</button>
    </form>
</div>
</body>
</html>
