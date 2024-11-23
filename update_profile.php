<?php
include 'connect.php';
session_start(); // Start the session

// Check if session variable is set
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_POST['userId'];
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$gender = $_POST['gender'];
$email = $_POST['email'];
$number = $_POST['number'];
$password = $_POST['password'];



// Prepare an SQL statement to prevent SQL injection
$stmt = $conn->prepare("UPDATE registration SET firstName=?, lastName=?, gender=?, email=?, password=?, number=? WHERE id=?");

if ($stmt === false) {
    // Output error if preparation failed
    die("MySQL prepare statement failed: " . $conn->error);
}

$stmt->bind_param("sssssii", $firstName, $lastName, $gender, $email, $password, $number, $userId);

// Execute the statement
if ($stmt->execute()) {
    $_SESSION['update_success'] = "Profile updated successfully!";
    header("Location: index2.php");
    exit();
} else {
    echo "Error updating profile: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
$conn->close();
?>