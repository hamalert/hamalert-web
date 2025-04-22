<?php
$loginOptional = true;
require_once("db.inc.php");

$errors = [];
if (!checkHash()) {
	$errors[] = "Invalid or expired link.";
} else {
	$canReset = true;
	if ($_POST) {
		if ($_POST['password'] !== $_POST['password2']) {
			$errors[] = "The passwords entered do not match.";
		} else if (strlen($_POST['password']) < 6) {
			$errors[] = "The password must be at least 6 characters long.";
		}
	
		if (!$errors) {
			resetPassword($_GET['u'], $_POST['password']);
			$resetOk = true;
		}
	}
}

function checkHash() {
	global $config;
	$h = substr(hash_hmac("sha256", $_GET['u'] . "-" . $_GET['ts'], $config['forgotpass_hashkey']), 0, 32);
	if ($h === $_GET['h'] && (time() - $_GET['ts']) <= $config['forgotpass_link_expiration'])
		return true;
	else
		return false;
}

?>
<?php include('settings_begin.inc.php') ?>

<h1 class="page-header">Reset password</h1>

<?php foreach ($errors as $error): ?>
<div class="alert alert-danger" role="alert">
	<?php echo $error ?>
</div>
<?php endforeach; ?>

<?php if (@$resetOk): ?>
<div class="alert alert-success alert-dismissible" role="alert">
	Your password has been reset successfully. You can now log in with your new password.
</div>

<?php elseif (@$canReset): ?>

<form method="post" class="limit-width">
	<div class="form-group">
		<label for="password">New password</label>
		<input type="password" class="form-control" id="password" name="password">
	</div>
	<div class="form-group">
		<label for="password">Confirm password</label>
		<input type="password" class="form-control" id="password2" name="password2">
	</div>
	<button type="submit" class="btn btn-primary">Save</button>
</form>

<?php endif; ?>

<?php include('settings_end.inc.php') ?>
