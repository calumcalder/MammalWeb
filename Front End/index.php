<?php

session_start();
if(!session_is_registered(myusername)){
header("location:login.php");
}

include_once("sql.php");
include_once("sqlqueries.php");

// Credit for the below date difference calculator, which I have heavily adapted, is to Emil H, from Stack Overflow, at http://stackoverflow.com/questions/676824/how-to-calculate-the-difference-between-two-dates-using-php

$today = date("Y-m-d");
$diff = abs(strtotime($today) - strtotime($sqlCurrentOperatorLastPasswordUpdate));
$years = floor($diff / (365 * 60 * 60 * 24));
$months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
$days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
$lastPasswordChangeDate = null;

if ($years == 1)
	{
	$lastPasswordChangeDate = "Over a year ago.";
	}
  else
if ($years > 0)
	{
	$lastPasswordChangeDate = "Over " . $years . " years ago.";
	}
  else
if ($years == 0 && $months == 1)
	{
	$lastPasswordChangeDate = "One month ago.";
	}
  else
if ($years == 0 && $months > 0)
	{
	$lastPasswordChangeDate = $months . " months ago.";
	}
  else
if ($months == 0 && $days == 1)
	{
	$lastPasswordChangeDate = "Yesterday.";
	}
  else
if ($days > 0)
	{
	$lastPasswordChangeDate = $days . " days ago.";
	}
  else
	{
	$lastPasswordChangeDate = "Today.";
	}

?>


<html>
<!DOCTYPE html>

<head>
  <title>MammalWeb Central Command</title>
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
    <h1 style="color:red">MammalWeb Central Command</h1>
    <p>Master System Control</p>
    <nav class="navbar navbar-default">
      <div class="container-fluid">
        <div class="navbar-header"></div>
        <div>
          <ul class="nav navbar-nav">
            <li class="active">
              <a href="/andrew.taylor/mw">
                <span class="glyphicon glyphicon-th"></span>&nbsp &nbsp Core Operations
              </a>
            </li>
            <li>
              <a href="/andrew.taylor/mw/intervention">
                <span class="glyphicon glyphicon-alert"></span>&nbsp &nbsp Classification Intervention
              </a>
            </li>
            <li>
              <a href="/andrew.taylor/mw/data">
                <span class="glyphicon glyphicon-hdd"></span>&nbsp &nbsp Data Management
              </a>
            </li>
            <li>
              <a href="/andrew.taylor/mw/settings">
                <span class="glyphicon glyphicon-wrench"></span>&nbsp &nbsp System Parameters
              </a>
            </li>
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
      <li class="active">
        <a data-toggle="pill" href="#home"><span class = "glyphicon glyphicon-dashboard"></span>&nbsp &nbsp System Status</a>
      </li>
      <li>
        <a data-toggle="pill" href="#statistics"><span class = "glyphicon glyphicon-stats"></span>&nbsp &nbsp Statistics</a>
      </li>
      <li>
        <a data-toggle="pill" href="#menu2"><span class = "glyphicon glyphicon-lock"></span>&nbsp &nbsp Security</a>
      </li>
    </ul>

    <div class="tab-content">
      <div id="home" class="tab-pane fade in active">


        <h3>System Status</h3>
        <div id="stoppedAlert" class="alert alert-danger" style="display: none">
          <strong>Attention!</strong> The system is not running.

        </div>
        <div id="startedAlert" class="alert alert-success" style="display: none">
          <strong>Fully Operational.</strong> All systems are active and running.

        </div>
        <div id="pausedAlert" class="alert alert-warning" style="display: none">
          <strong>Warning.</strong> The algorithm is currently paused.

        </div>
        <div id="devAlert" class="alert alert-info" style="display: none">
          <strong>Development.</strong> The system is currently being developed and is not yet active.

        </div>

        <div class="row">
          <div class="col-sm-6">
            <div class="panel panel-default">
              <div class="panel-heading"><span class="glyphicon glyphicon-star"></span>&nbsp &nbsp Main</div>
              <div class="panel-body">

                <div class="list-group">
                  <li class="list-group-item"><b>Operational status ID: </b>
                    <span class="label label-info" style="float: right">Z0</span>
                  </li>
                  <li class="list-group-item"><b>Algorithm status code: </b>
                    <span class="badge"><?php
echo $algorithmStatus ?></span>
                  </li>
                  <li class="list-group-item"><b>Database status: </b>
                    <?php
