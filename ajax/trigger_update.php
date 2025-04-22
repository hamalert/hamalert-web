<?php

require_once("../db.inc.php");

header("Content-Type: application/json");

$trigger = json_decode(file_get_contents("php://input"), true);

refreshUser();

// Validate
$arrayConditions = ['summitRef', 'callsign', 'fullCallsign', 'notCallsign', 'notFullCallsign', 'prefix', 'notPrefix', 'spotter', 'notSpotter', 'spotterPrefix', 'mode', 'band', 'source', 'dxcc', 'callsignDxcc', 'spotterDxcc', 'cq', 'spotterCq', 'continent', 'spotterContinent', 'qsl', 'state', 'spotterState', 'summitAssociation', 'summitRegion', 'iotaGroupRef', 'wwffDivision', 'wwffRef', 'bandslot'];
$arrayMaxEntries = 5000;
$errors = [];
if (!is_array($trigger['conditions']))
	bailout("Bad conditions array");
if (count($trigger['conditions']) == 0)
	bailout("At least one condition must be specified");
if (!@$trigger['_id'] && count(getAllTriggers()) >= $config['max_triggers'])
	bailout("At most {$config['max_triggers']} triggers per user may be defined.");

$haveNonTimeSpeedSnrSummitCondition = false;
foreach ($trigger['conditions'] as $condition => $value) {
	if (in_array($condition, $arrayConditions)) {
		 if (is_array($value) && $condition != "bandslot") {
			 if (count($value) > 5000) {
				 bailout("Lists may not have more than 5000 entries.");
			 } else if (count($value) == 0) {
				 bailout("Lists may not be empty.");
			 }
		 }
	} else if ($condition == "daysOfWeek") {
		 if (!is_array($value) || count($value) > 7) {
	 		bailout("Invalid value for $condition");
		 }
	} else if ($value === "" || $value === null || !is_scalar($value)) {
		bailout("Invalid value for $condition");
	}
	
	if (!in_array($condition, ['timeFrom', 'timeTo', 'daysOfWeek', 'speedFrom', 'speedTo', 'snr', 'summitPoints', 'summitActivations'])) {
		$haveNonTimeSpeedSnrSummitCondition = true;
	}
}

if (!$haveNonTimeSpeedSnrSummitCondition)
	bailout("At least one condition that doesn't apply to time, date, speed, SNR or summit points/activations must be added.");

if (!is_array($trigger['actions']))
	bailout("Bad actions array");
foreach ($trigger['actions'] as $action) {
	if (!is_string($action))
		bailout("Invalid action");
}

if (!is_string($trigger['comment']))
	bailout("Invalid value for comment");

// Fix clublog.lastUpdate option
if (@$trigger['options']['clublog']['lastUpdate']) {
	$trigger['options']['clublog']['lastUpdate'] = new MongoDB\BSON\UTCDateTime((int)$trigger['options']['clublog']['lastUpdate']['$date']['$numberLong']);
}

updateTrigger(@$trigger['_id'], $trigger['conditions'], $trigger['actions'], $trigger['comment'], $trigger['options']);

reloadMatcher();

echo json_encode(true);


function bailout($error) {
	header("HTTP/1.1 400 Bad Request");
	echo $error;
	exit;
}
