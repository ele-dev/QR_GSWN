<?php

	session_start();
	
	// Prevent unauthorized access
	if(!isset($_SESSION['loggedIn'])) {
		header("Location: logout.php");
		exit;
	}

	if($_SESSION['loggedIn'] != true) {
		header("Location: logout.php");
		exit;
	}
	
	$serverDomain = getEnvVariable("server_domain");
	
	function getEnvVariable($variable)
	{
		// connect to the database
		$con = mysqli_connect("localhost", "phpuser", "password", "dbName");
		
		// define the query
		$sql = "SELECT * FROM tbl_envVariables WHERE variable='" . htmlspecialchars($variable) . "'";
		
		// Exeute the query and store the result
		$result = mysqli_query($con, $sql);
		
		// close the db connection 
		mysqli_close($con);
		
		$dataset = mysqli_fetch_assoc($result);
		$value = $dataset["value"];
		
		return $value;
	}
	
	function getNextIdNumber()
	{
		// establish database connection 
		$con = mysqli_connect("localhost", "phpuser", "password", "dbName");
		
		// Get all table rows 
		$sql = "SELECT * FROM tbl_sharedFiles";
		
		// Execute the sql query and store the result
		$result = mysqli_query($con, $sql);
		
		// Fetch the result 
		$i = 0;
		while($dataset = mysqli_fetch_assoc($result))
		{
			$usedIds[$i] = $dataset["id"];
			$i++;
		}
		
		// close the connection
		mysqli_close($con);
		
		// Now search for the lowest possible id that isn't used already
		$nextId = 1;
		while(in_array($nextId, $usedIds) == true)
		{
			$nextId++;
		}
		
		return $nextId;
	}
	
	// Function to generate the new link 
	function generateUniqueLink()
	{
		$link = "";
		
		// establish database connection
		$con = mysqli_connect("localhost", "phpuser", "password", "dbName");
		
		// Define sql query to pull all reserved links/paths
		$sql = "SELECT * FROM tbl_sharedFiles";
		
		// Execute the query and store the result 
		$result = mysqli_query($con, $sql);
		
		mysqli_close($con);
		
		$i = 0;
		while($dataset = mysqli_fetch_assoc($result))
		{
			$reservedLinks[$i] = dirname($dataset["path"]);
			$i++;
		}
		
		$workDir = getEnvVariable("working_directory");
		
		// Generate a new unique link
		do {
			// $link = "/var/www/html/qrShare/shared/" . random_int(1000, 9999);
			$link = $workDir . "/shared/" . random_int(1000, 9999);
			$unique = true;
			for($j = 0; $j < count($reservedLinks); $j++)
			{
				if($link == $reservedLinks[$j]) {
					$unique = false;
					break;
				}
			}
		} while(!$unique);
		
		echo "<p>" . $workDir . "</p>";
		
		return $link;
	}
	
	// Function to prepare the files and directories for a new link
	function placeFile($link)
	{
		// create the folder that will contain the target file
		if(!mkdir(htmlspecialchars($link))) {
			echo "<p> Failed to create the folder! </p>";
			return "error";
		} else {
			echo "<p>Folder created</p>";
		}
		
		// Upload the file into the folder we just created
		$targetFile = $link . "/" . basename($_FILES["fileToUpload"]["name"]);
		echo "<p>Target File to upload: " . $targetFile . "</p>";
		if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], htmlspecialchars($targetFile))) 
		{
			echo "<p>File uploaded successfully.</p>";
		}
		else
		{
			echo "<p> Failed to upload the file!</p>";
			// Delete the folder 
			rmdir(htmlspecialchars($link));
			return "error";
		}
		
		// Create the table entry in the database for the new link
		$con = mysqli_connect("localhost", "phpuser", "GnsKV{Yf", "qrShare");
		
		// construct the actual web link to access the shared file
		$tempArray = explode("/", $link);
		$webLink = "https://qr-gswn.de/shared/" . $tempArray[(count($tempArray)-1)] . "/" . basename($_FILES["fileToUpload"]["name"]);
		
		// Get the file size
		$size = $_FILES["fileToUpload"]["size"];
		
		// Get the file extension 
		$fileExtension = pathinfo(basename($_FILES["fileToUpload"]["name"]), PATHINFO_EXTENSION);
		
		// Get school subject if given 
		$subject = "noValue";
		if(isset($_POST['subject'])) {
			$subject = $_POST['subject'];
		}
		
		// Prepare sql insert statement to fill the new link in the database table
		$sql = "INSERT INTO tbl_sharedFiles (id, path, shareLink, size, fileType, owner, subject) VALUES (" 
		. getNextIdNumber() . ", '" . $targetFile . "', '" . $webLink . "', '" . $size . "', '" . $fileExtension . "', '" . $_SESSION['currentUser'] . "', '" . $subject . "')";
		
		// Execute the query
		mysqli_query($con, htmlspecialchars($sql));
		
		// close the connection
		mysqli_close($con);
		
		return "success";
	}
	
	// Function to generate a QR code image for a given link
	function createQRcodeImg($link)
	{
		// And now generate the corresponding QR code image for the unique weblink
		include "phpqrcode/qrlib.php";
		
		echo "<p>creating qr code ...</p>";
		
		// Split the link in parts
		$tempArray = explode("/", $link);
		var_dump($tempArray);
		
		// First construct the destination weblink for the qr code
		$qrURL = "https://qr-gswn.de/shared/";
		$qrURL = $qrURL . $tempArray[(count($tempArray)-1)] . "/" . basename($_FILES["fileToUpload"]["name"]);
		echo "<p>QR code url: " . $qrURL . "</p>";
		
		// Then construct the filepath for the qrcode png file
		$qrPath = "shared/" . $tempArray[(count($tempArray)-1)] . "/qr.png";
		echo "<p> QR code img path: " . $qrPath . "</p>";
		
		// Finally generate the QR image file 
		QRcode::png(htmlspecialchars($qrURL), htmlspecialchars($qrPath), "M", 6, 6);
		
		return "success";
	}
	
	// --- Main code from here on --- //
	
	// First create the random path for the link 
	$targetDir = generateUniqueLink();
	echo "<p>Random generated link/path: " . $targetDir . "</p>";
	// Then upload the file to the destination
	$returnVal = placeFile($targetDir);
	if($returnVal == "error") {
		exit;
	}
	
	// Generate the QR code image file in the same directory
	$returnVal = createQRcodeImg($targetDir);
	if($returnVal == "error") {
		echo "<p>Failed to create QR code image!</p>";
		exit;
	} else {
		echo "<p>QR code created</p>";
	}
	
	// Redirection to the admin.php file
	header("Location: admin.php");
	exit;
?>