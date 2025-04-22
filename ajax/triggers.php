<?php

require_once("../db.inc.php");

header("Content-Type: application/json");

$triggers = getAllTriggers();

usort($triggers, "cmpTrigger");

function cmpTrigger($a, $b) {
	return strcasecmp($a['comment'], $b['comment']);
}

echo json_encode(fixObjectIDs($triggers));
