<?php
$loginOptional = true;
require_once("db.inc.php");

$errors = [];

if (@$_GET['t']) {
	$signup = getSignup($_GET['t']);
	if (!$signup) {
		$errors[] = "Invalid/expired link, or already confirmed. Try <a href=\"login\">logging in</a> with the username and the password that you have chosen. If you cannot log in, try <a href=\"register\">registering</a> again.";
	}
} else {
	$errors[] = "Invalid link.";
}

if ($signup && @$_POST['confirm']) {
	$delres = deleteSignup($_GET['t']);
	if ($delres->getDeletedCount() == 1) {
		try {
			if (!createUser($signup['username'], $signup['password'], $signup['email'])) {
				$errors[] = "User creation failed.";
			}
		} catch (Exception $e) {
			if (preg_match("/E11000/", $e->getMessage())) {
				$errors[] = "This account has already been confirmed.";
			} else {
				$errors[] = "User creation failed.";
			}
		}
	}
	
	if (!@$errors) {
		$registerOk = "Your new account has been confirmed! You can now <a href=\"login\">log in</a> with the username <strong>{$signup['username']}</strong> and the password that you have chosen.";
	}
}

?>
<?php include('settings_begin.inc.php') ?>

<h1 class="page-header">Register</h1>

<?php foreach (@$errors as $error): ?>
<div class="alert alert-danger" role="alert">
	<?php echo $error ?>
</div>
<?php endforeach; ?>

<?php if (@$registerOk): ?>
<div class="alert alert-success alert-dismissible" role="alert">
	<?php echo $registerOk; ?>
</div>
<?php elseif ($signup): ?>
<form action="" method="POST">
<div class="alert alert-info" role="alert">Please confirm that you would like to create a HamAlert account with the username <strong><?php echo htmlspecialchars($signup['username']) ?></strong>.</div>
<button type="submit" name="confirm" value="1" class="btn btn-primary">Confirm</button>
</form>
<?php endif; ?>

<?php include('settings_end.inc.php') ?>
