<?php

require_once("../db.inc.php");

header("Content-Type: application/json");

$res = disableTrigger($_POST['id'], $_POST['disabled']);

reloadMatcher();

echo json_encode($res == 1);
