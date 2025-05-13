<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

if ($_POST['id'] === '*') {
	deleteAllSpots();
} else {
	deleteSpot($_POST['id']);
}

echo json_encode(true);
