<?php

	// start session to get access to browser cookies
	session_start();
	
	// Prevent unauthorized access 
	if(!isset($_SESSION['loggedIn']) || !isset($_SESSION['currentUser'])) {
		echo "Not authorized!";
		exit;
	}
	
	$orderMethod = "default";
	$fileTypeFilter = "default";
	$subjectFilter = "default";
	$searchInput = "";
	
	// check for additional paramters
	if(isset($_POST['orderMethod'])) {
		$orderMethod = $_POST['orderMethod'];
	}
	
	if(isset($_POST['fileTypeFilter'])) {
		$fileTypeFilter = $_POST['fileTypeFilter'];
	}
	
	if(isset($_POST['subjectFilter'])) {
		$subjectFilter = $_POST['subjectFilter'];
	}
	
	if(isset($_POST['searchInput'])) {
		$searchInput = $_POST['searchInput'];
	}
	
	function displaySharedFiles($user, $order, $fileFilter, $subjectFilter, $search)
	{
		// First establish a database connection_aborted
		$con = mysqli_connect("localhost", "phpuser", "GnsKV{Yf", "qrShare");
		
		// construct the SQL query based on the order method and the current user 
		$sqlQuery = "";
		switch($order)
		{
			case "default":
			{
				$sqlQuery = "SELECT * FROM tbl_sharedFiles WHERE owner='". htmlspecialchars($user) . "' ORDER BY creationTime DESC"; 
				break;
			}
			
			case "default-inverted":
			{
				$sqlQuery = "SELECT * FROM tbl_sharedFiles WHERE owner='". htmlspecialchars($user) . "' ORDER BY creationTime";
				break;
			}
			
			case "fileSize":
			{
				$sqlQuery = "SELECT * FROM tbl_sharedFiles WHERE owner='". htmlspecialchars($user) . "' ORDER BY size";
				break;
			}
			
			case "fileSize-inverted":
			{
				$sqlQuery = "SELECT * FROM tbl_sharedFiles WHERE owner='". htmlspecialchars($user) . "' ORDER BY size DESC";
				break;
			}
			
			default:
			{
				$sqlQuery = "SELECT * FROM tbl_sharedFiles WHERE owner='". htmlspecialchars($user) . "' ORDER BY creationTime DESC";
				break;
			}
		}
		
		// Execute the sql select query and store the result
		$result = mysqli_query($con, $sqlQuery);
		
		// close the database connection 
		mysqli_close($con);
		
		// Fetch result into arrays 
		$i = 0; 
		while($dataset = mysqli_fetch_assoc($result))
		{
			$ids[$i] = $dataset["id"];
			$path[$i] = $dataset["path"];
			$shareLink[$i] = $dataset["shareLink"];
			$fileSize[$i] = $dataset["size"];
			$fileType[$i] = $dataset["fileType"];
			$schoolSubject[$i] = $dataset["subject"];
			$timestamp[$i] = $dataset["creationTime"];
			
			$i++;
		}
		
		// count how many shared Files are actually displayed
		$numElements = 0;
		
		// Loop through the list and output the elements formatted correctly
		for($j = 0; $j < $i; $j++)
		{
			// Decide whether the current element passes the filter
			// If not it will be skipped
			if($fileFilter != "default") {
				if(!passesFileFilter($fileFilter, $fileType[$j])) {
					continue;
				}
			}
			
			if($subjectFilter != "default") {
				if($schoolSubject[$j] != $subjectFilter) {
					continue;
				}
			}
			
			// Extract the necessesary string data 
			$fileName = basename($path[$j]);
			$size = round(($fileSize[$j] / 1000), 0) . "kb";
			$subjectStr = "Keine Angabe";
			if($schoolSubject[$j] != "noValue") {
				$subjectStr = $schoolSubject[$j];
			}
			
			// Decide whether the current element's filename contains the search string
			// If not it will be skipped 
			if(strlen($search) > 0) {
				$tempStr = $fileName;
				if($tempStr == str_replace($search, "", $tempStr)) {
					continue;
				}
			}
			
			$numElements++;
			
			// output the relevant information
			echo "<div class='grid-item'>";
				echo "<p><b> Dateiname: </b>" . $fileName . "</p>";
				echo "<p><b> Schulfach: </b>" . $subjectStr . "</p>";
				echo "<a href='" . dirname($shareLink[$j]) . "/qr.png'>";
					echo "<div class='tooltip'>";
						echo "<img class='qrImg' src='" . dirname($shareLink[$j]) . "/qr.png'></img>";
						echo "<span class='tooltiptext'>Klicke zum Anzeigen</span>";
					echo "</div>";
				echo "</a>";
				echo "<p><b> Datei Größe: </b>" . $size . "</p>";
				echo "<p><b> Link: </b><a href='" . $shareLink[$j] . "'>" . $shareLink[$j] . "</a></p>";
				echo "<p><b> Erstellt am: </b>" . $timestamp[$j] . "</p>";
				echo "<form action='remove.php' method='post'>";
					echo "<input type='hidden' name='id' value='". $ids[$j] . "'>";
					echo "<input type='submit' class='remove' value='Entfernen'>";
				echo "</form>";
			echo "</div>";
		}
		
		if($numElements == 0) {
			echo "<center><p>Keine Freigaben gefunden</p></center>";
		}
		
		return;
	}
	
	function passesFileFilter($activeFilter, $fileExtension)
	{
		switch($activeFilter)
		{
			case "image":
			{
				if($fileExtension == "jpg" || $fileExtension == "png" || $fileExtension == "bmp" || $fileExtension == "svg")
				{
					return true;
				}
				break;
			}
			
			case "video":
			{
				if($fileExtension == "mp4" || $fileExtension == "avi" || $fileExtension == "mpg" || $fileExtension == "wmv" || $fileExtension == "mov")
				{
					return true;
				}
				break;
			}
			
			case "word":
			{
				if($fileExtension == "doc" || $fileExtension == "docx" || $fileExtension == "odt")
				{
					return true;
				}
				break;
			}
			
			case "excel":
			{
				if($fileExtension == "xls" || $fileExtension == "xlsx" || $fileExtension == "calc")
				{
					return true;
				}
				break;
			}
			
			case "pdf":
			{
				if($fileExtension == "pdf")
				{
					return true;
				}
				break;
			}
			
			case "txt":
			{
				if($fileExtension == "txt")
				{
					return true;
				}
				break;
			}
			
			default:
			{
				break;
			}
		}
		
		return false;
	}
	
	// Call the function to output the formatted grid boxes
	displaySharedFiles($_SESSION['currentUser'], $orderMethod, $fileTypeFilter, $subjectFilter, $searchInput);
	
?>