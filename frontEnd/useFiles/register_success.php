<!DOCTYPE html>
	<?php
	$FNAME = $_POST['fName'];
	$LNAME = $_POST['lName'];
	$EMAIL = $_POST['email'];
	$USERNAME = $_POST['username'];
	$PASSWORD = $_POST['password'];
	$PASSWORD = md5($PASSWORD);
	$ZIPCODE = $_POST['zipcode'];
	$ZERO = 0;
	// Cool, got the data now.
	/*
	echo $FNAME;
	echo "\n";
	echo $LNAME;
	echo "\n";
	echo $EMAIL;
	echo "\n";
	echo $USERNAME;
	echo "\n";
	echo $PASSWORD;
	echo "\n";
	echo $ZIPCODE;
	*/
	echo "Creating account...\n";

	//$db = new PDO("sqlite:../db/447db.sqlite") or die("Unable to open the database.");

	$db = new SQLite3("../db/447db.sqlite");
	$query = "INSERT INTO accounts (key, username, password, firstName, lastName, email, zipcode) VALUES ('0', '".$USERNAME."', '".$PASSWORD."', '".$FNAME."', '".$LNAME."', '".$EMAIL."', '".$ZIPCODE."')";
	$db->exec($query);

	/* Doesn't work
	$qry = $db->prepare('INSERT INTO accounts (key, username, password, firstName, lastName, email, zipcode) VALUES (?, ?, ?, ?, ?, ?, ?)');
	$qry->execute(array($ZERO, $USERNAME, $PASSWORD, $FNAME, $LNAME, $EMAIL, $ZIPCODE));
	*/

	/*
	$insert = "INSERT INTO accounts (key, username, password, firstName, lastName, email, zipcode) VALUES (:zero, :username, :password, :fname, :lname, :email, :zipcode)";
	$stmt = $db->prepare($insert);
	$stmt->bindParam(':zero', $ZERO);
	$stmt->bindParam(':username', $USERNAME);
	$stmt->bindParam(':password', $PASSWORD);
	$stmt->bindParam(':fname', $FNAME);
	$stmt->bindParam(':lname', $LNAME);
	$stmt->bindParam(':email', $EMAIL);
	$stmt->bindParam(':zipcode', $ZIPCODE);
	$stmt->execute();
	*/

	// Doesn't work.
	// $query = "INSERT INTO accounts (key, username, password, firstName, lastName, email, zipcode) VALUES ('0', '".$USERNAME."', '".$PASSWORD."', '".$FNAME."', '".$LNAME."', '".$EMAIL."', '".$ZIPCODE."')";
	// $db->query($query);

	/* Doesn't work
	$db = sqlite_open('../db/447db.sqlite');
	$query = "INSERT INTO accounts (key, username, password, firstName, lastName, email, zipcode) VALUES (0, ".$USERNAME.", ".$PASSWORD.", ".$FNAME.", ".$LNAME.", ".$EMAIL.", ".$ZIPCODE.")";
	$qry = sqlite_exec($db, $query);
	if (!$query){
		echo "Account creation failed! Returning you to previous page...";
		header("refresh:5;register,php");
	}
	*/

	header("refresh:5;url=Login.php");
	?>
</html>