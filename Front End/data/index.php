<?php
session_start();
if(!session_is_registered(myusername)){
header("location: /andrew.taylor/mw/login.php");
}
include("../sql.php");
$status = 1;
?>

<html>
<!DOCTYPE html>
<style>
.nothingFancy {
	text-decoration:none;
	color: green;
}
</style>
<head>
	<title>MammalWeb Central Command</title>
	<html lang="en-gb">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<link rel="stylesheet" href="http://community.dur.ac.uk/andrew.taylor/mw/query-builder.css">
	<script src = "https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<script src = "http://community.dur.ac.uk/andrew.taylor/mw/query-builder.js"></script>


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
        <li><a href="/andrew.taylor/mw/intervention"><span class="glyphicon glyphicon-alert"></span>&nbsp &nbsp Classification Intervention</a></li>
        <li class="active"><a href="/andrew.taylor/mw/data"><span class="glyphicon glyphicon-hdd"></span>&nbsp &nbsp Data Management</a></li>
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
		<li class="active"><a data-toggle="pill" href="#home">Queries</a></li>
		<li><a data-toggle="pill" href="#menu1">Export</a></li>
		<li><a data-toggle="pill" href="#menu2">Data Visualisation</a></li>
		</ul>

		<div class="tab-content">
			<div id="home" class="tab-pane fade in active">
 				<h3>Queries</h3>
				<!-- Start Here for Queries -->
				<form id="queries" action = "generator.php" method="post">
					<fieldset class="form-group">
					  <div class="row">
					    <div class="form-group col-sm-10">
						<label for="rawSQL">Raw SQL query</label>
						<input type="text" class="form-control" id="rawSQL" name="rawSQL" placeholder="Example: SELECT COUNT(*) from Animal">
						</div>
						<div class="form-group col-sm-2">
						<label for="rawSQLLimit">Range to view</label>
						<input type="text" class="form-control" id="rawSQLLimit" name="rawSQLLimit" placeholder="LIMIT 0, 30" value="LIMIT 0, 30">
						</div>
						</div>
						<small class="text-muted">DELETE, DROP, INSERT INTO and UPDATE queries are disabled for security purposes.</small>
					</fieldset>
					<button type="submit" class="btn btn-primary">Submit</button>

				</form>
				<!-- Stop Here for Queries -->
			</div>
			<div id="menu1" class="tab-pane fade">
 				<h3>Statistics</h3>
				<!-- Bandits Start Here for Export -->
				
				<!-- Bandits Stop Here for Export -->
				
			</div>
			<div id="menu2" class="tab-pane fade">
				<h3>Visualisation</h3>
					<form id="queries" action = "generator.php" method="post">
					<fieldset class="form-group">
						<label for="rawSQL">Image ID to view</label>
						<input type="text" class="form-control" id="imageID" name="imageID" placeholder="Example 12345">
						<small class="text-muted">Image will open in a new window.</small>
					</fieldset>
					<button type="submit" class="btn btn-primary">Submit</button>

				</form>
			</div>
		</div>


	</div>
</body>

</html>