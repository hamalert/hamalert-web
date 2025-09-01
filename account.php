<?php
$pageTitle = 'Account';
require_once("db.inc.php");

refreshUser();

$errors = array();
if (@$_POST) {
	if (@$_GET['changePassword']) {
		if ($_POST['password'] !== $_POST['password2']) {
			$errors[] = "The passwords entered do not match.";
		} else if (strlen($_POST['password']) < 6) {
			$errors[] = "The password must be at least 6 characters long.";
		}
	
		if (!$errors) {
			setPassword($_POST['password']);
			$updatePassOk = true;
		}
	} else if (@$_GET['updateClublog']) {
		if (!$_POST['clublogEmail'] && !$_POST['clublogPassword']) {
			setClublogInfo(null, null);
			$updateClublogOk = true;
		} else {
			if (!$_POST['clublogEmail'] || !filter_var($_POST['clublogEmail'], FILTER_VALIDATE_EMAIL)) {
				$errors[] = "Please enter a Club Log account email address.";
			}
			if (!$_POST['clublogPassword'] || $_POST['clublogPassword'] == '••••••') {
				$errors[] = "Please enter a Club Log application password.";
			}
	
			if (!$errors) {
				setClublogInfo($_POST['clublogEmail'], $_POST['clublogPassword']);
				$updateClublogOk = true;
			}
		}
	} else if (@$_GET['updateAccountEmail']) {
		if (!filter_var($_POST['accountEmail'], FILTER_VALIDATE_EMAIL)) {
			$errors[] = "Please enter a valid account email address.";
		} else {
			sendChangeAccountEmail($_POST['accountEmail']);
			$changeEmailOk = true;
		}
	}
}

$mySpotHash = substr(hash_hmac("sha256", $_SESSION['user']['username'], $config['myspot_hashkey']), 0, 16);
$mySpotSrc = $config['self_url'] . "/myspot?c=" . $_SESSION['user']['username'] . "&h=$mySpotHash";

function sendChangeAccountEmail($email) {
	global $config;
	
	$username = $_SESSION['user']['username'];
	$hash = substr(hash_hmac('sha256', "$username$email", $config['change_email_hashkey']), 0, 32);
	$emailUrl = urlencode($email);

	$body = <<<EOD
Hello,

you have requested your account email address to be changed on HamAlert.
Please click the following link to effect the change:

{$config['self_url']}/changeEmail_confirm?u=$username&e=$emailUrl&h=$hash

73,

The HamAlert team

EOD;

	mail($email, "HamAlert account email change", $body, "From: {$config['mail_from']}\r\nReturn-Path: {$config['mail_return_path']}");
}

?>
<?php include('settings_begin.inc.php') ?>

<h1 class="page-header">Account <small><?php echo htmlspecialchars($_SESSION['user']['username']) ?> <small><a href="/change_username">change username</a></small></small></h1>

<?php foreach ($errors as $error): ?>
<div class="alert alert-danger" role="alert">
	<?php echo $error ?>
</div>
<?php endforeach; ?>

<?php if (@$updatePassOk): ?>
<div class="alert alert-success alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	The password has been updated successfully.
</div>
<?php endif; ?>

<?php if (@$updateClublogOk): ?>
<div class="alert alert-success alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	The Club Log settings have been updated successfully.
</div>
<?php endif; ?>

<?php if (@$changeEmailOk): ?>
<div class="alert alert-info alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	Please check your email and click the link in the confirmation message to finalize your account email address change.
</div>
<?php endif; ?>

<?php if (@$_GET['updatedAccountEmail']): ?>
<div class="alert alert-success alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	Your account email address has been changed successfully.
</div>
<?php endif; ?>

<fieldset>
	<legend>Account email</legend>

	<div class="form-group">
		<form class="limit-width" method="post" action="?updateAccountEmail=1" autocomplete="off">
		<div class="form-group">
			<label for="email">Account email address</label>
			<?php if (@$changeEmailOk): ?>
			<input type="email" class="form-control" id="accountEmail" name="accountEmail" value="<?php echo htmlspecialchars(@$_POST['accountEmail']) ?>" disabled="disabled">
			<?php else: ?>
			<input type="email" class="form-control" id="accountEmail" name="accountEmail" value="<?php echo htmlspecialchars(@$_SESSION['user']['accountEmail']) ?>">
			<?php endif; ?>
			<p class="help-block">The account email address is only used for important announcements regarding HamAlert, and to retrieve lost passwords. It will not be used for alerts.</p>
		</div>
		<button type="submit" name="saveAccountEmail" value="1" class="btn btn-primary">Save</button>
	</form>
	</div>
