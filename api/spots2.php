<?php

// API call for App version 1.0.3 and up

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

// Retrieve up to "limit" spots from the database within the last "maxAge" seconds and
// where the action includes 'app'
$spots = getSpots($_REQUEST['maxAge'], $_REQUEST['limit'], 'app');

// Fix receivedDate field
foreach ($spots as &$spot) {
	$spot['receivedDate'] = $spot['receivedDate']->toDateTime()->format(DateTime::ISO8601);

	// TEMP fix frequency: force number
	$spot['frequency'] = (float)$spot['frequency'];

	if (is_array(@$spot['state'])) {
		$spot['state'] = join(",", $spot['state']);
	}
}

resetBadgeCount();

$res = ['spots' => fixObjectIDs($spots)];

if (count($spots) == 0) {
	// Check if this user has any triggers with the app action enabled at all
	$triggers = getAllTriggers();
	$hasAnyAppTrigger = false;
	foreach ($triggers as $trigger) {
		if (in_array('app', $trigger['actions'])) {
			$hasAnyAppTrigger = true;
			break;
		}
	}
	$res['hasAnyAppTrigger'] = $hasAnyAppTrigger;
}

echo json_encode($res);
