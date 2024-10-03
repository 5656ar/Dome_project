<?php
include 'connect.php';
session_start(); // Start the session

// Check if session variables are set
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];

// Fetch user data from the database
$stmt = $conn->prepare("SELECT firstName, lastName, gender, email, number FROM registration WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>User not found.</div>";
    exit();
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>Edit Profile</h2>
    <form action="update_profile.php" method="post">
        <input type="hidden" name="userId" value="<?php echo $userId; ?>">
        <div class="form-group">
            <label for="firstName">First Name</label>
            <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>
        </div>
        <div class="form-group">
            <label for="lastName">Last Name</label>
            <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required>
        </div>
        <div class="form-group">
            <label for="gender">Gender</label>
            <select class="form-control" id="gender" name="gender" required>
                <option value="m" <?php echo $user['gender'] === 'm' ? 'selected' : ''; ?>>Male</option>
                <option value="f" <?php echo $user['gender'] === 'f' ? 'selected' : ''; ?>>Female</option>
                <option value="o" <?php echo $user['gender'] === 'o' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="number">Phone Number</label>
            <input type="text" class="form-control" id="number" name="number" value="<?php echo htmlspecialchars($user['number']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
