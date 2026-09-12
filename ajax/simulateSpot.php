<?php

require_once("../db.inc.php");

header("Content-Type: application/json");

$req = json_decode(file_get_contents("php://input"), true);
$inputSpot = $req['spot'];

// Validate
$callsignRegex = "/^[A-Z0-9\/-]{3,16}$/i";	// allow dash for spotter calls
$modeRegex = "/^[a-z0-9]{2,8}$/";
$sourceRegex = "/^[a-z]{2,32}$/";
$frequencyRegex = "/^\d+\.?(?:\d+)?$/";
$summitRefRegex = "/^[a-zA-Z0-9]{1,8}\/[a-zA-Z]{2}\-(([0-9][0-9][1-9])|([0-9][1-9][0])|([1-9][0-9][0]))$/i";
$dvEventRegex = "/^(active|linked)$/";
$dvNodeRegex = "/^[A-Z0-9]{3,7}(-[A-Z])?$/";	// also used for reflectors

$errors = [];
if (!preg_match($callsignRegex, $inputSpot['fullCallsign']))
	$errors[] = "Invalid callsign.";
if (!preg_match($callsignRegex, $inputSpot['spotter']))
	$errors[] = "Invalid spotter callsign.";
if (!preg_match($modeRegex, $inputSpot['mode']))
	$errors[] = "Invalid mode.";
if (!preg_match($sourceRegex, $inputSpot['source']))
	$errors[] = "Invalid source.";
// Frequency is optional for D-STAR spots (mode 'dstar'; source names the feed instead)
if (@$inputSpot['frequency'] || $inputSpot['mode'] != 'dstar') {
	if (!preg_match($frequencyRegex, @$inputSpot['frequency']))
		$errors[] = "Invalid frequency.";
}
if (@$inputSpot['summitRef'] && !preg_match($summitRefRegex, $inputSpot['summitRef']))
	$errors[] = "Invalid summit reference.";
if (@$inputSpot['dvEvent'] && !preg_match($dvEventRegex, $inputSpot['dvEvent']))
	$errors[] = "Invalid D-STAR event.";
if (@$inputSpot['dvNode'] && !preg_match($dvNodeRegex, strtoupper($inputSpot['dvNode'])))
	$errors[] = "Invalid D-STAR node.";
if (@$inputSpot['dvReflector'] && !preg_match($dvNodeRegex, strtoupper($inputSpot['dvReflector'])))
	$errors[] = "Invalid D-STAR reflector.";

if ($errors) {
	echo json_encode(['success' => false, 'errors' => $errors]);
	exit;
}

$spot = [
	'user_id' => $_SESSION['user']['_id']->__toString(),
	'fullCallsign' => strtoupper($inputSpot['fullCallsign']),
	'spotter' => strtoupper($inputSpot['spotter']),
	'mode' => $inputSpot['mode'],
	'source' => $inputSpot['source']
];

if (@$inputSpot['frequency']) {
	$spot['frequency'] = $inputSpot['frequency'];
}

if (@$inputSpot['summitRef']) {
	$spot['summitRef'] = strtoupper($inputSpot['summitRef']);
}

if (@$inputSpot['comment']) {
	$spot['comment'] = $inputSpot['comment'];
}

// dv* fields (D-STAR event/node/reflector) are ignored unless mode is 'dstar', regardless of
// what the client sent - source only names the feed (quadnet/ircddb/dstarusers).
if ($inputSpot['mode'] == 'dstar') {
	if (@$inputSpot['dvEvent']) {
		$spot['dvEvent'] = $inputSpot['dvEvent'];
	}
	if (@$inputSpot['dvNode']) {
		$spot['dvNode'] = strtoupper($inputSpot['dvNode']);
	}
	if (@$inputSpot['dvReflector']) {
		$spot['dvReflector'] = strtoupper($inputSpot['dvReflector']);
	}
}

// Send to backend
$curl = curl_init($config['simulate_spot_url']);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($spot));

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($status != 200) {
	echo json_encode(['success' => false, 'errors' => ['Could not send spot to backend']]);
} else {
	echo json_encode(['success' => true]);
}

curl_close($curl);
