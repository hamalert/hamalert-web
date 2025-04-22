<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

deleteAppToken($_POST['token']);

echo json_encode(true);
