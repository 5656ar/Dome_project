<?php
include 'connect.php';
session_start(); // Start the session

// Check if session variables are set
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];

// Fetch user data from the database to get the latest information
$stmt = $conn->prepare("SELECT firstName, lastName, gender, email, number FROM registration WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$row = $result->fetch_assoc();

// Store data in session variables for easy access
$_SESSION['firstName'] = $row['firstName'];
$_SESSION['lastName'] = $row['lastName'];
$_SESSION['email'] = $row['email'];
$_SESSION['number'] = $row['number'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Welcome Page</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 100px;
        }
    </style>
</head>
<body>
<div class="container text-center">
    <h1 class="welcome-message">Welcome to Your Dashboard!</h1>
    <h3><?php echo htmlspecialchars($_SESSION['firstName']) . " " . htmlspecialchars($_SESSION['lastName']); ?></h3>
    <h3><?php echo "Your email is: " . htmlspecialchars($_SESSION['email']); ?></h3>
    <h3><?php echo "Your ID is: " . htmlspecialchars($_SESSION['userId']); ?></h3>
    <p>You are logged in.</p>
    
    <form action="logout.php" method="post" onsubmit="return confirm('Are you sure you want to log out?');">
        <button type="submit" class="btn btn-danger" aria-label="Logout">Get Out</button>
    </form>

    <!-- Add Edit Profile Button -->
    <a href="edit_profile.php" class="btn btn-primary mt-3">Edit Profile</a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
