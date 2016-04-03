<?php
session_start();
?>
 
 <html>
<!DOCTYPE html>

<head>
  <title>Generator Results</title>
  <html lang="en-gb">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function() {
      $('[data-toggle="tooltip"]').tooltip();
    });
  </script>


</head>

<body>
  <div class="container">
    <h1>
    <h1 style="color:red">MammalWeb Generator</h1>
    <p>Jenn the Generator (vesion 0.2 beta)</p>
    
 
 <?php
 
include("../sql.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["rawSQL"])) {

	
        $rawSQLQuery = clean($_POST["rawSQL"]) . " " . clean($_POST["rawSQLLimit"]);
		if(strpos(strtolower($rawSQLQuery), "delete") !== false || strpos(strtolower($rawSQLQuery), "drop") !== false || strpos(strtolower($rawSQLQuery), "insert") !== false || strpos(strtolower($rawSQLQuery), "update") !== false) { //Banned queries
			echo("Query blocked. You entered a forbidden command.");
		}
		else {
			$rawSQLResult = $sqlConnection->query($rawSQLQuery);
				?>
			<div class="row">
				<div class="col-sm-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<span class="glyphicon glyphicon-list-alt"></span>&nbsp &nbsp Query Results for <?php echo ("<code>" . $rawSQLQuery . "</code>");?> &nbsp &nbsp
							<button type="button" class="btn btn-default" data-toggle="collapse" href="#collapsibleNotifications">Expand/Collapse</button>
						</div>
						<div class="panel-body">
							<div id="collapsibleNotifications" class="panel-collapse">
								<table class="table table-striped">
									<thead>
										<tr>
											<?php
												if ($rawSQLResult->num_rows > 0) {
													$row = $rawSQLResult->fetch_assoc();
												$keys= array_keys($row);
												foreach($row as $key=> $value)
												{
												echo("<th>" . $key . "</th>");
												}
												echo("</tr>");
												echo("</thead>");
												echo("<tbody>");
												echo("<tr>");
												foreach($row as $key=> $value)
												{
												echo("<td>" . $value . "</td>");
												}
												echo("</tr>");
												while($row = $rawSQLResult->fetch_assoc()) {
												echo("<tr>");
												foreach($row as $key=> $value)
												{
												echo("<td>" . $value . "</td>");
												}
												echo("</tr>");
												       }
												?>
										</tbody>
								</table>
								<input type="button" class="button btn btn-success" name="printWindow" onclick="window.print();" value="Print" />
							</div>
						</div>
					</div>
				</div>
			</div>

		  					<form id="queries" action = "generator.php" method="post">
					<fieldset class="form-group">
						<label for="rawSQL">Run another query...</label>
						<input type="text" class="form-control" id="rawSQL" name="rawSQL" placeholder="Example: SELECT COUNT(*) from Animal">
						<small class="text-muted">DROP, UPDATE and INSERT INTO queries are disabled for security purposes.</small>
					</fieldset>
					<button type="submit" class="btn btn-primary">Run</button>

				</form>
          </div>
		  <?php
			//Image URL query
	        
	        } else {
	            echo("Query failed.");
	        }
			 
			
		}
		
    } else if (isset($_POST["imageID"])) {
        $imageID        = clean($_POST["imageID"]);
        $imageURLQuery  = "SELECT person_id, site_id, filename, upload_filename, person_id, upload_id, size, sequence_id, sequence_num, taken FROM Photo WHERE `photo_id` = " . $imageID . ";";
        $imageURLResult = $sqlConnection->query($imageURLQuery);
        //Image URL query
        if ($imageURLResult->num_rows > 0) {
            while ($row = $imageURLResult->fetch_assoc()) {
                $imageURL = "http://www.mammalweb.org/biodivimages/person_" . $row["person_id"] . "/site_" . $row["site_id"] . "/". $row["filename"];
				?>
				<div class="row">
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<span class="glyphicon glyphicon-list-alt"></span>&nbsp &nbsp Image ID <?php echo ($imageID);?> &nbsp &nbsp
							</div>
							<div class="panel-body">
								<div class="col-sm-8">
									<?php
									echo("<img src='" . $imageURL . "' class='img-rounded img-responsive' alt='Image ID '" . $imageID . "'>");
									?>
								</div>
								<div class="col-sm-4">
									<ul class="list-group">
										<li class="list-group-item">
										<?php
										echo("<b>Date taken:</b> " . $row["taken"]);
										?>
										</li>
										<li class="list-group-item">
										<?php
										echo("<b>Person ID:</b> " . $row["person_id"]);
										?>
										</li>
										<li class="list-group-item">
										<?php
										echo("<b>Site ID:</b> " . $row["site_id"]);
										?>
										</li>
										<li class="list-group-item">
										<?php
										echo("<b>Upload ID:</b> " . $row["upload_id"]);
										?>
										</li>
										<li class="list-group-item">
										<?php
										echo("<b>Sequence ID:</b> " . $row["sequence_id"]);
										?>
										</li>
										<li class="list-group-item">
										<?php
										echo("<b>Sequence number:</b> " . $row["sequence_num"]);
										?>
										</li>
										<li class="list-group-item">
										<?php
										echo("<b>Size (kb):</b> " . $row["size"]);
										?>
										</li>
										<li class="list-group-item">
										<?php
										echo("<b>URL:</b> " . $imageURL);
										?>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
            }
        } else {
            echo("No image found. Are you sure image ID " . $imageID . " exists? I doubt it does... :P");
        }
		echo("&nbsp;&nbsp;");
		?>
		<div>
			<form id="queries" action = "generator.php" method="post">
				<fieldset class="form-group">
				<label for="rawSQL">View another image...</label>
				<input type="text" class="form-control" id="imageID" name="imageID" placeholder="Example 12345">
				<small class="text-muted">Image loads in this window.</small>
				</fieldset>
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
		</div>
		<?php
		}
    } else {
        echo ("Hey, I am the MammalWeb Generator. Unfortunately you can't access me directly. You need to call me through one of the pages on the control panel. <br><br>");
		echo ("See you next time xxx");
}

//Strip out all harmful code
function clean($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?> 
<footer>
<div class="pull-right">
		<?php
		if(!session_is_registered(myusername)){
			echo("P.S. Oh, if you're tryin' to hit on me, it looks silly if you haven't <a href='../login.php'>logged in</a> first ;)");
		}
		?>
</div>
</footer>
</div>
</body>
</html>