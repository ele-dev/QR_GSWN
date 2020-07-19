<!doctype html>

<?php
	session_start();
	
	if(isset($_POST['password']) && isset($_POST['username'])) {
		if(validateLoginData($_POST['username'], $_POST['password']) == true) {
			$_SESSION['loggedIn'] = true;
			$_SESSION['currentUser'] = $_POST['username'];
			
			header("Location: admin.php");
			exit;
		}
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
	
	function printUsers()
	{
		// connect to the database
		$con = mysqli_connect("localhost", "phpuser", "password", "dbName");
		
		// define the query
		$sql = "SELECT * FROM tbl_userAccounts";
		
		// Exeute the query and store the result
		$result = mysqli_query($con, $sql);
		
		// close the db connection 
		mysqli_close($con);
		
		// fetch the data 
		$i = 0;
		while($dataset = mysqli_fetch_assoc($result))
		{
			$userList[$i] = $dataset["username"];
			$i++;
		}
		
		// print it out to the select tag on the page
		for($j = 0; $j < $i; $j++)
		{
			echo "<option value='" . $userList[$j] . "'>" . $userList[$j] . "</option>";
		}
		
		return;
	}
	
?>

<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Webservice for managing files shared via QR codes">
		<meta name="author" content="Elias Geiger">
		<meta name="keywords" content="QR codes, File sharing, GSWN">
		
		<title>QR Datei Freigaben</title>
		
		<link rel="shortcut icon" type="image/png" href="resources/qrFavicon.png"/>
		
		<link rel="stylesheet" type="text/css" href="css/general.css" />
		<link rel="stylesheet" type="text/css" href="css/login.css" />
	</head>
	
	<body>
		<H1>Verwaltung von QR Datei Freigaben</H1>
		
		<div>
			<img src="resources/qrCode-graphic.png" id="pageLogo"></img>
		</div>
		
		<br>
		
		<center>
		<div class="loginContainer">
			<form action="" method="post">
				<p>Hinweis: <i>vorname.nachname</i></p> 
				<p><span id="prediction"></span></p>
				<p><input type="text" placeholder="Benutzername Eingeben" name="username" id="username" onkeyup="showPrediction(this.value)"></p>
				<p><input type="password" placeholder="Passwort Eingeben" name="password" id="password"></p>
				<input type="submit" id="submit" value="Einloggen -->">
			</form>
		</div>
		</center>
		
		<!-- java script file for the ajax realtime prediction -->
		<script src="js/userPrediction.js"></script>
		
		<footer>
			<hr>
			<a href="impressum.php">Impressum</a>
		</footer>
	<body>
</html>
