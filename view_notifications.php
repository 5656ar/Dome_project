<?php
include 'connect.php'; // Include database connection
session_start();

// Check if user is Admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle delete notification
if (isset($_GET['delete_id']) && ctype_digit($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $_SESSION['successMessage'] = "Notification deleted successfully!";
    } else {
        $_SESSION['errorMessage'] = "Failed to delete the notification.";
    }
    
    header("Location: view_notifications.php");
    exit();
}

// Handle toggle visibility
if (isset($_GET['toggle_visibility_id']) && ctype_digit($_GET['toggle_visibility_id'])) {
    $toggle_id = (int)$_GET['toggle_visibility_id'];

    // Get current visibility status
    $stmt = $conn->prepare("SELECT is_visible FROM notifications WHERE id = ?");
    $stmt->bind_param("i", $toggle_id);
    $stmt->execute();
    $stmt->bind_result($is_visible);
    $stmt->fetch();
    $stmt->close();

    // Toggle visibility
    $new_visibility = $is_visible ? 0 : 1;
    $stmt = $conn->prepare("UPDATE notifications SET is_visible = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_visibility, $toggle_id);

    if ($stmt->execute()) {
        $_SESSION['successMessage'] = "Notification visibility updated successfully!";
    } else {
        $_SESSION['errorMessage'] = "Failed to update notification visibility.";
    }

    header("Location: view_notifications.php");
    exit();
}

// Fetch all notifications from the database
$sql = "SELECT * FROM notifications ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Notifications</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; margin: 0; display: flex; }
        .sidebar { height: 100vh; width: 220px; background-color: #343a40; padding: 20px; position: fixed; }
        .sidebar h2 { color: white; margin-bottom: 20px; }
        .sidebar a { color: white; text-decoration: none; padding: 10px; display: block; transition: background-color 0.3s; }
        .sidebar a:hover { background-color: #495057; }
        .content { margin-left: 240px; padding: 20px; flex: 1; }
        .container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1); }
        h1 { margin-bottom: 20px; text-align: center; }
        .alert { margin-top: 20px; }
        .table td, .table th { vertical-align: middle; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Menu</h2>
    <a href="index.php">Back To Home Page</a>
    <a href="revenue_chart.php">Dashboard</a>
    <a href="show_users.php">Manage Users</a>
    <a href="show_rooms.php">Manage Rooms</a>
    <a href="confirm_bookings.php">Confirm bookings</a>
    <a href="view_utilities.php">View Utilities</a>
    <a href="set_utilities.php">Set Utilities</a>
    <a href="create_notification.php">Manage Notifications</a>
    <a href="view_notifications.php">View Notifications</a>
    <a href="admin_dashboard.php">Confirm Payment</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
</div>

<div class="content">
    <div class="container">
        <h1>All Notifications</h1>

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['successMessage'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['successMessage']; unset($_SESSION['successMessage']); ?>
            </div>
        <?php elseif (isset($_SESSION['errorMessage'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['errorMessage']; unset($_SESSION['errorMessage']); ?>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Delivery Method</th>
                        <th>Date Created</th>
                        <th>Visibility</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['content']); ?></td>
                            <td><?php echo htmlspecialchars($row['delivery_method']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td><?php echo $row['is_visible'] ? 'Visible' : 'Hidden'; ?></td>
                            <td>
                                <a href="view_notifications.php?delete_id=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this notification?');">
                                   Delete
                                </a>
                                <a href="view_notifications.php?toggle_visibility_id=<?php echo $row['id']; ?>" 
                                   class="btn btn-warning btn-sm">
                                   <?php echo $row['is_visible'] ? 'Hide' : 'Show'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No notifications found.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
