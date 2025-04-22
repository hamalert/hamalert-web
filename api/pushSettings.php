<?php

$isApi = true;
require_once("../db.inc.php");

header("Content-Type: application/json");

$result = null;
if (@$_SESSION['user']['appTokens']) {
	foreach ($_SESSION['user']['appTokens'] as $appToken) {
		if ($appToken['token'] === $_GET['token']) {
			$result = [
				'disable' => @$appToken['disable'] ? true : false,
				'sound' => @$appToken['sound'] ? @$appToken['sound'] : 'default'
			];
			break;
		}
	}
}

echo json_encode($result);
