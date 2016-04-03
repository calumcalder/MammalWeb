<!-- Bootstrap Login Template (adapted)
<!-- https://getbootstrap.com/examples/signin/ -->
<!-- Accessed December, 2015 -->

<!-- PHP Login System (adapted) -->
<!-- http://www.phpeasystep.com/phptu/6.html -->
<!-- PHP Easy Step -->
<!-- phpeasystep.com -->
<!-- Accessed 2015 -->

<html>
<!DOCTYPE html>

<head>
  <title>MammalWeb Central Command - Login</title>
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
          <ul class="nav navbar-nav navbar-right">
            <li>
              <a href="#">
                <span class="glyphicon glyphicon-log-in"></span>&nbsp &nbsp Login
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

<div class = "col-sm-4">
</div>
<div class = "col-sm-4">

<?php if (isset($_GET['loginAttempt']) && $_GET['loginAttempt'] == 1): ?>
        <div id="invalidLogin" class="alert alert-warning">
          <strong>Warning.</strong> That username or password is incorrect.

        </div>

<?php elseif (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
        <div id="loggedOut" class="alert alert-success">
          <strong>Logged out.</strong> See you next time!

        </div>

<?php endif; ?>

      <form class="form-signin" name="form1" method="post" action="authenticate.php">
       <h2 class="form-signin-heading">Please sign in</h2>
        <label for="inputEmail" class="sr-only">E-mail address</label>
	<input type="email" name = "myusername" value="<?php echo $_GET['inputEmail'] ?>" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
        <br>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name = "mypassword" id="inputPassword" class="form-control" placeholder="Password" required>
        <div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me"> Remember me
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>
</div>
<div class = "col-sm-4">
</div>

</div>

  <div class="navbar navbar-default navbar-fixed-bottom hidden-xs" role="navigation">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">MammalWeb Central Command</a>
    </div>

    <div>
      <ul class="nav navbar-nav">


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
</body>
</html>
