<?php
include 'connect.php'; // Include database connection

// Start session
session_start();

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Delete user query
    $delete_query = "DELETE FROM registration WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        // Redirect to avoid form resubmission
        header("Location: show_users.php?msg=User deleted successfully");
        exit();
    } else {
        echo "Error deleting user: " . $conn->error;
    }
}

// Check if filter is applied
$filter = "";
if (isset($_POST['filter_email'])) {
    $filter = $_POST['filter_email'];
    $query = "SELECT * FROM registration WHERE email LIKE '%$filter%'";
} else {
    // Fetch all users if no filter
    $query = "SELECT * FROM registration";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .sidebar {
            height: 100%; /* Full height for sidebar */
            background-color: #343a40;
            padding: 20px;
            position: fixed; /* Fix to the left */
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
            margin-left: 240px; /* Leave space for the sidebar */
            padding: 20px;
            flex: 1;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-top: 20px;
        }
        table {
            margin-top: 20px;
        }
        th, td {
            vertical-align: middle;
        }
        tr:hover {
            background-color: #f1f1f1;
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
</div>

<div class="content">
    <div class="container">
        <h1 class="text-center">Registered Users</h1>

        <!-- Display success message if user deleted -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Filter Form -->
        <form action="" method="POST" class="form-inline mb-4">
            <div class="form-group mx-sm-3 mb-2">
                <label for="filter_email" class="sr-only">Email Domain</label>
                <input type="text" name="filter_email" class="form-control" id="filter_email" placeholder="Enter email domain (e.g., gmail.com)" value="<?php echo htmlspecialchars($filter); ?>">
            </div>
            <button type="submit" class="btn btn-primary mb-2">Filter</button>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th> <!-- ID Column -->
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Number</th>
                    <th>Password</th> <!-- Password Column -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through to display each user
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>"; // Show ID
                    echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['password']) . "</td>"; // Show Password
                    echo "<td>";
                    echo "<a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Edit</a> ";
                    echo "<a href='show_users.php?delete_id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Close database connection
mysqli_close($conn);
?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
