<?php
	$sqlServer = "calum-calder.com";
	$sqlUsername = "admin3";
	$sqlPassword = "admin";
	$sqlDatabase = "MammalWeb";
	$sqlConnection = new mysqli($sqlServer, $sqlUsername, $sqlPassword, $sqlDatabase);
	$sqlStatus = False;

	if (!($sqlConnection->connect_error))
		{
		$sqlStatus = True;
		}

	$sqlOperatorQuery = "SELECT id, email, lastPasswordUpdate FROM operator WHERE id = 1";
	$sqlOperatorResult = $sqlConnection->query($sqlOperatorQuery);
	$currentOperatorEmail = null;
	$currentOperatorID = null;
	$sqlCurrentOperatorLastPasswordUpdate = null;

	if ($sqlOperatorResult->num_rows > 0)
		{
		while ($row = $sqlOperatorResult->fetch_assoc())
			{
			$currentOperatorEmail = $row["email"];
			$currentOperatorID = $row["id"];
			$sqlCurrentOperatorLastPasswordUpdate = $row["lastPasswordUpdate"];
			}
		}
	  else
		{
		$currentOperatorEmail = "No operators present.";
		}
	
	$animalOptionID = array();
	$habitatOptionID = array();
	$landuseOptionID = array();
	$animalNames = array();
	$habitatNames = array();
	$landuseNames = array();
	$res = $sqlConnection->query("SELECT * FROM `Options` WHERE `struc` = 'mammal' OR `struc` = 'bird'	;");
	if ($res->num_rows > 0) {
		while($row = $res->fetch_assoc()) {
			$optionId = $row["option_id"];
			array_push($animalOptionID,$optionId);
			$nameId = $row["option_name"];
			array_push($animalNames,$nameId);
		}
	}
	
	$res = $sqlConnection->query("SELECT * FROM `Options` WHERE `struc` = 'habitat';");
	if ($res->num_rows > 0) {
		while($row = $res->fetch_assoc()) {
			$optionId = $row["option_id"];
			array_push($habitatOptionID,$optionId);
			$nameId = $row["option_name"];
			array_push($habitatNames,$nameId);
		}
	}
	
	$res = $sqlConnection->query("SELECT * FROM `Options` WHERE `struc` = 'landuse';");
	if ($res->num_rows > 0) {
		while($row = $res->fetch_assoc()) {
			$optionId = $row["option_id"];
			array_push($landuseOptionID,$optionId);
			$nameId = $row["option_name"];
			array_push($landuseNames,$nameId);
		}
	}
	
	$animalOptionID = json_encode($animalOptionID);
	$animalNames = json_encode($animalNames);
	$habitatOptionID = json_encode($habitatOptionID);
	$habitatNames = json_encode($habitatNames);
	$landuseOptionID = json_encode($landuseOptionID);
	$landuseNames = json_encode($landuseNames);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
  
	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script> 
 

	</head>
	
	<body>
	<div class="row">
		<div class="col-md-2">
		</div>
		<div class="col-md-2">
		<select id="animalDropDown">
				<option>Choose an animal</option>
			</select>

		</div>
		<div class="col-md-2">
			
			<select id="habitatDropDown">
				<option>Choose a habitat</option>
			</select>
		</div>
		<div class="col-md-2">
			<select id="landuseDropDown">
				<option>Choose the landuse</option>
			</select>
		</div>
		<div class="col-md-2">
			  <label for="from">Time from:</label>
			  <input type="text" class="form-control" id="from">
		</div>
		<div class="col-md-2">
			  <label for="to">Time to:</label>
			  <input type="text" class="form-control" id="to">
			</div>
			
	  	</div>
		<div class="row">
			<div class="col-md-6">
			</div>
			<div class="col-md-6">
				<button type="button" value="Submit" id = "button1" >Submit</button>
			</div>
	  	</div>
	</div>
	</body>
	
		<script type="text/javascript">
			var animals = document.getElementById("animalDropDown");
			var animalNames = JSON.parse('<?php echo $animalNames ?>');
			var animalOptionID = JSON.parse('<?php echo $animalOptionID ?>');
			console.log(animalNames);
			for (var i = 0; i < animalNames.length; i++) {
				var opt = animalNames[i];
				var el = document.createElement("option");
				el.textContent = opt;
				el.value = animalOptionID[i];
				console.log(opt);
				animals.appendChild(el);
			}
			el.textContent = "Any";
			el.value = "Any";
			animals.appendChild(el);
			animalOptionID.push("Any");

			var habitats = document.getElementById("habitatDropDown");
			var habitatNames = JSON.parse('<?php echo $habitatNames ?>');
			var habitatOptionID = JSON.parse('<?php echo $habitatOptionID ?>');
			for (var i = 0; i < animalNames.length; i++) {
				var opt = habitatNames[i];
				var el = document.createElement("option");
				el.textContent = opt;
				el.value = habitatOptionID[i];
				habitats.appendChild(el);
			}
			el.textContent = "Any";
			el.value = "Any";
			habitats.appendChild(el);
			habitatOptionID.push("Any");

			var landuses = document.getElementById("landuseDropDown");
			var landuseNames = JSON.parse('<?php echo $landuseNames ?>');
			var landuseOptionID = JSON.parse('<?php echo $landuseNames ?>');
			for (var i = 0; i < landuseNames.length; i++) {
				var opt = landuseNames[i];
				var el = document.createElement("option");
				el.textContent = opt;
				el.value = landuseOptionID[i];
				landuses.appendChild(el);
			}
			
			el.textContent = "Any";
			el.value = "Any";
			landuses.appendChild(el);
			landuseOptionID.push("Any");
			
				$("#button1").click(function(){
					
					var animalChoice = document.getElementById("animalDropDown");
					var habitatChoice = document.getElementById("habitatDropDown");
					var landuseChoice = document.getElementById("landuseDropDown");
					var fromChoice = document.getElementById("from");
					var toChoice = document.getElementById("to");
					data = {
						animal: animalChoice.value,
						habitat: habitatChoice.value,
						landuse: landuseChoice.value,
						timeFrom: fromChoice.value,
						timeTo: toChoice.value
					}
					$.get("http://community.dur.ac.uk/h.g.jamieson/query.php", data, function(response){
						//output to csv
					})
				});
		</script>
</html>

