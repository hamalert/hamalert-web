<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

deleteSpot($_POST['id']);

echo json_encode(true);
