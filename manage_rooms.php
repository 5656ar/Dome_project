<?php
include 'connect.php';
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบในฐานะ Admin หรือไม่
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle the cancel rent request
if (isset($_POST['cancel_rent'])) {
    $room_id = $_POST['room_id'];

    // อัปเดตห้องเพื่อยกเลิกการเช่า
    $cancelQuery = "UPDATE rooms SET status = 'available', rented_by = NULL WHERE id = ?";
    $stmt = $conn->prepare($cancelQuery);
    $stmt->bind_param("i", $room_id);

    if ($stmt->execute()) {
        $cancelSuccess = "Rent canceled successfully!";
    } else {
        $cancelError = "Error: Could not cancel the rent.";
    }
}

// ดึงข้อมูลห้องทั้งหมดเพื่อแสดงในหน้า
$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manage Rooms</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h1>Manage Rooms</h1>

    <?php if (isset($cancelSuccess)): ?>
        <div class="alert alert-success"><?php echo $cancelSuccess; ?></div>
    <?php elseif (isset($cancelError)): ?>
        <div class="alert alert-danger"><?php echo $cancelError; ?></div>
    <?php endif; ?>

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

                        <?php if ($room['status'] == 'rented'): ?>
                            <p class="text-danger">Rented by User ID: <?php echo $room['rented_by']; ?></p>
                            <!-- ปุ่มยกเลิกการเช่า จะปรากฏเฉพาะห้องที่ถูกเช่า -->
                            <form action="manage_rooms.php" method="post">
                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                <button type="submit" name="cancel_rent" class="btn btn-danger">Cancel Rent</button>
                            </form>
                        <?php else: ?>
                            <p class="text-success">Available for rent</p>
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
