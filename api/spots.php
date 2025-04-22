<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

// Retrieve up to "limit" spots from the database within the last "maxAge" seconds and
// where the action includes 'app'
$spots = getSpots($_REQUEST['maxAge'], $_REQUEST['limit'], 'app');

// Fix receivedDate field
foreach ($spots as &$spot) {
	$spot['receivedDate'] = $spot['receivedDate']->toDateTime()->format(DateTime::ISO8601);
}

resetBadgeCount();

echo json_encode(fixObjectIDs($spots));
