<?php

require_once("config.inc.php");

// Check signature first
$payload = @$_REQUEST['sso'];
$mySig = hash_hmac('sha256', $payload, $config['discourse_connect_secret']);
if (@$_REQUEST['sig'] !== $mySig) {
	http_response_code(403);
	exit;
}

require_once("db.inc.php");

refreshUser();

$fields = [];
parse_str(base64_decode($payload), $fields);

$returnFields = [
	'nonce' => $fields['nonce'],
	'email' => $_SESSION['user']['accountEmail'],
	'external_id' => (string)$_SESSION['user']['_id'],
	'username' => $_SESSION['user']['username'],
	'name' => $_SESSION['user']['username']
];

$returnPayload = base64_encode(http_build_query($returnFields));
$returnSig = hash_hmac('sha256', $returnPayload, $config['discourse_connect_secret']);

header("Location: " . $fields['return_sso_url'] . "?sso=" . urlencode($returnPayload) . "&sig=" . urlencode($returnSig));
