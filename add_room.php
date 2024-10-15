<?php
include 'connect.php'; // Connect to the database
session_start(); 

// Check if the user is Admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission to add a new room
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gather room data from the form
    $roomNumber = $_POST['room_number'];
    $roomType = $_POST['room_type'];
    $price = $_POST['price'];
    $furniture = $_POST['furniture'];
    $status = $_POST['status'];
    $details = $_POST['details'];

    // Handle multiple image uploads
    $imageUrls = [];
    for ($i = 0; $i < 4; $i++) {
        if (isset($_FILES['room_image']['name'][$i]) && $_FILES['room_image']['error'][$i] == 0) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES["room_image"]["name"][$i]);
            move_uploaded_file($_FILES["room_image"]["tmp_name"][$i], $targetFile);
            $imageUrls[] = $targetFile;
        } else {
            $imageUrls[] = null; // No image uploaded for this slot
        }
    }

    // Handle floor plan image upload
    if (isset($_FILES['floor_plan']) && $_FILES['floor_plan']['error'] == 0) {
        $floorPlanDir = "uploads/";
        $floorPlanFile = $floorPlanDir . basename($_FILES["floor_plan"]["name"]);
        move_uploaded_file($_FILES["floor_plan"]["tmp_name"], $floorPlanFile);
        $floorPlanUrl = $floorPlanFile;
    } else {
        $floorPlanUrl = null; // No floor plan uploaded
    }

    // Prepare SQL statement to insert new room
    $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, price, furniture, status, image_url_1, image_url_2, image_url_3, image_url_4, details, floor_plan_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssssssss", $roomNumber, $roomType, $price, $furniture, $status, $imageUrls[0], $imageUrls[1], $imageUrls[2], $imageUrls[3], $details, $floorPlanUrl);

    if ($stmt->execute()) {
        header("Location: show_rooms.php"); // Redirect to show_rooms.php after successful addition
        exit();
    } else {
        echo "Error adding room: " . $conn->error;
    }
}
?>
