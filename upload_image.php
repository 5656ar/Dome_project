<?php
include 'connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roomId = $_POST['room_id'];
    $details = $_POST['details'];

    // Handle Image Upload
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["room_image"]["name"]);
    if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $targetFile)) {
        $stmt = $conn->prepare("UPDATE rooms SET image_url = ?, details = ? WHERE id = ?");
        $stmt->bind_param("ssi", $targetFile, $details, $roomId);

        if ($stmt->execute()) {
            echo "Image and details uploaded successfully!";
        } else {
            echo "Error updating room: " . $conn->error;
        }
    } else {
        echo "Error uploading file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Upload Room Image</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Upload Room Image and Details</h1>
    <form action="upload_image.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="room_id" value="<?php echo $_GET['room_id']; ?>">
        <div class="form-group">
            <label>Room Image</label>
            <input type="file" class="form-control" name="room_image" required>
        </div>
        <div class="form-group">
            <label>Details</label>
            <textarea class="form-control" name="details" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
</body>
</html>