if ($sqlStatus == 1)
	{ ?> <span class="label label-success" style = "float: right">ONLINE</span> <?php
	}
  else
	{ ?> <span class="label label-danger" style = "float: right">OFFLINE</span> <?php
	} ?>
                  </li>
                </div>




                Current Classification Cycle
                <div class="progress">
                  <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:40%">
                    40%
                  </div>
                </div>
              </div>


            </div>
          </div>
          <div class="col-sm-6">
            <div class="row">
              <div class="col-sm-4">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <span class="glyphicon glyphicon-qrcode"></span>&nbsp &nbsp Technical Info
                  </div>
                  <div class="panel-body">
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#networkModal">Network</button>
                    <div id="networkModal" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Network Information</h4>
                          </div>
                          <div class="modal-body">
                            <p><b>Server hostname: </b>
                              <?php
echo $_SERVER['SERVER_NAME']; ?>
                            </p>
                            <p><b>Server IP: </b>
                              <?php
echo $_SERVER['SERVER_ADDR']; ?>
                            </p>
                            <p><b>Server software: </b>
                              <?php
echo $_SERVER['SERVER_SOFTWARE']; ?>
                            </p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#timeModal">Time</button>
                    <div id="timeModal" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Time and Date</h4>
                          </div>
                          <div class="modal-body">
                            <p><b>System date: </b>
                              <?php
echo date("l, jS F, Y"); ?>
                            </p>
                            <p><b>System time: </b>
                              <?php
echo date("h.i A") ?>
                            </p>
                            <p><b>System time zone: </b>
                              <?php
echo date("T"); ?>
                            </p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <br />

              </div>
              <div class="col-sm-8">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <span class="glyphicon glyphicon-off"></span>&nbsp &nbsp Control
                  </div>
                  <div class="panel-body" align="center">
                    <input type="submit" class="button btn btn-success" name="start" value="Start" />
                    <input type="submit" class="button btn btn-warning" name="pauseAlgorithm" value="Pause Algorithm" />
                    <input type="submit" class="button btn btn-danger" name="stopSystem" value="Stop System" />
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-12">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <span class="glyphicon glyphicon-list-alt"></span>&nbsp &nbsp Notifications&nbsp &nbsp
                    <button type="button" class="btn btn-default" data-toggle="collapse" href="#collapsibleNotifications">Expand</button>
                  </div>

                  <div class="panel-body">
                    <div id="collapsibleNotifications" class="collapse panel-collapse">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Class</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>Z0</td>
                            <td>Notification system under development.</td>
                            <td><span class="label label-info">Info</span>
                            </td>
                          </tr>
                          <tr>
                            <td>A0</td>
                            <td>Algorithm successfully ran yesderday at 18.00.</td>
                            <td><span class="label label-success">Success</span>
                            </td>
                          </tr>
                          <tr>
                            <td>B0</td>
                            <td>Large number of blanks in last cycle (42).</td>
                            <td><span class="label label-warning">Warning</span>
                            </td>
                          </tr>
                          <tr>
                            <td>C0</td>
                            <td>Password hasn't been changed for 2 months.</td>
                            <td><span class="label label-danger">Danger</span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                      <input type="submit" class="button btn btn-success" name="refreshNotifications" value="Refresh Notifications" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <div id="statistics" class="tab-pane fade">
        <h3>Statistics at <?php echo date("h.i A") ?></h3>
        <div class="row">
          <div class="col-sm-6">
            <div class="panel panel-default">
              <div class="panel-heading">Quick Figures</div>
              <div class="panel-body">

                <div class="list-group">
                  <li class="list-group-item"><b>Total animal species: </b><span class="badge">
								<?php
echo $totalAnimals ?></span>
                  </li>
                  <li class="list-group-item"><b>Total images: </b><span class="badge">
								<?php
echo $totalImages ?></span>
                  </li>
                  <li class="list-group-item"><b>Images added today: </b><span class="badge">
								<?php
echo $totalImagesAddedToday ?></span>
                  </li>
                 <li class="list-group-item"><b>Total photos classified today: </b><span class="badge">
								<?php
echo $newlyClassifiedImages ?></span>
                  </li>
                  <li class="list-group-item"><b>Total blanks: </b>
                    <span class="badge"><?php
echo $totalBlanks ?></span>
                  </li>

                  <li class="list-group-item"><b>Total photos needing attention: </b>
                    <span class="badge"><?php
