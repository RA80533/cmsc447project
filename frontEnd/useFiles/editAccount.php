<?php

session_start();

if ($_SESSION["HAS_LOGGED_IN"] and $_POST) {
    include '../dbconfig.php';
	$fileName = "editAdvisor.php";
	
	//initialization of variables
	//post validation
	$open_connection = connectToDB();
	
	//session variable needed
	$email = $_SESSION["ADVISOR_EMAIL"];
	$id = $_SESSION["ADVISOR_ID"];
	
	//values posted from homepage.php
	$emailpost = $_POST["email"];
	$fnamepost = $_POST["fName"];  
	$lnamepost = $_POST["lName"];  
	$bldgNamepost = $_POST["bldgName"];
	$officeRmpost = $_POST["officeRm"]; 
	$oldPasswordpost= $_POST["oldpassword"]; 
	$newPasswordpost= $_POST["password"]; 
	$passwordConfirmpost = $_POST["confirm_password"]; 
	
	//regex for email validation 
	$email_validation = '/^[A-Za-z0-9_]+@umbc.edu$/';
  
	//boolean to determine if email is invalid
	$invalid_email = false;
 
	//boolean to determine if advisor record exists in db
	$advisor_exists = false;
  
	//query for advisor
	$advisor_val_query = "SELECT * FROM Advisor WHERE email = '$emailpost'";
  
	//boolean to determine if any of the password fields are correct
	$invalid_password = false;
  
	//query execution
	$validation_query = $open_connection->query($advisor_val_query);

  
	//error msgs
	$duplicate_error="";
	$email_error= "";
	$passwordMatch_error ="";
	$query_error="";
  
	//determines if at least one record exists with entered email
	if($email == $emailpost){
	}
	else{
		if(mysqli_num_rows($validation_query) > 0){
			$duplicate_error ="Duplicate, Another advisor currently uses that email"; 
			$advisor_exists = true;
		}
	}
	
	//email validation
	if(!preg_match($email_validation, $emailpost)){
		$email_error = "Invalid Email, We can only accept emails that end in umbc.edu, example: name@umbc.edu";
		$invalid_email = true;
	}
	
	//password validation
	if($newPasswordpost!= "" && $passwordConfirmpost != ""){
		if($newPasswordpost != $passwordConfirmpost) { 
		$passwordMatch_error = "The passwords you entered did not match";
		$invalid_password = true;
		}
		else {
			if($oldPasswordpost != ""){
				//hash old password to check for comparison
				$oldPasswordpost = md5($oldPasswordpost); 
				$advisor_val_query = "SELECT * FROM Advisor WHERE advisorID = '$id' AND password = '$oldPasswordpost'";
				$passwordValidation = $open_connection->query($advisor_val_query); 
				if(mysqli_num_rows($passwordValidation) == 1){
				}
				else{ 
					$passwordMatch_error = "Matching, The old password you entered does not match your actual old password";
					$invalid_password = true; 
				}
			}
		}
	}
 
	//query activity after determining if no errors have occured also update session
	if($invalid_email == false && $advisor_exists == false && $invalid_password == false){
		if($newPasswordpost == "") {
			$sql = "UPDATE `Advisor` SET `firstName` = '$fnamepost', `lastName` = '$lnamepost', `buildingName` = '$bldgNamepost', `roomNumber` = '$officeRmpost', `email` = '$emailpost' WHERE `advisorID` = '$id'"; 
		
			//executes query and redirects to homepage
			if($rs = $open_connection->query($sql)){
				$_SESSION['ADVISOR_FNAME']= $fnamepost;
				$_SESSION['ADVISOR_LNAME']= $lnamepost;
				$_SESSION['ADVISOR_EMAIL']= $emailpost;
				$_SESSION["ADVISOR_BLDG_NAME"] = $bldgNamepost;
				$_SESSION["ADVISOR_RM_NUM"] = $officeRmpost; 
				header('Location: ../../views/homepage.php');
			}
			else{
				header('Location: ../../views/homepage.php');
			}
		}
		else{ 
			$newPasswordpost = md5($newPasswordpost);
			$sql = "UPDATE `Advisor` SET `firstName` = '$fnamepost', `lastName` = '$lnamepost', `buildingName` = '$bldgNamepost', `roomNumber` = '$officeRmpost', `email` = '$emailpost', `password` = '$newPasswordpost' WHERE `advisorID` = '$id'";
			if($rs = $open_connection->query($sql)){
				$_SESSION['ADVISOR_FNAME']= $fnamepost;
				$_SESSION['ADVISOR_LNAME']= $lnamepost;
				$_SESSION['ADVISOR_EMAIL']= $emailpost;
				$_SESSION["ADVISOR_BLDG_NAME"] = $bldgNamepost;
				$_SESSION["ADVISOR_RM_NUM"] = $officeRmpost; 
				header('Location: ../../views/homepage.php'); 
			}
			else{
				$query_error="There was a error with inserting the query";
			}	
		}	
	}	
}	
?>

<html>
<head>
	<title>Edit Error</title>
</head>
<body>
The following errors have occured...<br>
<?php echo($email_error) ?> <br>
<?php echo($query_error) ?> <br>
<?php echo($passwordMatch_error) ?> <br>
<?php echo($duplicate_error) ?> <br>
</body>
</html>