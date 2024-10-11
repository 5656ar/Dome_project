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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Home Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .hero {
            background-color: #007bff;
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        .hero h1 {
            font-size: 3rem;
        }
        .hero p {
            font-size: 1.5rem;
        }
        .features {
            margin-top: 50px;
        }
        .features h2 {
            text-align: center;
            margin-bottom: 40px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
        }
        .card:hover {
            transform: scale(1.05);
            transition: transform 0.2s;
        }
        .notification-banner {
            background-color: #ffc107;
            padding: 20px;
            text-align: center;
            font-weight: bold;
            color: #000;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            margin: 20px auto;
            max-width: 80%;
            animation: slide-down 0.5s ease-in-out;
            position: relative;
        }

        .notification-banner p {
            margin: 0;
            font-size: 1.2rem;
        }

        .notification-banner p:first-child {
            font-size: 1.5rem;
            font-weight: 700;
            color: #d9534f;
        }

        @keyframes slide-down {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to Our Website</h1>
            <p>Your journey starts here. Explore the best rooms and services available for your next adventure.</p>
            <a href="search_rooms.php" class="btn btn-light btn-lg">Search Rooms</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Our Services</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-4">
                        <div class="card-body text-center">
                            <h4 class="card-title">Room Search</h4>
                            <p class="card-text">Find the perfect room for your stay with our easy-to-use search system.</p>
                            <a href="search_rooms.php" class="btn btn-primary">Explore Rooms</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4">
                        <div class="card-body text-center">
                            <h4 class="card-title">Easy Registration</h4>
                            <p class="card-text">Sign up today to get access to exclusive offers and personalized services.</p>
                            <a href="register.php" class="btn btn-primary">Register Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4">
                        <div class="card-body text-center">
                            <h4 class="card-title">User Support</h4>
                            <p class="card-text">Need help? Our team is here to assist you 24/7 for any inquiries.</p>
                            <a href="#" class="btn btn-primary">Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Notification Banner -->
    <?php if ($latestNotification): ?>
        <div class="notification-banner">
            <p>🔔 Latest Notification</p>
            <p><?php echo $latestNotification['content']; ?></p>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p>&copy; 2024 Your Website. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
