<?php
include 'connect.php';
session_start(); // Ensure session is started before using it

$email = $_POST['email'];
$password = $_POST['password'];



$sql = "SELECT * FROM registration WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn, $sql);

// Check if the query was successful
if (!$result) {
    // If the query fails, display the error
    die("Query failed: " . mysqli_error($conn));
}

$row = mysqli_fetch_array($result);

if ($row > 0) {
    // Set session variables

    $userId = $row['id']; // This should be the ID of the user from your database
    $_SESSION['userId'] = $row['id'] ;// Store userId in session
    $_SESSION['firstName'] = $row['firstName'];
    $_SESSION['lastName'] = $row['lastName'];
    $_SESSION['email'] = $row['email'];
    
    
    // Redirect to index2.html
    $show=header("Location: index2.php");
    exit(); // Prevent further script execution after redirection
} else {
    // Set an error session variable and output a message
    $_SESSION['login_error'] = 'Invalid email or password';
    $show=header('Location: login.php');
    exit(); // Prevent further script execution after redirection
}
echo $show()
?>
