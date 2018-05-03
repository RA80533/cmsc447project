<!DOCTYPE html>
<?php
	// server should keep session data for AT LEAST 1 hour
	ini_set('session.gc_maxlifetime', 3600);

	// each client should remember their session id for EXACTLY 1 hour
	session_set_cookie_params(3600);
	session_start();
	
	// Connect to the SQLite database
	$db = new PDO("sqlite:../db/447db.sqlite") or die("Unable to open the database.");

	/* This will display the accounts table.
	$query = "SELECT * FROM accounts WHERE 1";
	foreach ($db->query($query) as $row){
		echo $row[0];
	}

	$db = null;
	*/

	// Checks to see if the user is logged in, if so it redirects them to homepage

	/*
	if (isset($_SESSION["HAS_LOGGED_IN"])) {
		if ($_SESSION["HAS_LOGGED_IN"]) {
			header('Location: map.html');
		}
	}
	*/

	if ($_POST) {
		$username = ($_POST["username"]);
		$password = ($_POST["password"]); //replace md5 with name of server
		//$password = md5($_POST["password"]); //replace md5 with name of server
		// Search if user exists in DB
		$search_account = "SELECT * FROM accounts WHERE userName = '$username' AND password = '$password';";
		
		$counter = 0;
		
		foreach ($db->query($search_account) as $row){
			$counter = $counter + 1;
		}	
		
		// Check whether or not there has been a successful user creation
		if ($counter == 1) {
			/*
			// Translate the SQL Query into a dictionary
			$advisorDict = mysqli_fetch_assoc($queryOfSearchAdvisor);

			// Assigning to session values based on what data is found
			$_SESSION["HAS_LOGGED_IN"] = true;
			*/
			// Redirecting to index.html
			//header('Location:../index.html');
			echo "Login Successful";
	} else {
			echo "Login FAILED";
	}

   $db = null;
}
?>
<html>

    <head>
        <title>ZipCompare Login Portal</title>
        <link rel="stylesheet" type="text/css" href="../../css-pure/pure-min.css">
        <link rel="stylesheet" type="text/css" href="css/index.css"> </head>

    <body>
            
        <div id="log-form">
            <h1 id="log-title">
                ZipCompare Login
            </h1>
            <form class='pure-form pure-form-aligned' <?php $self=htmlspecialchars($_SERVER[ "PHP_SELF"]); echo ( "action='$self'"); ?> method='POST'>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username </label>
                        <input id="username" type="username" placeholder="name123" name="username" autocapitalize="off" autocorrect="off" required> </div>
                    <div class="pure-control-group">
                        <label for="password">Password </label>
                        <input type="password" autocapitalize="off" autocorrect="off" placeholder="Password" id="password" name="password" required>                     
                    </div>
                    <div class="pure-controls">
                        <button type="submit" class="pure-button pure-u-24-24 pure-button-primary">Login</button>
                    </div>
                </fieldset>
            </form>
            <div id="register">
                <br> Create an Account &nbsp;
                <a href="index.php">
                    <button class="pure-button" type="button">Register</button>
                </a>
            </div>
            <br>
            <div id="forgot-pass">
                <a href="../forms/editAdvisor.php">
                    Change your password?
                </a>
            </div>
        </div>
    </body>

    </html>