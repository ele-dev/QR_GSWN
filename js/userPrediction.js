'use strict'

function showPrediction(str)
{
	var xhttp;
	if(str.length == 0) {
		document.getElementById("prediction").innerHTML = "";
		console.log("Empty field");
		return;
	}
	
	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) {
			// Get the html element that display the prediction options
			var predictionLabel = document.getElementById("prediction");
			predictionLabel.innerHTML = "";
			predictionLabel.innerHTML = this.responseText;
		}
	};
	
	// console.log("User input: " + str);
	
	xhttp.open("GET", "../php/gethint.php?userInput=" + str, true);
	xhttp.send();
}
