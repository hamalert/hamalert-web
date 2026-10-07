<?php

$loginOptional = true;
require_once("db.inc.php");

if (@$_SESSION['user']) {
	redirectAfterLogin();
}

if (@$_REQUEST['username'] && @$_REQUEST['password']) {
	$loginResult = checkLogin(strtoupper($_REQUEST['username']), $_REQUEST['password']);
	if ($loginResult) {
		redirectAfterLogin();
	}
}

// Only follow goto when it is a same-site path. Reject absolute URLs,
// protocol-relative URLs (//evil.example, ///evil.example), and backslash
// variants that browsers normalize into those.
function redirectAfterLogin() {
	$goto = $_GET['goto'] ?? '';
	$parts = is_string($goto) ? parse_url($goto) : false;
	$path = is_array($parts) ? ($parts['path'] ?? '') : '';
	$safe = is_array($parts)
		&& !isset($parts['scheme'])
		&& !isset($parts['host'])
		&& !isset($parts['user'])
		&& !preg_match('/[[:cntrl:]\\\\]/', $goto)
		&& preg_match('#\A/[A-Za-z0-9_]*\z#', $path);
	header('Location: ' . ($safe ? $goto : 'triggers'));
	exit;
}

?>
<?php include('settings_begin.inc.php') ?>

<?php include("detect_ie.inc.php") ?>

<div id="halogo" class="container">
	<img src="images/hamalert_wb.png" alt="HamAlert" srcset="images/hamalert_wb@2x.png 2x" />
	
	<p>Push notifications for amateur radio spots</p>
</div>

<div class="container hide-ie">
	<?php /*
	<div style="text-align: center; margin-top: 1em">
		<div class="alert alert-warning" role="alert" style="display: inline-block; margin-bottom: 0"> 
			Note: The PSK Reporter source is <a href="news">currently disabled</a>.
		</div>
	</div>
	*/ ?>

	<form class="form-signin" method="post">
		<?php if (@$loginResult === false): ?>
		<div class="alert alert-danger" role="alert">
			Login failed; please check username and password. <strong>Make sure to enter your username/callsign, not your email address!</strong>
		</div>
		<?php endif; ?>
		
		<label for="username" class="sr-only">Username</label>
		<input type="text" id="username" name="username" class="form-control" placeholder="Username/Callsign" required autofocus style="text-transform: uppercase" />
		<label for="password" class="sr-only">Password</label>
		<input type="password" id="password" name="password" class="form-control" placeholder="Password" required />
		<button class="btn btn-lg btn-primary btn-block" type="submit">Log in</button>
		<a href="register" class="btn btn-lg btn-default btn-block">Register</a>
		
		<p style="float: right; margin-top: 1em"><a href="forgotpass">Forgot password</a></p>
	</form>

</div> <!-- /container -->

<?php include('settings_end.inc.php') ?>
