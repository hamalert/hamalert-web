<?php
$loginOptional = true;
require_once("db.inc.php");

$callsign = @$_GET['c'];
if (!$callsign) {
	http_response_code(400);
	exit;
}

header("Content-Type: application/json");

$spot = getMySpotForCallsign($callsign, 7 * 86400);
if (!$spot) {
	echo "{}";
} else {
	recursive_unset($spot, '_id');
	$spot['receivedDate'] = $spot['receivedDate']->toDateTime()->format("c");
	echo json_encode($spot);
}


function recursive_unset(&$array, $unwanted_key) {
	unset($array[$unwanted_key]);
	foreach ($array as &$value) {
		if (is_array($value)) {
			recursive_unset($value, $unwanted_key);
		}
	}
}