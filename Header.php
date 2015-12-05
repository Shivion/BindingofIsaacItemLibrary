<?php
	require'connect.php';
	session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Binding of Isaac Item Library</title>
	<!--Boot Strap-->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<!--Theme-->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
	<!--My CSS-->
    <link rel="stylesheet" type="text/css" href="BoIIL.css">
</head>
<body>
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<a class="navbar-brand" id="Home" href="index.php">Binding of Isaac Item Library</a>
		<!--<div class="pull-right" id="login">-->
		<div>
			<?php
				if(isset($_SESSION['username']))
				{
					echo '<p class="navbar-text navbar-right" id="logout"> Hello ' .$_SESSION['username'] . '! <a class="navbar-link" href="login.php?logout">Log Out</a></p>';
				}
				else
				{
					echo '<form class="navbar-form navbar-right" id="login" action="login.php" method="post">
							<div class="form-group">
								<label class="sr-only" for="username">Username</label>
								<input class="form-control input-sm" type="text" id="username" name="username" placeholder="Username">
							</div>
							<div class="form-group">
								<label class="sr-only" for="pass">Password</label>
								<input class="form-control input-sm" type="password" id="pass" name="pass" placeholder="Password">
							</div>
							<input class="btn btn-default navbar-btn btn-sm" type="submit" value="Login">
						</form>';
				}
			?>
		</div>
	</nav>