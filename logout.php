<?php 

	session_start();
	
	// Remove all session variables
	session_unset();
	
	// destroy the session
	session_destroy();
	
	header("Location: index.php");
	exit;

?>