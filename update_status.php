<?php
include 'connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roomId = $_POST['room_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE rooms SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $roomId);

    if ($stmt->execute()) {
        echo "Room status updated successfully!";
    } else {
        echo "Error updating room status: " . $conn->error;
    }
}

// Fetch room details for status update
if (isset($_GET['room_id'])) {
    $roomId = $_GET['room_id'];
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Update Room Status</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Update Room Status</h1>
    <form action="update_status.php" method="post">
        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="status">
                <option value="available" <?php if($room['status'] == 'available') echo 'selected'; ?>>Available</option>
                <option value="rented" <?php if($room['status'] == 'rented') echo 'selected'; ?>>Rented</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Status</button>
    </form>
</div>
</body>
</html>
