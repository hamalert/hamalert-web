<?php

require_once("../db.inc.php");

header("Content-Type: application/json");

$req = json_decode(file_get_contents("php://input"), true);
$inputSpot = $req['spot'];

// Validate
$callsignRegex = "/^[A-Z0-9\/-]{3,16}$/i";	// allow dash for spotter calls
$modeRegex = "/^[a-z0-9]{2,8}$/";
$sourceRegex = "/^[a-z]{2,32}$/";
$frequencyRegex = "/^\d+\.\d+$/";
$summitRefRegex = "/^[a-zA-Z0-9]{1,8}\/[a-zA-Z]{2}\-(([0-9][0-9][1-9])|([0-9][1-9][0])|([1-9][0-9][0]))$/i";

$errors = [];
if (!preg_match($callsignRegex, $inputSpot['fullCallsign']))
	$errors[] = "Invalid callsign.";
if (!preg_match($callsignRegex, $inputSpot['spotter']))
	$errors[] = "Invalid spotter callsign.";
if (!preg_match($modeRegex, $inputSpot['mode']))
	$errors[] = "Invalid mode.";
if (!preg_match($sourceRegex, $inputSpot['source']))
	$errors[] = "Invalid source.";
if (!preg_match($frequencyRegex, $inputSpot['frequency']))
	$errors[] = "Invalid frequency.";
if (@$inputSpot['summitRef'] && !preg_match($summitRefRegex, $inputSpot['summitRef']))
	$errors[] = "Invalid summit reference.";

if ($errors) {
	echo json_encode(['success' => false, 'errors' => $errors]);
	exit;
}

$spot = [
	'user_id' => $_SESSION['user']['_id']->__toString(),
	'fullCallsign' => strtoupper($inputSpot['fullCallsign']),
	'spotter' => strtoupper($inputSpot['spotter']),
	'mode' => $inputSpot['mode'],
	'source' => $inputSpot['source'],
	'frequency' => $inputSpot['frequency']
];

if (@$inputSpot['summitRef']) {
	$spot['summitRef'] = strtoupper($inputSpot['summitRef']);
}

if (@$inputSpot['comment']) {
	$spot['comment'] = $inputSpot['comment'];
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
