<?php
$loginOptional = true;
session_cache_limiter(false);
require_once("../db.inc.php");
require_once("../config.inc.php");

addCacheHeader(time(), 86400);

header("Content-Type: application/json");

$stats = getSpotsStats(365);

/* Preprocess for Stats JS */
$jsStats = ['sources' => [], 'data' => [], 'xlabels' => []];
$sources = [];
foreach ($stats as $stat) {
	foreach ($stat['spots'] as $source => $count) {
		if (!@$sources[$source]) {
			$jsStats['sources'][] = $config['sources'][$source];
			$sources[$source] = true;
		}
	}
}

foreach ($stats as $stat) {
	$i = 0;
	foreach ($sources as $source => $dummy) {
		$jsStats['data'][$i][] = @$stat['spots'][$source] ?: 0;
		$i++;
	}
	$jsStats['xlabels'][] = $stat['date'];
}

echo json_encode($jsStats);
