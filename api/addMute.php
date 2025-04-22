<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

addMute($_POST['callsign'], @$_POST['band'] ? $_POST['band'] : null, @$_POST['mode'] ? $_POST['mode'] : null,
	@$_POST['summitRef'] ? $_POST['summitRef'] : null, $_POST['ttl']);

echo json_encode(true);
