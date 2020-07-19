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
	
	// Ensure that the necessary parameters are given
	if(!isset($_POST['id'])) {
		header("Location: admin.php");
		exit;
	}
	
	// Function to delete the files and directory for a given link
	function removeLinkAndFile($linkId)
	{
		// output id
		echo "<p>ID: " . $linkId . "</p>";
	
		// establish DB connection
		$con = mysqli_connect("localhost", "phpuser", "password", "dbName");
		
		// prepare statement to pull information
		$sql = "SELECT * FROM tbl_sharedFiles WHERE id=" . $linkId;
		
		// Execute query to get the file path
		$result = mysqli_query($con, htmlspecialchars($sql));
		
		$dataset = mysqli_fetch_assoc($result);
		$file = $dataset["path"];
		
		echo "<p>File Path: " . $file . "</p>";
		
		// Delete the file 
		if(!unlink(htmlspecialchars($file))) {
			mysqli_close($con);
			return false;
		}
		
		// Delete the qr code image file 
		$file = dirname($file) . "/qr.png";
		if(!unlink(htmlspecialchars($file))) {
			mysqli_close($con);
			return false;
		}
		
		// Delete the link directory that contained the file too
		$dirPath = dirname($file);
		
		echo "<p>Directory path: " . $dirPath . "</p>";
		
		if(!rmdir(htmlspecialchars($dirPath))) {
			mysqli_close($con);
			return false;
		}
		
		// prepare sql statement for deleting the DB entry 
		$sql = "DELETE FROM tbl_sharedFiles WHERE id='" . $linkId . "'";
		
		// Execute the query
		mysqli_query($con, htmlspecialchars($sql));
		
		// close the connection
		mysqli_close($con);
		
		return true;
	}
	
	// Call the function to remove the file and corresponding link 
	$result = removeLinkAndFile($_POST['id']);
	echo "<br>";
	if($result) {
		echo "<p>Success</p>";
	} else {
		echo "<p>Error</p>";
		exit;
	}
	
	// Redirection to the admin.php file
	header("Location: admin.php");
	
?>