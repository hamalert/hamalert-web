<?php

require_once(__DIR__ . "/../config.inc.php");
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../ajax/sota-api.inc.php");

chdir(__DIR__);

$mongoClient = new MongoDB\Client($config['mongodb_uri'], [], [
	'typeMap' => [
		'root' => 'array',
		'document' => 'array',
		'array' => 'array',
	],
	]);
$db = $mongoClient->hamalert;

$triggers = $db->triggers->find(['options.autoUpdateSotaCompleteCandidates' => true])->toArray();

$start = time();
foreach ($triggers as $trigger) {
	if (!isset($trigger['conditions']['summitRef']))
		continue;
	
	$user = $db->users->findOne(['_id' => $trigger['user_id']]);
	if (!$user)
		continue;
	
	$call = $user['username'];
	if (!preg_match("/^[A-Z0-9-]{3,12}$/", $call)) {
		die("Invalid call");
	}

	$completeCandidates = getCompleteCandidatesForCallsign($call);

	// Filter complete candidates to only include valid summits
	$completeCandidates = filterValidSummits($completeCandidates);
	if ($completeCandidates) {
		$db->triggers->updateOne(['_id' => $trigger['_id']], ['$set' => ['conditions.summitRef' => $completeCandidates]]);
	}
	
	sleep(5);
	if ((time() - $start) > 7200) {
		echo "SOTA complete candidate update is taking too long; consider reducing sleep\n";
		exit(1);
	}
}

function filterValidSummits($summitRefList) {
	global $db;
	$summits = $db->summits->find([
		'SummitCode' => ['$in' => $summitRefList], 
		'ValidTo' => ['$gte' => new MongoDB\BSON\UTCDateTime()]
	], ['sort' => ['SummitCode' => 1], 'projection' => ['_id' => 0, 'SummitCode' => 1]]);
	
	$summitRefsFiltered = [];
	foreach ($summits as $summit) {
		$summitRefsFiltered[] = $summit['SummitCode'];
	}
	return $summitRefsFiltered;
}

?>
