<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

$result = null;
if (@$_SESSION['user']['appTokens']) {
	foreach ($_SESSION['user']['appTokens'] as $appToken) {
		if ($appToken['token'] === $_GET['token']) {
			$result = @$appToken['disable'] ? true : false;
			break;
		}
	}
}

echo json_encode($result);
