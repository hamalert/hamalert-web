<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

addAppToken($_POST['token'], $_POST['type'], $_POST['deviceName']);

echo json_encode(true);
