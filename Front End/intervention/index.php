<?php
include("../sql.php");
$status = 1;

function getProblemImages($evenness) {
	global $sqlConnection;
	$query = "SELECT * FROM `Photo` WHERE evenness >= $evenness AND photo_id NOT IN (SELECT photo_id FROM XClassification) LIMIT 0, 100;";
	return $sqlConnection->query($query);
}

$evenness = isset($_GET['evenness']) ? $_GET['evenness'] : "0.6";
$problem_images = getProblemImages($evenness);
?>

<html>
<!DOCTYPE html>
<style>
.nothingFancy {
	text-decoration:none;
	color: green;
}

.header {
	margin-top: 10px;
}

.slider-container {
	display: inline-block;
	width: calc(100% - 305px);
}
.slider-refresh {
	float: right;
	margin-top: -5px;
}
.slider-description {
	display: inline-block;
	position: relative;
	top: -7px;
	padding-right: 10px;
}
.evenness-range-label {
	display: inline-block;
	position: relative;
	top: -5;
	width: 40px;
}


#photo-id-input {
	background:none!important;
	border:none; 
	padding:0!important;
	font: inherit;
	cursor: pointer;
	text-align: left;
	width: 100%;
}
</style>
<head>
	<title>MammalWeb Central Command</title>
	<html lang="en-gb">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<script src = "https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>


</head>

<body>

	<div class = "container">
		<h1><h1 style="color:red">MammalWeb Central Command</h1>
		<p>Master System Control</p>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
    </div>
    <div>
      <ul class="nav navbar-nav">
        <li><a href="/andrew.taylor/mw"><span class="glyphicon glyphicon-th"></span>&nbsp &nbsp Core Operations</a></li>
        <li class="active"><a href="/andrew.taylor/mw/intervention"><span class="glyphicon glyphicon-alert"></span>&nbsp &nbsp Classification Intervention</a></li>
        <li><a href="/andrew.taylor/mw/data"><span class="glyphicon glyphicon-hdd"></span>&nbsp &nbsp Data Management</a></li>
        <li><a href="/andrew.taylor/mw/settings"><span class="glyphicon glyphicon-wrench"></span>&nbsp &nbsp System Parameters</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li>
	  <a href="/andrew.taylor/mw/logout.php">
	    <span class="glyphicon glyphicon-log-out"></span>&nbsp &nbsp Logout (<?php echo($currentOperatorEmail); ?>)
   	  </a>
	</li>
      </ul>
    </div>
  </div>
</nav>

		<ul class="nav nav-tabs">
		<li class="active"><a data-toggle="pill" href="#home">Problem Images</a></li>
		<li><a data-toggle="pill" href="#menu1">Statistics</a></li>
		<li><a data-toggle="pill" href="#menu2">Security</a></li>
		</ul>

		<div class="tab-content">
			<div id="home" class="tab-pane fade in active">
				<div class="alert alert-info header">
				Click on an image ID below to be taken to the processing page. Move the slider to change the minimum threshold for image evenness to filter images.
				</div>
				<form class="evenness-slider-form">
					<div class="slider-description">
					Minimum Evenness Score:
					</div>
					<div class="slider-container">
					<input name="evenness" id="evenness-slider" value="<?php echo $evenness ?>" class="evenness-slider" type="range" min="0" max="1" step="0.025"> </input>
					<script>
					var slider = document.getElementById('evenness-slider')
					slider.oninput = slider.onchange = function() {document.getElementById('evenness-label').innerHTML = this.value}
					</script>
					</div>
					<div class="evenness-range-label" id="evenness-label"> <?php echo $evenness ?> </div>
					<input type="submit" class="slider-refresh btn btn-default" value="Refresh"></input>
				</form>

				<table class="image-table table table-hover">
				<thead>
					<th>Photo ID</th>
					<th>Evenness</th>
				</thead>
				<tbody>
					<?php
					while ($row = $problem_images->fetch_assoc()) {
						$photo_id = $row['photo_id'];
						$evenness = $row['evenness'];
						echo "<tr>";
						echo "<td><form action='../data/generator.php' target='_blank' method='post'>";
						echo "	    <input id='photo-id-input' type='submit' name='imageID' value='$photo_id' />";
						echo "</form></td>";
						echo "<td>$evenness</td>";
						echo "</tr>";
					}
					?>
				</tbody>
				</table>
			</div>
			<div id="menu1" class="tab-pane fade">
 				<h3>Statistics</h3>
				<p>...</p>
			</div>
			<div id="menu2" class="tab-pane fade">
				<h3>Security</h3>
				<p>...</p>
			</div>
		</div>


	</div>
</body>

</html>
