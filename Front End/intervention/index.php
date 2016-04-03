<?php
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
		<li class="active"><a data-toggle="pill" href="#home">System Status</a></li>
		<li><a data-toggle="pill" href="#menu1">Statistics</a></li>
		<li><a data-toggle="pill" href="#menu2">Security</a></li>
		</ul>

		<div class="tab-content">
			<div id="home" class="tab-pane fade in active">
 				<h3>System Status</h3>
				<ul class="list-group">
<?php if ($status == 0): ?>
<div class="alert alert-danger">
<strong>Major Error!</strong> The system is not running.
</div>
<?php elseif ($status == 1): ?>
<div class="alert alert-success">
<strong>Fully Operational.</strong> All systems are active and running.
</div>
<?php elseif ($status == 2): ?>
<div class="alert alert-warning">
<strong>Warning.</strong> There are currently active system warnings.
</div>
<?php elseif ($status == 3): ?>
<div class="alert alert-info">
<strong>Development.</strong> The system is currently being developed and is not yet active.
</div>
<?php endif; ?>

					<li class="list-group-item"><b>Operational status code:</b> <?php echo $status ?></li>
					<li class="list-group-item"><b>System time: </b><?php echo date("h.i A")?></li>
					<li class="list-group-item"><b>System time zone:</b> <?php echo date("T"); ?></li>
				</ul>
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