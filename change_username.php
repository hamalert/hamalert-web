<?php
require_once("db.inc.php");

refreshUser();

$errors = [];
if (@$_POST) {
	if (@$_POST['changeUsername']) {
		$username = strtoupper($_POST['username']);
		if (!preg_match("/^[A-Z0-9-]{3,16}$/", $username)) {
			$errors[] = "The username may only consist of the letters A-Z, 0-9 and -, and must be between 3 and 16 characters long.";
		}
		if (!isUsernameAvailable($username)) {
			$errors[] = "This username is alredy taken.";
		}
	
		if (!$errors) {
			changeUsername($username);
			session_destroy();
			$changeUsernameOk = true;
		}
	}
}

?>
<?php include('settings_begin.inc.php') ?>

<h1 class="page-header">Change username</h1>

<?php foreach ($errors as $error): ?>
<div class="alert alert-danger" role="alert">
	<?php echo $error ?>
</div>
<?php endforeach; ?>

<?php if (@$changeUsernameOk): ?>
<div class="alert alert-info alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	Your username has been changed successfully. You have been logged out.
	<p><a href="/login">Login again</a></p>
</div>

<?php else: ?>

<fieldset>
	<div class="form-group">
		<form class="limit-width" method="post" action="" autocomplete="off">
		<div class="form-group">
			<label for="username">Username</label>
			<?php if (@$_POST['username']): ?>
			<input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars(@$_POST['username']) ?>">
			<?php else: ?>
			<input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars(@$_SESSION['user']['username']) ?>">
			<?php endif; ?>
		</div>
		<button type="submit" name="changeUsername" value="1" class="btn btn-primary">Save</button>
	</form>
	</div>
</fieldset>

<?php endif; ?>

<?php include('settings_end.inc.php') ?>
