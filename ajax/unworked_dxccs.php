<?php

session_cache_limiter(false);
require_once("../db.inc.php");
require_once("../config.inc.php");

$mode = @$config['clublog']['modeValues'][@$_GET['mode']];

if (!preg_match("/^[A-Z0-9\/]{3,16}$/", @$_GET['call'])) {
	bailout("Invalid callsign");
}
if (!isset($mode)) {
	bailout("Invalid mode");
}
if (!@$_SESSION['user']['clublog']['email'] || !$_SESSION['user']['clublog']['password']) {
	bailout("Missing Club Log account information");
}

$statuses = [2];
if (@$_GET['status']) {
	$statuses = [];
	$statusesGet = explode(",", $_GET['status']);
	foreach ($statusesGet as $s) {
		if (@$config['clublog']['qslStatusValues'][$s])
			$statuses[] = $config['clublog']['qslStatusValues'][$s];
	}
}

$unworkedDxccs = [];
$allDxccs = getAllDxccs();

$options = [
	'socket' => [
		'bindto' => '0:0'
	]
];
$context  = stream_context_create($options);

$clubLogResponse = @file_get_contents("https://clublog.org/json_dxccchart.php?" . 
	http_build_query([
	'call' => $_GET['call'],
	'mode' => $mode,
	'date' => $_GET['date'],
	'api' => $config['clublog']['apikey'],
	'email' => $_SESSION['user']['clublog']['email'],
	'password' => $_SESSION['user']['clublog']['password']
]), false, $context);
if (!$clubLogResponse) {
	bailout("Cannot get DXCC matrix from Club Log (check email and password in account settings).");
}
$clubLogMatrix = json_decode($clubLogResponse, true);
if (!is_array($clubLogMatrix)) {
	bailout("Cannot get DXCC matrix from Club Log (check email and password in account settings).");
}

foreach ($allDxccs as $dxcc) {
	$matrixStatus = @$clubLogMatrix[$dxcc['dxcc']];

	$isUnworked = true;
	if ($matrixStatus) {
		foreach ($matrixStatus as $band => $status) {
			if (in_array($status, $statuses)) {
				$isUnworked = false;
				break;
			}
		}
	}

	if ($isUnworked)
		$unworkedDxccs[] = $dxcc['dxcc'];
}

sort($unworkedDxccs);

header("Content-Type: application/json");
echo json_encode($unworkedDxccs);


function bailout($message) {
	header("HTTP/1.1 400 Bad Request");
	echo $message;
	exit;
}
