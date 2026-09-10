<?php
/*
	Creates a local test user, for running the web app on its own (see README.md).

	Run inside the dev container:
	  docker exec hamalert-dev-web php tools/seedLocalUser.php [USERNAME] [PASSWORD]

	Defaults: HB9DQM / testpass123. Re-running with an existing username leaves it untouched.
*/
if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

$loginOptional = true;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/';
chdir(dirname(__DIR__));
require_once("db.inc.php");

$username = strtoupper($argv[1] ?? 'HB9DQM');
$password = $argv[2] ?? 'testpass123';

if (getUserForUsername($username)) {
	echo "User $username already exists, leaving it as is.\n";
	exit(0);
}

if (!createUser($username, password_hash($password, PASSWORD_DEFAULT), 'test@example.invalid')) {
	fwrite(STDERR, "Could not create user $username\n");
	exit(1);
}

echo "Created user $username with password \"$password\". Log in at {$config['self_url']}/login\n";
