<?php
include 'connect.php';
session_start(); // Ensure session is started before using it

$email = $_POST['email'];
$password = $_POST['password'];

// Prepare the SQL query using prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM registration WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if the query returned a result
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Verify the password (if using hashed passwords)
    if ($row && $password === $row['password']) { // You should use password_verify() if the password is hashed
        // Set session variables
        $_SESSION['userId'] = $row['id']; // Store userId in session
        $_SESSION['firstName'] = $row['firstName'];
        $_SESSION['lastName'] = $row['lastName'];
        $_SESSION['email'] = $row['email'];
        
        // Redirect to index2.php
        header("Location: index.php");
        exit(); // Prevent further script execution after redirection
    } else {
        // Set an error session variable if password doesn't match
        $_SESSION['login_error'] = 'Invalid email or password';
        header('Location: login.php');
        exit();
    }
} else {
    // Set an error session variable if email not found
    $_SESSION['login_error'] = 'Invalid email or password';
    header('Location: login.php');
    exit();
}
?>
