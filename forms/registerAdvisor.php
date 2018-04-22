<!DOCTYPE html>
<?php
session_start();

if ($_POST) {
    include '../dbconfig.php';

    // Parse values from form
    $fName = $_POST["fName"];
    $lName = $_POST["lName"];
    $email = $_POST["email"];
    $bldgName = $_POST["bldgName"];
    $officeRm = $_POST["officeRm"];
	$password = $_POST["password"];
	
	// A higher "cost" is more secure but consumes more processing power
	$cost = 10;
	
	// Create a random salt
	//$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
	//$salt = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
	// Prefix information about the hash so PHP knows how to verify it later.
	// "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
	//$salt = sprintf("$2a$%02d$", $cost) . $salt;
	
	// Hash the password with the salt
	$password = md5($password);
	
	// Connect to DB
    $open_connection = connectToDB();
    $checkForEmails = "SELECT 1 from `Advisor` WHERE `email` = '$email' LIMIT 1";
	$results = $open_connection->query($checkForEmails);
	
	//validates that the email is not already in use, then creates a new row for the advisor
    if (mysqli_num_rows($results) == 0) {
		$insert_advisor = "
			INSERT INTO Advisor (
				email, password, firstName, lastName, buildingName, roomNumber
			)
			VALUES (
				'$email', '$password', '$fName', '$lName', '$bldgName', '$officeRm'
			)
			";
		$open_connection->query($insert_advisor);
		header('Location: ../../views/login.php');
    } 
	else {
		$_SESSION["ERROR_ADVISOR_REGISTRATION_EMAIL"] = "Error: This email already exists!";
		header('Location: ../../views/index.php');
	}
}