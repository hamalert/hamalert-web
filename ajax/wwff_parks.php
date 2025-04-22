<?php

session_cache_limiter(false);
require_once("../db.inc.php");

addCacheHeader(time(), 86400);

header("Content-Type: application/json");

$parksDb = getWwffParksForDivision($_GET['division']);
$parks = [];
foreach ($parksDb as $park) {
	$parks[] = [
		'Ref' => $park['reference'],
		'Name' => $park['name']
	];
}

echo json_encode($parks);
