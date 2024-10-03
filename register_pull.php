<?php
    include 'connect.php';

	$firstName = $_POST['firstName'];
	$lastName = $_POST['lastName'];
	$gender = $_POST['gender'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$number = $_POST['number'];



    $spl = "INSERT INTO registration(firstName, lastName, gender, email, password, number) values('$firstName', '$lastName', '$gender', '$email', '$password', '$number')";
    $result=mysqli_query($conn,$spl);
    if($result){
        $_SESSION['register_success'] = "Registration completed successfully!";
        header("Location: login.php");
        exit();
    }else{
        echo "Can't Save";
    }
    mysqli_close($conn)
    
?>