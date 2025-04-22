<?php
require_once("db.inc.php");

refreshUser();

$errors = array();
if (@$_POST['delete']) {
	deleteAccount();
	$deleteOk = true;
}

?>
<?php include('settings_begin.inc.php') ?>

<h1 class="page-header">Delete account</h1>

<?php if (@$deleteOk): ?>
<div class="alert alert-success" role="alert">
	<p>Your account has been deleted.</p>
</div>

<?php else: ?>
	
<div class="alert alert-danger" role="alert">
	<p>Are you sure you want to permanently delete your account <strong><?php echo htmlspecialchars($_SESSION['user']['username']) ?></strong> and all
	associated data (triggers, destinations etc.)? This operation cannot be undone!</p>
	
	<form method="post" style="margin-top: 1em">
		<button type="submit" name="delete" value="1" class="btn btn-danger">Delete account</button>
	</form>
</div>

<?php endif; ?>

<?php include('settings_end.inc.php') ?>
