<?php

require_once("../db.inc.php");
require_once("sota-api.inc.php");

header("Content-Type: application/json");

$call = $_SESSION['user']['username'];
if (!preg_match("/^[A-Z0-9-]{3,12}$/", $call)) {
	die("Invalid call");
}

$completeCandidates = getCompleteCandidatesForCallsign($call);

// Filter complete candidates to only include valid summits
$completeCandidates = filterValidSummits($completeCandidates);

echo json_encode($completeCandidates);
