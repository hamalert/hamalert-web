<?php

require_once("../db.inc.php");

header("Content-Type: application/json");

$res = deleteAppToken($_POST['token']);

echo json_encode($res == 1);
