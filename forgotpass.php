<?php
$loginOptional = true;
$pageTitle = 'Forgot password';
require_once("db.inc.php");

$errors = [];
if ($_POST) {
	$email = $_POST['email'];
	$username = @$_POST['username'];
	
	if ($email) {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = "The specified email address is invalid.";
		} else {
			$user = getUserForAccountEmail($email);
		}
	}
	if (!$user && $username) {
		$user = getUserForUsername(trim(strtoupper($username)));
	}
	
	if ($user) {
		$ts = time();
		$h = substr(hash_hmac("sha256", $user['username'] . "-" . $ts, $config['forgotpass_hashkey']), 0, 32);
		sendResetMail($ts, $h, $user['username'], $user['accountEmail']);
	}
	$resetOk = true;	// don't leak information if user/email exists
}

function sendResetMail($ts, $h, $username, $email) {
	global $config;
	
	$body = <<<EOD
Hello,

you have requested to reset the password for your HamAlert account.
Please click the following link to reset the password for your account $username:

{$config['self_url']}/forgotpass_confirm?u=$username&ts=$ts&h=$h

73,

The HamAlert team

EOD;

	mail($email, "HamAlert password reset", $body, "From: {$config['mail_from']}\r\nReturn-Path: {$config['mail_return_path']}");
}

?>
<?php include('settings_begin.inc.php') ?>

<h1 class="page-header">Forgot password</h1>

<?php foreach ($errors as $error): ?>
<div class="alert alert-danger" role="alert">
	<?php echo $error ?>
</div>
<?php endforeach; ?>

<?php if (@$resetOk): ?>
<div class="alert alert-success alert-dismissible" role="alert">
	If an account was found for your username or email address, then an email with instructions to reset your password has been sent to your address. If you do not get an email in the next few minutes, check your spam folder and make sure that you entered the right username or email address.
</div>

<?php else: ?>

<div class="alert alert-info" role="alert">
	Enter <strong>either</strong> your username or your email address to reset your HamAlert password.
</div>

<form method="post" class="limit-width">
	<div class="form-group">
		<label for="email">Username/Callsign</label>
		<input type="text" class="form-control" id="username" name="username" placeholder="YOURCALL" value="<?php echo htmlspecialchars(@$username ?? "") ?>" style="text-transform: uppercase" />
		<p class="help-block">Enter the username that you used when you created your account.</p>
	</div>
	<div class="form-group">
		<label for="email">Email address</label>
		<input type="email" class="form-control" id="email" name="email" placeholder="user@domain.com" value="<?php echo htmlspecialchars(@$email ?? "") ?>" />
		<p class="help-block">Alternatively, enter the email address that you used when you created your account.</p>
	</div>
	<button type="submit" class="btn btn-primary">Reset password</button>
</form>

<?php endif; ?>

<?php include('settings_end.inc.php') ?>
