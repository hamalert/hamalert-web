<?php

session_cache_limiter(false);
require_once("../db.inc.php");

addCacheHeader(time(), 86400);

header("Content-Type: application/json");

$assocs = getAllAssociations();

foreach ($assocs as &$assoc) {
	unset($assoc['_id']);
}

echo json_encode($assocs);