echo $totalNeedingAttention ?></span>
                  </li>
                  <li class="list-group-item"><b>Most common species: </b>
                    <span class="badge"><?php
echo $mostCommonSpecies ?></span>
                  </li>
				  
				  
				  
                </div>

                <div id="statisticsButtons">

                  <input type="submit" class="button btn btn-success" name="refreshStats" value="Refresh Stats" />
                  <input type="submit" class="button btn btn-info" name="emailStats" value="E-mail Stats" />

                  <!-- Credit -->
                  <!-- First author at http://stackoverflow.com/questions/20738329/how-to-call-a-php-function-on-the-click-of-a-button -->



                  <script type="text/javascript">
                    $(document).ready(function() {

                      // Get contol buttons in their correct state based on whether or not the algorithm is currrently running

                      $.get('system_status.php', function(mammalWebStatus) {
                        if (mammalWebStatus == "STARTED") {
                          $('input[name = "start"]').val('Started');
                          $('input[name = "start"]').prop('disabled', true);
                          $("#startedAlert").show("slow");
                        } else if (mammalWebStatus == "PAUSED") {
                          $('input[name = "algorithmPaused"]').val('Algorithm Paused');
                          $('input[name = "algorithmPaused"]').prop('disabled', true);
                          $("#pausedAlert").show("slow");
                        } else {
                          $('input[name = "systemStopped"]').val('System Stopped');
                          $('input[name = "systemStopped"]').prop('disabled', true);
                          $("#stoppedAlert").show("slow");
                        }
                      });
                      $('.button').click(function() {
                        var clickBtnValue = $(this).val();
                        var pwdValue = $('input[name = "newPassword"]').val();
                        var emailValue = $('input[name = "newEmail"]').val();

                        $(this).val('Working...').delay(0).queue(function() {

                          $(this).dequeue();
                        });
                        if (clickBtnValue == "Update Password") {
                        data = {
                          'password': pwdValue
                        };
                        }
                          else if (clickBtnValue == "Update E-mail") {
                        data = {
                          'email': emailValue
                        };
                        }
                          else {
                        data = {
                          'action': clickBtnValue
                        };
                        }



                        $.post('control.php', data, function(response) {

                          // Response div goes here.

                          if ((response) == 'E-MAIL_SUCCESSFUL') {
                            $('input[name = "emailStats"]').val('E-mail Sent!');
                            $('input[name = "emailStats"]').prop('disabled', true);
                          } else if ((response) == 'STATS_REFRESH_SUCCESSFUL') {
                            $('input[name = "refreshStats"]').val('Refresh Stats');
                            window.location.href("http://community.dur.ac.uk/andrew.taylor/mw/#statistics");
                          } else if ((response) == 'NOTIFICATION_REFRESH_SUCCESSFUL') {
                            $('input[name = "refreshNotifications"]').val('Refresh Notifications');
                          } else if ((response) == 'START_SUCCESSFUL') {
                            $('input[name = "start"]').val('Started');
                            $('input[name = "start"]').prop('disabled', true);
                            $('input[name = "pauseAlgorithm"]').val('Pause Algorithm');
                            $('input[name = "pauseAlgorithm"]').prop('disabled', false);
                            $('input[name = "stopSystem"]').val('Stop System');
                            $('input[name = "stopSystem"]').prop('disabled', false);
                            $("#startedAlert").show("slow");
                            $("#pausedAlert").hide("slow");
                            $("#stoppedAlert").hide("slow");
                          } else if ((response) == 'PAUSE_ALGORITHM_SUCCESSFUL') {
                            $('input[name = "pauseAlgorithm"]').val('Algorithm Paused');
                            $('input[name = "pauseAlgorithm"]').prop('disabled', true);
                            $('input[name = "start"]').val('Start');
                            $('input[name = "start"]').prop('disabled', false);
                            $("#pausedAlert").show("slow");
                            $("#startedAlert").hide("slow");
                            $("#stoppedAlert").hide("slow");
                          } else if ((response) == 'STOP_SYSTEM_SUCCESSFUL') {
                            $('input[name = "stopSystem"]').val('System Stopped');
                            $('input[name = "stopSystem"]').prop('disabled', true);
                            $('input[name = "pauseAlgorithm"]').val('Algorithm Paused');
                            $('input[name = "pauseAlgorithm"]').prop('disabled', true);
                            $('input[name = "start"]').prop('disabled', false);
                            $('input[name = "start"]').val('Start');
                            $("#stoppedAlert").show("slow");
                            $("#startedAlert").hide("slow");
                            $("#pausedAlert").hide("slow");
                          } else if ((response) == 'E-MAIL_ERROR') {
                            $('input[name = "emailStats"]').val('E-mail Error!');
                          } else if ((response) == 'PASSWORD_UPDATE_SUCCESSFUL') {
                            $('input[name = "updatePassword"]').prop('disabled', true);
                            $('input[name = "updatePassword"]').val('Updated!');
                          } else if ((response) == 'PASSWORD_UPDATE_ERROR') {
                            $('input[name = "updatePassword"]').val('Password Update Error!');
                          } else if ((response) == 'E-MAIL_UPDATE_SUCCESSFUL') {
                            $('input[name = "updateEmail"]').prop('disabled', true);
                            $('input[name = "updateEmail"]').val('E-mail Updated!');
                          } else if ((response) == 'E-MAIL_UPDATE_ERROR') {
                            $('input[name = "updateEmail"]').val('E-mail Update Error!');
                          }
                        });
                      });

                    });
                  </script>


                  <script>
                    function reloadPage() {
                      location.relaod();
                    }
                  </script>
                </div>


              </div>
            </div>
          </div>
          <div class="col-sm-6">
          </div>
        </div>
      </div>
      <div id="menu2" class="tab-pane fade">
        <h3>Security</h3>
        <div class="row">
          <div class="col-sm-6">
            <div class="panel panel-default">
              <div class="panel-heading">Account Management</div>
              <div class="panel-body">
                <div class="list-group">
                  <li class="list-group-item"><b>Operator e-mail: </b> <?php
