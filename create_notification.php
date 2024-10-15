<?php
include 'connect.php'; // Include database connection
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $delivery_method = $_POST['delivery_method'];

    $sql = "INSERT INTO notifications (title, content, delivery_method) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $content, $delivery_method);
    $stmt->execute();

    if ($delivery_method == 'line' || $delivery_method == 'both') {
        // Send notification to Line group (need to integrate Line API)
        sendLineNotification($title, $content);
    }

    // Optional: echo a success message
    $successMessage = "Notification sent successfully!";
}

function sendLineNotification($title, $content) {
    // Use Line API to send the message
    $line_token = 'ZNyhAfnGat1mPbz0PMJLnnYMLefB1QcV56B200ndGgE'; // Replace with your actual Line token
    $message = $title . "\n" . $content;

    $data = [
        'message' => $message,
    ];

    $ch = curl_init("https://notify-api.line.me/api/notify");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Bearer ' . $line_token
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Create Notification</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex; /* Use flex layout */
        }
        .sidebar {
            height: 100vh; /* Full height for sidebar */
            width: 220px; /* Fixed width for sidebar */
            background-color: #343a40; /* Dark background */
            padding: 20px;
            position: fixed; /* Fix position to the left */
        }
        .sidebar h2 {
            color: white;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 240px; /* Leave space for sidebar */
            padding: 20px;
            flex: 1;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Menu</h2>
    <a href="home.php">Back To Home Page</a>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="show_users.php">Manage Users</a>
    <a href="show_rooms.php">Manage Rooms</a>
    <a href="confirm_bookings.php">Confirm bookings</a>
    <a href="view_utilities.php">View Utilities</a>
    <a href="set_utilities.php">Set Utilities</a>
    <a href="create_notification.php">Manage Notifications</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
</div>

<div class="content">
    <div class="container">
        <h1>Create Notification</h1>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <form action="create_notification.php" method="post">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea class="form-control" name="content" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="delivery_method">Delivery Method</label>
                <select class="form-control" name="delivery_method" required>
                    <option value="website">Website</option>
                    <option value="line">Line</option>
                    <option value="both">Both</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Notification</button>
        </form>

        <!-- Back Button -->

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
