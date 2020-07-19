<!doctype html>

<?php
	session_start();

	$status = "";

	// Prevent unauthorized access
	if(!isset($_SESSION['loggedIn'])) {
		header("Location: logout.php");
		exit;
	}
	
	function run()
	{
		if(!isset($_POST['newPwd']) || !isset($_POST['newPwdRepeat']) || !isset($_POST['oldPwd'])) {
			$status = "Unvollständige Eingabe!";
			return;
		}
	
		// Validate the user inputs, first compare the two fields for the new password
		if($_POST['newPwd'] != $_POST['newPwdRepeat']) {
			$status = "<b>Neues Passwort</b> und <b>Neues Passwort Wiederholen</b> stimmen nicht überein!";
			echo "<p class='error'>" . $status . "</p>";
			return;
		}
		
		// Ensure that the old password was entered correctly
		if(validateLoginData($_SESSION['currentUser'], $_POST['oldPwd']) == false) {
			$status = "<b>Aktuelles Passwort</b> inkorrekt!";
			echo "<p class='error'>" . $status . "</p>";
			return;
		}
		
		// If everything went right then update the login passwort in the database 
		$con = mysqli_connect("localhost", "phpuser", "password", "dbName");
		
		// get the md5 hash of the new password
		$pass_hash = md5($_POST['newPwd']);
		
		// Define sql string for updating the login_password entry
		$sql = "UPDATE tbl_userAccounts SET login_password_hash='" . htmlspecialchars($pass_hash) . "' WHERE username='" . $_SESSION['currentUser'] . "'";
		
		// Execute the sql query
		mysqli_query($con, $sql);
		
		// close the DB connection
		mysqli_close($con);
		
		// Set the status 
		$status = "Passwort wurde erfolgreich geändert";
		echo "<p class='success'>" . $status . "</p>";
		
		return;
	}
	
	function validateLoginData($user, $password)
	{
		// connect to the database
		$con = mysqli_connect("localhost", "phpuser", "password", "dbName");
		
		// get the md5 hash of the password 
		$pass_hash = md5($password);
		
		// define the query
		$sql = "SELECT * FROM tbl_userAccounts WHERE username='" . htmlspecialchars($user) . "' AND login_password_hash='" . htmlspecialchars($pass_hash) . "'";
		
		// Exeute the query and store the result
		$result = mysqli_query($con, $sql);
		
		// close the db connection 
		mysqli_close($con);
		
		// count the result rows
		$rowCount = mysqli_num_rows($result);
		
		if($rowCount == 1) {
			return true;
		}
		
		return false;
	}

?>

<html>

	<head>
		<meta charset="uft-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Webservice for managing files shared via QR codes">
		<meta name="author" content="Elias Geiger">
		<meta name="keywords" content="QR codes, File sharing, GSWN">
		
		<title>QR Datei Freigaben - Passwort Ändern</title>
		
		<link rel="shortcut icon" type="image/png" href="resources/qrFavicon.png"/>
		
		<link rel="stylesheet" type="text/css" href="css/general.css" />
		<link rel="stylesheet" type="text/css" href="css/password.css" />
	</head>
	
	<body>
		<H2>Ändern sie das Login Passwort für den Webdienst</H2>
		
		<center>
			<form action="" method="post">
				<p><label for="oldPwd">Aktuelles Passwort: </label></p>
				<p><input type="password" name="oldPwd" id="oldPwd"></p>
				
				<p><label for="newPwd">Neues Passwort: </label></p>
				<p><input type="password" name="newPwd" id="newPwd"></p>
				
				<p><label for="newPwdRepeat">Neues Passwort wiederholen: </label></p>
				<p><input type="password" name="newPwdRepeat" id="newPwdRepeat"></p>
				
				<input type="submit" value="Passwort Ändern">
			</form>
		</center>
		
		<hr>
		
		<p><a href="admin.php">Zurück zur Verwaltung</a></p>
		
		<?php
			run();
		?>
	</body>

</html>