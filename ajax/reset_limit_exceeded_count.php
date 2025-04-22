<?php

require_once("../db.inc.php");

header("Content-Type: application/json");

resetLimitExceededCount();

echo json_encode(true);