</fieldset>

<fieldset>
	<legend>Club Log</legend>

	<?php if (@$_SESSION['user']['clublog']['invalid']): ?>
	<div class="alert alert-warning" role="alert">
		The Club Log credentials that you have entered are invalid. Club Log updates have been disabled.
		Please set your Club Log email and password again to restart them.
	</div>
	<?php endif; ?>

	<form class="limit-width" method="post" action="?updateClublog=1" autocomplete="off">
		<div class="form-group">
			<label for="email">Email</label>
			<input type="email" class="form-control" id="clublogEmail" name="clublogEmail" value="<?php echo htmlspecialchars(@$_SESSION['user']['clublog']['email'] ?? "") ?>">
		</div>
		<div class="form-group">
			<label for="password">Password</label>
			<input type="text" class="form-control" id="clublogPassword" name="clublogPassword" value="<?php if (@$_SESSION['user']['clublog']['password']) echo "••••••" ?>">
			<p class="help-block">Please create an <a href="https://clublog.org/edituser.php?tab=7">Application Password</a> in your Club Log account settings
				instead of entering your regular Club Log password here.<br />
				Linking your Club Log account will let you use “band slot” conditions.</p>
		</div>
		<button type="submit" name="saveClublog" value="1" class="btn btn-primary">Save</button>
	</form>
	<p></p>
</fieldset>

<fieldset>
	<legend>MySpot</legend>
	
	<img src="<?php echo htmlspecialchars($mySpotSrc)?>" srcset="<?php echo htmlspecialchars($mySpotSrc)?>&amp;hr=1 2x" style="margin-right: 16px; margin-bottom: 16px" />
	<img src="<?php echo htmlspecialchars($mySpotSrc)?>&amp;dark=1" srcset="<?php echo htmlspecialchars($mySpotSrc)?>&amp;dark=1&amp;hr=1 2x" style="margin-bottom: 16px" />
	
	<p class="help-block">MySpot is a dynamic image that you can embed in your website, QRZ.com profile etc.
		It shows information from the latest spot for your callsign, if there has been one within the last hour (configurable, see below).
		Right-click the desired image and copy the URL, or use the HTML code below.
		To test it, <a href="simulate">simulate</a> a spot for your callsign, then come back to this page.</p>
	
	<p>HTML code for light image:<br /><code>&lt;img src="<?php echo htmlspecialchars($mySpotSrc)?>" srcset="<?php echo htmlspecialchars($mySpotSrc)?>&amp;hr=1 2x" /&gt;</code></p>
	
	<p>HTML code for dark image:<br /><code>&lt;img src="<?php echo htmlspecialchars($mySpotSrc)?>&amp;dark=1" srcset="<?php echo htmlspecialchars($mySpotSrc)?>&amp;dark=1&amp;hr=1 2x" /&gt;</code></p>
	
	<p class="help-block">The srcset attribute ensures that a high-resolution version of the image is rendered on displays that support it (“Retina” displays).</p>

	<p class="help-block">To show spots from a longer period than the last hour, you can specify the number of hours by appending, for example, <code>&amp;a=24</code>
		to the URL in order to use spots from the last 24 hours. You can use any number of hours, as the last spot will be saved indefinitely, and the latest spot always
		overwrites the current spot.</p>
	
	<form method="post" action="myspot_clear" style="margin-bottom: 1em">
		<button type="submit" class="btn btn-warning btn-xs">Clear MySpot</button>
	</form>
</fieldset>


<fieldset>
	<legend>Change password</legend>

	<form class="limit-width" method="post" action="?changePassword=1">
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

</fieldset>

<form method="get" action="account_delete">
	<div style="text-align: right">
		<button type="submit" class="btn btn-danger">Delete account</button>
	</div>
</form>

<?php include('settings_end.inc.php') ?>