echo $currentOperatorEmail; ?></li>
                  <li class="list-group-item"><b>Operator ID: </b> <?php
echo $currentOperatorID; ?></li>
                  <li class="list-group-item"><b>Last password change: </b><?php
echo $lastPasswordChangeDate; ?></li>
                </div>
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#passwordModal">Change Password</button>
                <div id="passwordModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Update Password</h4>
                      </div>
                      <div class="modal-body">

                          <input name="newPassword" type="password" class="form-control" placeholder="New password">


                      </div>
                      <div class="modal-footer">
                        <input type="submit" class="button btn btn-success" name="updatePassword" value="Update Password" style="float: left" />
                        </form>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>

                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#emailModal">Change E-mail</button>
                <div id="emailModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Update Operator E-mail Address</h4>
                      </div>
                      <div class="modal-body">

                          <input name="newEmail" type="text" class="form-control" placeholder="New e-mail address">


                      </div>
                      <div class="modal-footer">
                        <input type="submit" class="button btn btn-success" name="updateEmail" value="Update E-mail" style="float: left" />
                        </form>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>


              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  </div>

  <div class="container">
    <div class="row">
      <p class="alert alert-default"></p>
    </div>
  </div>


  <div id="aboutModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">About</h4>
        </div>
        <div class="modal-body">
          <p><b>MammalWeb Central Command (Alpha)</b>
          </p>
          <p><b>Version: </b>0.6.2</p>
          <br />
          <p><b>Developed by Group 8 </b> - Computer Science @ Durham University</p>
          <p><b>Web control development: </b>Andrew Taylor</p>
          <p><b>Database systems: </b>Stefan Pawliszyn</p>
          <p><b>Algorithms: </b>Calum Calder</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>


  <div class="navbar navbar-default navbar-fixed-bottom hidden-xs" role="navigation">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">MammalWeb Central Command</a>
    </div>

    <div>
      <ul class="nav navbar-nav">

        <li>
          <a href="/andrew.taylor/mw">
            <span class="glyphicon glyphicon-question-sign"></span>&nbsp &nbsp Help
          </a>
        </li>
        <li>
          <a href="#" data-toggle="modal" data-target="#aboutModal">
            <span class="glyphicon glyphicon-info-sign"></span>&nbsp &nbsp About
          </a>
        </li>
        <ul class="nav navbar-nav navbar-right">
          <li>
            <a href="/andrew.taylor/mw">
              <span class="glyphicon glyphicon-copyright-mark"></span>&nbsp &nbsp
              <?php
echo date("Y"); ?> Andrew Taylor
            </a>
          </li>
        </ul>
    </div>
  </div>
</body>

</html>