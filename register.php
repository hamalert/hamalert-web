<?php
$loginOptional = true;
if (@$_GET['hidenav']) {
	$userScalable = 'no';
}
require_once("db.inc.php");

$errors = [];
if ($_POST) {
	$email = @$_POST['email'];
	$username = strtoupper(@$_POST['username'] ?? "");
	$password = @$_POST['password'];
	$password2 = @$_POST['password2'];
	$riddle = @$_POST['riddle'];

	if ($email && $username && $password && $password2 && $riddle) {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = "The specified email address is invalid.";
		}
		if (!preg_match("/^[A-Z0-9-]{3,16}$/", $username)) {
			$errors[] = "The username may only consist of the letters A-Z, 0-9 and -, and must be between 3 and 16 characters long.";
		}
		if (!isUsernameAvailable($username)) {
			$errors[] = "This username is already taken.";
		}
		if (getUserForAccountEmail($email)) {
			$errors[] = "An account for this email address already exists.";
		}
		if ($password !== $password2) {
			$errors[] = "The passwords do not match.";
		}
		if (strlen($password) < 6) {
			$errors[] = "The password must be at least 6 characters long.";
		}
		if (trim(strtolower($riddle)) !== "qrp") {
			$errors[] = "Please answer the question correctly.";
		}
		
		if (!$errors) {
			$token = bin2hex(openssl_random_pseudo_bytes(16));
			saveSignup($token, $email, $username, $password);
			sendSignupMail($token, $email);
			$registerOk = true;
		}
	} else {
		$errors[] = "Please fill in all fields.";
	}
}

function sendSignupMail($token, $email) {
	global $config;
	
	$body = <<<EOD
Hello,

you have requested an account to be created on HamAlert. Please click the following link to activate your account:

{$config['self_url']}/reg_confirm?t=$token

This link expires after 24 hours.

73,

The HamAlert team

EOD;

	mail($email, "HamAlert account creation", $body, "From: {$config['mail_from']}\r\nReturn-Path: {$config['mail_return_path']}");
}

?>
<?php include('settings_begin.inc.php') ?>

<h1 class="page-header">Register</h1>

<?php include("detect_ie.inc.php") ?>

<?php foreach ($errors as $error): ?>
<div class="alert alert-danger" role="alert">
	<?php echo $error ?>
</div>
<?php endforeach; ?>

<?php if (@$registerOk): ?>
<div class="alert alert-success alert-dismissible" role="alert">
	Your registration was successful, but your account is not active yet. Please check your email for a confirmation message,
	and click the link in the message to activate your account. If you do not get an email in the next few minutes, check your spam folder.
</div>

<?php else: ?>

<?php /*
<div class="alert alert-warning" role="alert">
	<p>AT&T appears to be blocking email from HamAlert. If you are on an AT&T domain (e.g. att.net, sbcglobal.net, bellsouth.net etc.) and 
		do not receive your registration confirmation email, please use another email address.</p>
</div>
*/ ?>

<form id="loginForm" class="hide-ie limit-width" method="post">
	<div class="form-group">
		<label for="email">Email address</label>
		<input type="email" class="form-control" id="email" name="email" placeholder="user@domain.com" value="<?php echo htmlspecialchars(@$email ?? "") ?>" />
		<p class="help-block">You will receive an email with a link to confirm your address.</p>
	</div>
	<div class="form-group">
		<label for="username">Desired username</label>
		<input type="text" class="form-control" id="username" name="username" placeholder="YOURCALL" value="<?php echo htmlspecialchars(@$username ?? "") ?>" style="text-transform: uppercase" />
		<p class="help-block">In most cases, you should use your amateur radio callsign as the username.</p>
	</div>
	<div class="form-group">
		<label for="username">Password</label>
		<input type="password" class="form-control" id="password" name="password" />
		<p class="help-block">Minimum length: 6 characters.</p>
	</div>
	<div class="form-group">
		<label for="username">Confirm password</label>
		<input type="password" class="form-control" id="password2" name="password2" />
	</div>
	<div class="form-group">
		<label for="riddle">Which Q code means “low power” in amateur radio?</label>
		<input type="text" class="form-control" id="riddle" name="riddle" value="<?php echo htmlspecialchars(@$riddle ?? "") ?>" />
		<p class="help-block">This is just a simple measure to keep out the spam bots.</p>
	</div>
	<button type="submit" id="submit" class="btn btn-primary">Register</button>
</form>

<script type="text/javascript">
	$('#loginForm').on('submit',function(e){
		var $form = $(this);

		if ($form.data('submitted') === true) {
			e.preventDefault();
		} else {
			$form.data('submitted', true);
		}
	});
</script>

<?php endif; ?>

<?php include('settings_end.inc.php') ?>
