<?php

$cacheFile = "/tmp/activator_userids_" . posix_getuid() . ".json";
$cacheUpdated = false;

function getActivationsForCallsign($callsign) {
	$userId = getUserIdForCallsign($callsign);
	if (!$userId) {
		return null;
	}
	$activations = json_decode(file_get_contents("https://api-db2.sota.org.uk/logs/activator/$userId/99999/1"), true);
	if (!$activations) {
		return [];
	}
	$ret = [];
	foreach ($activations as $activation) {
		$matches = [];
		preg_match("/^(\S+) \((.*)\)$/", $activation['Summit'], $matches);
		$ret[] = [
			'date' => $activation['ActivationDate'],
			'summitref' => $matches[1],
			'summitname' => $matches[2],
			'callused' => $activation['OwnCallsign'],
			'numqsos' => $activation['QSOs'],
			'points' => $activation['Points'],
			'bonus' => $activation['BonusPoints']
		];
	}
	return $ret;
}

function getChasesForCallsign($callsign) {
	$userId = getUserIdForCallsign($callsign);
	if (!$userId) {
		return null;
	}
	return json_decode(file_get_contents("https://api-db2.sota.org.uk/logs/chaser/$userId/99999/1"), true);
}

function getCompleteCandidatesForCallsign($callsign) {
	$activations = getActivationsForCallsign($callsign);
	$chases = getChasesForCallsign($callsign);
	if ($activations === null || $chases === null) {
		return [];
	}

	$completeCandidates = [];
	foreach ($activations as $activation) {
		$completeCandidates[$activation['summitref']] = true;
	}

	foreach ($chases as $chase) {
		unset($completeCandidates[$chase['SummitCode']]);
	}
	$completeCandidates = array_keys($completeCandidates);
	sort($completeCandidates);
	return $completeCandidates;
}

function getUserIdForCallsign($callsign) {
	global $cacheFile, $cacheUpdated;

	// Check cache
	if (file_exists($cacheFile)) {
		$cache = json_decode(file_get_contents($cacheFile), true);
		if (@$cache[$callsign]) {
			return $cache[$callsign];
		}
	}

	if ($cacheUpdated) {
		// Not in cache, and we've already updated it, so do nothing
		return null;
	}

	// Not in cache; load cache again
	$allusers = json_decode(file_get_contents("https://api-db2.sota.org.uk/rolls/activator/-1/0/all/all"), true);
	$cache = [];
	foreach ($allusers as $user) {
		$cache[$user['Callsign']] = $user['UserID'];
	}

	$tmpFile = $cacheFile . getmypid();
	file_put_contents($tmpFile, json_encode($cache));
	rename($tmpFile, $cacheFile);
	$cacheUpdated = true;
	return @$cache[$callsign];
}
