<?php
include 'connect.php';
session_start(); 

// Check if user is logged in
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit();
// }

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roomNumber = $_POST['room_number'];
    $roomType = $_POST['room_type'];
    $price = $_POST['price'];
    $furniture = $_POST['furniture'];
    $status = $_POST['status'];
    $details = $_POST['details'];

    // Initialize an array to hold image URLs
    $imageUrls = [
        $room['image_url_1'],
        $room['image_url_2'],
        $room['image_url_3'],
        $room['image_url_4']
    ];

    // Check which images are selected for deletion
    $imagesToDelete = isset($_POST['delete_images']) ? $_POST['delete_images'] : [];

    foreach ($imagesToDelete as $imageIndex) {
        // Clear the image URL from the array
        $imageUrls[$imageIndex] = '';
        
        // Delete the file from the server if it exists
        if (!empty($room['image_url_' . ($imageIndex + 1)])) {
            unlink($room['image_url_' . ($imageIndex + 1)]);
        }
    }

    // Handle multiple image uploads
    $totalFiles = count($_FILES['room_images']['name']);
    $currentImageCount = 0;

    // Count existing images
    foreach ($imageUrls as $imageUrl) {
        if (!empty($imageUrl)) {
            $currentImageCount++;
        }
    }

    // Calculate how many more images can be uploaded
    $remainingSlots = 4 - $currentImageCount;

    for ($i = 0; $i < $totalFiles && $i < $remainingSlots; $i++) {
        if ($_FILES['room_images']['error'][$i] == 0) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES["room_images"]["name"][$i]);
            if (move_uploaded_file($_FILES["room_images"]["tmp_name"][$i], $targetFile)) {
                $imageUrls[$currentImageCount] = $targetFile;
                $currentImageCount++;
            }
        }
    }

    // Prepare SQL statement to update room details
    $stmt = $conn->prepare("UPDATE rooms SET room_number = ?, room_type = ?, price = ?, furniture = ?, status = ?, image_url_1 = ?, image_url_2 = ?, image_url_3 = ?, image_url_4 = ?, details = ? WHERE id = ?");
    $stmt->bind_param("ssdsssssssi", $roomNumber, $roomType, $price, $furniture, $status, $imageUrls[0], $imageUrls[1], $imageUrls[2], $imageUrls[3], $details, $roomId);

    if ($stmt->execute()) {
        echo "Room updated successfully!";
        header("Location: show_rooms.php");
        exit();
    } else {
        echo "Error updating room: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Room</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Edit Room</h1>
    <form action="edit_room.php?id=<?php echo $roomId; ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Room Number</label>
            <input type="text" class="form-control" name="room_number" value="<?php echo $room['room_number']; ?>" required>
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
            <input type="number" class="form-control" name="price" step="0.01" value="<?php echo $room['price']; ?>" required>
        </div>
        <div class="form-group">
            <label>Furniture</label>
            <textarea class="form-control" name="furniture" required><?php echo $room['furniture']; ?></textarea>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="status">
                <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                <option value="rented" <?php echo $room['status'] == 'rented' ? 'selected' : ''; ?>>Rented</option>
            </select>
        </div>
        <div class="form-group">
            <label>Room Images (Max 4 images)</label>
            <input type="file" class="form-control" name="room_images[]" multiple>
            <div>
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <?php if (!empty($room['image_url_' . $i])): ?>
                        <div>
                            <img src="<?php echo $room['image_url_' . $i]; ?>" alt="Current Image" style="width:100px;height:auto; margin-right:5px;">
                            <label><input type="checkbox" name="delete_images[]" value="<?php echo $i - 1; ?>"> Delete</label>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>
        <div class="form-group">
            <label>Details</label>
            <textarea class="form-control" name="details" required><?php echo $room['details']; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Room</button>
    </form>
</div>
</body>
</html>
