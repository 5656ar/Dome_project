<?php
include 'connect.php';
session_start();

// Fetch the latest notification
$sql = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);
$latestNotification = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Home Page</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .notification-banner {
            background-color: #ffc107;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="#">Your Website</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="home.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="search_rooms.php">Search Rooms</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="register.php">Register</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="login.php">Login</a>
            </li>
        </ul>
    </div>
</nav>

<!-- Notification Banner -->
<?php if ($latestNotification): ?>
    <div class="notification-banner">
        <p>🔔 Latest Notification: <?php echo $latestNotification['title']; ?></p>
        <p><?php echo $latestNotification['content']; ?></p>
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to Our Website</h1>
        <p>Your journey starts here. Explore the best rooms and services available for your next adventure.</p>
        <a href="search_rooms.php" class="btn btn-light btn-lg">Search Rooms</a>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4 mt-5">
    <p>&copy; 2024 Your Website. All Rights Reserved.</p>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
