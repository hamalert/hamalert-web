<?php

session_cache_limiter(false);
require_once("../db.inc.php");

addCacheHeader(time(), 86400);

header("Content-Type: application/json");

$iotaGroupsDb = getAllIotaGroups();
$iotaGroups = [];
foreach ($iotaGroupsDb as $iotaGroup) {
	$group = [
		'ref' => $iotaGroup['grpRef'],
		'name' => $iotaGroup['grpName']
	];
	if (@$iotaGroup['grpComment'])
		$group['comment'] = $iotaGroup['grpComment'];
	$iotaGroups[] = $group;
}

echo json_encode($iotaGroups);
