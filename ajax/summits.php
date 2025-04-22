<?php

session_cache_limiter(false);
require_once("../db.inc.php");

addCacheHeader(time(), 86400);

header("Content-Type: application/json");

$summitsDb = getSummitsForRegion($_GET['association'], $_GET['region']);
$summits = [];
foreach ($summitsDb as $summit) {
	$summits[] = [
		'Ref' => $summit['SummitCode'],
		'Name' => $summit['SummitName']
	];
}

echo json_encode($summits);
