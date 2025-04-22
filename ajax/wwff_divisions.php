<?php

session_cache_limiter(false);
require_once("../db.inc.php");

addCacheHeader(time(), 86400);

header("Content-Type: application/json");

$divisions = getAllWwffDivisions();

foreach ($divisions as &$division) {
	unset($division['_id']);
}

echo json_encode($divisions);
