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

// Handle adding new user
if (isset($_POST['add_user'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $number = $_POST['number'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password for security

    // Insert new user into the database
    $insert_query = "INSERT INTO registration (firstName, lastName, gender, email, number, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssss", $firstName, $lastName, $gender, $email, $number, $password);

    if ($stmt->execute()) {
        header("Location: show_users.php?msg=User added successfully");
        exit();
    } else {
        echo "Error adding user: " . $conn->error;
    }
}

// Set default query to fetch all users
$query = "SELECT * FROM registration";
$conditions = [];

// Check if filter by email or ID is applied
if (!empty($_POST['filter_email'])) {
    $filter_email = $_POST['filter_email'];
    $conditions[] = "email LIKE '%$filter_email%'";
}
if (!empty($_POST['filter_id'])) {
    $filter_id = (int)$_POST['filter_id'];
    $conditions[] = "id = $filter_id";
}

// Combine conditions if any filters are applied
if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
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
            height: 100%;
            background-color: #343a40;
            padding: 20px;
            position: fixed;
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
            margin-left: 240px;
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
        <h1 class="text-center">Registered Users</h1>

        <!-- Display success message if user deleted or added -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Button to open modal for adding new user -->
        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addUserModal">
            Add New User
        </button>

        <!-- Filter Form -->
        <form action="" method="POST" class="form-inline mb-4">
            <div class="form-group mx-sm-3 mb-2">
                <label for="filter_email" class="sr-only">Email Domain</label>
                <input type="text" name="filter_email" class="form-control" id="filter_email" placeholder="Enter email domain (e.g., gmail.com)" value="<?php echo isset($filter_email) ? htmlspecialchars($filter_email) : ''; ?>">
            </div>
            <div class="form-group mx-sm-3 mb-2">
                <label for="filter_id" class="sr-only">User ID</label>
                <input type="text" name="filter_id" class="form-control" id="filter_id" placeholder="Enter User ID" value="<?php echo isset($filter_id) ? htmlspecialchars($filter_id) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary mb-2">Filter</button>
        </form>

        <!-- Table container for responsive display -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Gender</th>
                        <th>Email</th>
                        <th>Number</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through to display each user
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['password']) . "</td>";
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
</div>

<!-- Modal for Adding User -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select class="form-control" id="gender" name="gender" required>
                            <option value="m">Male</option>
                            <option value="f">Female</option>
                            <option value="o">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="number">Phone Number</label>
                        <input type="text" class="form-control" id="number" name="number" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-success">Add User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
