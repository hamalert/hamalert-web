<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

setOptionsForAppToken($_POST['token'], $_POST['sound'], $_POST['disable'] ? true : false);

echo json_encode(true);
