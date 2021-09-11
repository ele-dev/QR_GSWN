<?php

	// Look for HTTP GET value
	if(!isset($_GET['userInput'])) {
		exit;
	}

	$userInput = $_GET['userInput'];
	
	// No prediction for an empty value 
	if(strlen($userInput) == 0) {
		exit;
	}
	
	// Fetches existing username(s) that match the input
	function outputOptions($str)
	{
		// connect to the local DB server
		$con = mysqli_connect("localhost", "phpuser", "password", "dbName");
		
		// Get all the usernames available
		$sql = "SELECT username FROM tbl_userAccounts";
		
		// Excecute query and store result
		$result = mysqli_query($con, $sql);
		
		// close the connection 
		mysqli_close($con);
		
		// Fetch usernames into an array 
		$i = 0;
		while($dataset = mysqli_fetch_assoc($result))
		{
			$userList[$i] = $dataset["username"];
			$i++;
		}
		
		// Loop through the list and decide if the given input could become a username or not
		for($j = 0; $j < $i; $j++)
		{
			if(!strncmp($str, $userList[$j], strlen($str)))
			{
				echo $userList[$j] . "  ";
			}
		}
	}
	
	// Call the function to print out the matches
	outputOptions($userInput);
	
?>