<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

clearMutes();

echo json_encode(true);
