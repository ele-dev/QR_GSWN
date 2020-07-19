'use strict'

function refreshData()
{
	// First get the content of the html elements 
	var orderMethod = document.getElementById("order-method").value;
	console.log("order method: " + orderMethod);
	
	var fileTypeFilter = document.getElementById("filetype-filter").value;
	console.log("File type filter: " + fileTypeFilter);
	
	var subjectFilter = document.getElementById("subject-filter").value;
	console.log("Subject filter: " + subjectFilter);
	
	var searchInput = document.getElementById("search-input").value;
	console.log("search input: " + searchInput);
	
	// Get the html container element where we enter the result of the ajax request 
	var container = document.getElementById("dataWrapper");
	
	// Make an asynchronous http post request to the web server
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) {
			// Clear the target container 
			container.innerHTML = "";
			
			// now enter the result of the request 
			container.innerHTML = this.responseText;
		}
	};
	
	// construct the post values 
	var postParameters = "orderMethod=" + orderMethod;
	if(searchInput != "") {
		postParameters += "&searchInput=" + searchInput;
	}
	postParameters += "&fileTypeFilter=" + fileTypeFilter;
	postParameters += "&subjectFilter=" + subjectFilter;
	// ... append filter method and search input 
	console.log("HTTP POST parameters: " + postParameters);
	
	xhttp.open("POST", "../php/getSharedFiles.php", true);
	xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhttp.send(postParameters);
	
	return;
}