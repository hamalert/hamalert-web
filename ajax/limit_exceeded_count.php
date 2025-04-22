<?php

require_once("../db.inc.php");

header("Content-Type: application/json");

refreshUser();

$res = [
	'limitExceededCount' => @$_SESSION['user']['limitExceededCount'] ? $_SESSION['user']['limitExceededCount'] : 0
];

if (@$_SESSION['user']['limitExceededCountSince']) {
	$res['limitExceededCountSince'] = $_SESSION['user']['limitExceededCountSince']->__toString();
}

echo json_encode($res);
