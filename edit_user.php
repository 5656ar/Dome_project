<?php
include 'connect.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

// เริ่มต้นเซสชัน
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่ (ตามต้องการ)


// รับ ID ผู้ใช้จาก URL
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    
    // ดึงข้อมูลผู้ใช้
    $query = "SELECT * FROM registration WHERE id = $userId";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        die("User not found.");
    }

    $user = mysqli_fetch_assoc($result);
} else {
    die("No user ID provided.");
}

// จัดการการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = mysqli_real_escape_string($conn, trim($_POST['firstName']));
    $lastName = mysqli_real_escape_string($conn, trim($_POST['lastName']));
    $gender = mysqli_real_escape_string($conn, trim($_POST['gender']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $number = mysqli_real_escape_string($conn, trim($_POST['number']));
    
    // อัปเดตข้อมูลผู้ใช้
    $updateQuery = "UPDATE registration SET firstName='$firstName', lastName='$lastName', gender='$gender', email='$email', number='$number' WHERE id=$userId";
    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        $_SESSION['update_success'] = "User updated successfully!";
        header("Location: show_users.php");
        exit();
    } else {
        echo "Error updating user: " . mysqli_error($conn);
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit User</title>
</head>
<style>
        /* styles.css */

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            color: #333;
        }

        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Ensures padding and border are included in total width */
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
        }

        .btn-back:hover {
            background-color: #5a6268; /* Darker gray on hover */
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }

</style>
<body>

<h1>Edit User</h1>
<a href="show_users.php" class="btn btn-secondary mb-3">Back to User List</a>

<!-- ฟอร์มแก้ไขผู้ใช้ -->
<form method="post" action="">
    <label for="firstName">First Name:</label>
    <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required><br>

    <label for="lastName">Last Name:</label>
    <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required><br>

    <label for="gender">Gender:</label>
    <select id="gender" name="gender" required>
        <option value="m" <?php if ($user['gender'] == 'Male') echo 'selected'; ?>>Male</option>
        <option value="f" <?php if ($user['gender'] == 'Female') echo 'selected'; ?>>Female</option>
        <option value="o" <?php if ($user['gender'] == 'Other') echo 'selected'; ?>>Other</option>
    </select><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

    <label for="number">Phone Number:</label>
    <input type="text" id="number" name="number" value="<?php echo htmlspecialchars($user['number']); ?>" required><br>

    <input type="submit" value="Update User">
</form>



</body>
</html>
