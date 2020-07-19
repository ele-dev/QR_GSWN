<!doctype html>

<?php
	session_start();

	$authorized = true;

	// check for login data
	if(!isset($_SESSION['loggedIn'])) {
		$authorized = false;
		header("Location: logout.php");
		exit;
	}
	
	if($_SESSION['loggedIn'] != true) {
		$authorized = false;
		header("Location: logout.php");
		exit;
	}
	
	echo "<p> Eingeloggt als: " . $_SESSION['currentUser'];

	function displayLinks()
	{
		// connect to the database
		$con = mysqli_connect("localhost", "user", "password", "dbName");
		
		// define sql query
		$sql = "SELECT * FROM tbl_sharedFiles WHERE owner='" . $_SESSION['currentUser'] . "' ORDER BY creationTime DESC";
		
		// execute the query and store the result
		$result = mysqli_query($con, $sql);
		
		// close the connection 
		mysqli_close($con);
		
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
		
		for($j = 0; $j < $i; $j++)
		{
			// Extract the necessesary string data 
			$fileName = basename($path[$j]);
			$size = round(($fileSize[$j] / 1000), 0) . "kb";
			$subjectStr = "Keine Angabe";
			if($schoolSubject[$j] != "noValue") {
				$subjectStr = $schoolSubject[$j];
			}
			
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
		
		return;
	}
	
	function displaySubjects()
	{
		// establish database connection 
		$con = mysqli_connect("localhost", "phpuser", "GnsKV{Yf", "qrShare");
		
		// Get all available school subjects 
		$sql = "SELECT * FROM tbl_subjectList";
		
		// Execute query and store result 
		$result = mysqli_query($con, $sql);
		
		// close the DB connection 
		mysqli_close($con);
		
		// Fetch the result into an array 
		$i = 0;
		while($dataset = mysqli_fetch_assoc($result))
		{
			$subjectList[$i] = $dataset["subjectName"];
			$i++;
		}
		
		// Output them in option tags for the html select element
		for($j = 0; $j < $i; $j++)
		{
			echo "<option value='" . $subjectList[$j] . "'>" . $subjectList[$j] . "</option>";
		}
	}
?>

<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Webservice for managing files shared via QR codes">
		<meta name="author" content="Elias Geiger">
		<meta name="keywords" content="QR codes, File sharing, GSWN">
		
		<title>QR Datei Freigaben - Admin Interface</title>
		
		<link rel="shortcut icon" type="image/png" href="resources/qrFavicon.png"/>
		
		<link rel="stylesheet" type="text/css" href="css/general.css" />
		<link rel="stylesheet" type="text/css" href="css/manager.css" />
	</head>
	
	<body>
		<H2>Erstellen sie eine neue Freigabe mit QR code</H2>
		
		<form action="upload.php" method="post" enctype="multipart/form-data">
			<p><b>Datei:</b><input type="file" name="fileToUpload" id="fileToUpload"></p>
			<p><b>Schulfach (optional):</b>
			<select name="subject">
				<option value="noValue">Keine Angabe</option>
				<?php 
					displaySubjects();
				?>
			</select>
			</p>
			<input type="submit" id="create" value="Freigabe Erstellen" name="submit">
		</form>
		
		<br><hr><br>
		
		<H2>Aktive Freigaben: </H2>
		
		<div class="bar-grid">
			<span>
				Suche: 
				<input type="text" placeholder="Dateiname eingeben" id="search-input" onkeyup="refreshData()" />
			</span>
		
			<span>
				Sortieren nach: 
				<select id="order-method" onchange="refreshData()">
					<option value="default">Datum - neu zu alt (Standart)</option>
					<option value="default-inverted">Datum - alt zu neu</option>
					<option value="fileSize">Dateigröße - klein zu groß</option>
					<option value="fileSize-inverted">Dateigröße - groß zu klein</option>
					<!-- ... -->
				</select>
			</span>
			
			<span>
				Filtern: 
				<select id="subject-filter" onchange="refreshData()">
					<option value="default">Alle Fächer</option>
					<?php 
						displaySubjects();
					?>
				</select>
				<select id="filetype-filter" onchange="refreshData()">
					<option value="default">Alle Dateien</option>
					<option value="pdf">PDF</option>
					<option value="word">Word</option>
					<option value="excel">Excel</option>
					<option value="image">Bilder</option>
					<option value="video">Videos</option>
					<option value="txt">Textdatei</option>
				</select>
			</span>
		</div>
		
		<!-- include external script file for ajax functionalities -->
		<script src="js/loadData.js"></script>
		
		<div class="grid-container" id="dataWrapper">
			<?php
				displayLinks();
			?>
		</div>
		
		<br><hr><br>
		
		<p>Möchten sie das <a href="changePassword.php">Passwort ändern</a>?</p>
		
		<br>
		
		<form action="logout.php" method="post">
			<input type="submit" value="<-- Ausloggen">
		</form>
	<body>
</html>
