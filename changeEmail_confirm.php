<?php
require_once("db.inc.php");

refreshUser();

$username = $_SESSION['user']['username'];
if ($username !== $_GET['u']) {
	die("Bad link or wrong account");
}
$email = $_GET['e'];
$hash = substr(hash_hmac('sha256', "$username$email", $config['change_email_hashkey']), 0, 32);
if ($hash !== $_GET['h']) {
	die("Bad link");
}

setAccountEmail($email);
header("Location: /account?updatedAccountEmail=1");
