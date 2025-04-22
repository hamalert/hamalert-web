<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

setOptionsForAppToken($_POST['token'], null, $_POST['disable'] ? true : false);

echo json_encode(true);
