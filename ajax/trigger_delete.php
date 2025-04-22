<?php

require_once("../db.inc.php");

header("Content-Type: application/json");

$res = deleteTrigger($_POST['id']);

reloadMatcher();

echo json_encode($res == 1);
