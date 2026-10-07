<?php
require_once("db.inc.php");

refreshUser();

$username = $_SESSION['user']['username'];
$email = $_GET['e'] ?? '';
$ts = $_GET['ts'] ?? '';
$providedHash = $_GET['h'] ?? '';
if ($username !== ($_GET['u'] ?? '') || !is_string($email) || !is_string($ts) || !is_string($providedHash) || !preg_match('/^\d+$/', $ts)) {
	die("Bad link or wrong account");
}

$age = time() - (int)$ts;
$hash = substr(hash_hmac('sha256', $username . "\n" . $email . "\n" . $ts, $config['change_email_hashkey']), 0, 32);
if ($age < 0 || $age > $config['forgotpass_link_expiration'] || !hash_equals($hash, $providedHash)) {
	die("Bad or expired link");
}

if (!isAccountEmailAvailable($email, $_SESSION['user']['_id'])) {
	header("Location: /account?accountEmailTaken=1");
	exit;
}

$oldEmail = $_SESSION['user']['accountEmail'] ?? '';
try {
	setAccountEmail($email);
} catch (MongoDB\Driver\Exception\BulkWriteException $e) {
	if ($e->getCode() !== 11000) {
		throw $e;
	}
	header("Location: /account?accountEmailTaken=1");
	exit;
}
if ($oldEmail && is_string($oldEmail) && strcasecmp($oldEmail, $email) !== 0) {
	sendEmailChangedNotice($oldEmail, $username, $email);
}
header("Location: /account?updatedAccountEmail=1");

function sendEmailChangedNotice($oldEmail, $username, $email) {
	global $config;

	$body = <<<EOD
Hello,

the email address on your HamAlert account $username was changed to $email.

If you did not make this change, reset your password and contact the HamAlert administrator.

73,

The HamAlert team

EOD;

	mail($oldEmail, "HamAlert account email address changed", $body, "From: {$config['mail_from']}\r\nReturn-Path: {$config['mail_return_path']}");
}
