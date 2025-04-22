<?php require_once("db.inc.php");

$viewport = "width=device-width, initial-scale=";
$viewport .= @$initialScale ?: "1.0";

if (@$userScalable)
	$viewport .= ", user-scalable=" . $userScalable;

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="<?php echo $viewport ?>">

	<title>HamAlert</title>

	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-dialog.min.css" rel="stylesheet">
	<link href="css/dashboard.css?v=4" rel="stylesheet">
	<link href="css/pace.css" rel="stylesheet">
	<link rel="stylesheet" href="css/bootstrap-select.min.css">
	<link rel="stylesheet" href="css/ladda-themeless.min.css">
	<link rel="icon" type="image/png" sizes="96x96" href="/images/favicon-96x96.png">

	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/bootstrap-dialog.min.js"></script>
	<script src="js/jsrender.min.js"></script>
	<?php if (!@$_GET['hidenav']): ?>
	<script src="js/pace.min.js"></script>
	<?php endif; ?>
	<script src="js/bootstrap-select.min.js"></script>
	<script src="js/spin.min.js"></script>
	<script src="js/ladda.min.js"></script>
</head>

<body>

	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<?php if (!@$_GET['hidenav']): ?>
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="about" style="padding-top: 8px"><img alt="HamAlert" src="images/hamalert.png" style="width: 160px; height: 32px"></a>
				<?php else: ?>
				<a class="navbar-brand" href="#" style="padding-top: 8px"><img alt="HamAlert" src="images/hamalert.png" style="width: 160px; height: 32px"></a>
				<?php endif; ?>
			</div>
			<?php if (!@$_GET['hidenav']): ?>
			<div id="navbar" class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<?php if (@$_SESSION['user']): ?>
					<li><a href="triggers">Triggers</a></li>
					<li><a href="limits">Limits</a></li>
					<li><a href="destinations">Destinations</a></li>
					<li><a href="simulate">Simulate</a></li>
					<?php endif; ?>
					<li><a href="about">About</a></li>
					<li><a href="https://forum.hamalert.org/">Forum</a></li>
					<li><a href="help">Help</a></li>
					<li><a href="news">News</a></li>
					<li><a href="/stats">Stats</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<?php if (@$_SESSION['user']): ?>
					<?php if (!@$_SESSION['user']['alerts']): ?>
					<li><p class="navbar-text"><a href="account" style="color: #ffffff"><?php echo htmlspecialchars($_SESSION['user']['username']) ?></a>
							<a href="destinations" class="label label-danger" style="margin-left: 0.5em">Alerts disabled</a></p></li>
					<?php else: ?>
					<li><a href="account" style="color: #ffffff"><?php echo htmlspecialchars($_SESSION['user']['username']) ?></a></li>
					<?php endif; ?>
					<li><a href="logout">Logout</a></li>
					<?php else: ?>
					<li><a href="register">Register</a></li>
					<li><a href="login">Login</a></li>
					<?php endif; ?>
				</ul>
			</div>
			<?php endif; ?>
		</div>
	</nav>

	<div class="container">
