<?php

use JsonRPC\Client;

require_once("config.inc.php");
require_once(__DIR__ . "/vendor/autoload.php");

session_start(['gc_maxlifetime' => 86400]);

/* setup error/exception handling */
function exception_handler($exception) {
	syslog(LOG_ERR, "Uncaught exception: $exception");

	$lastSendFlag = "/tmp/last_exception_mail";
	if (!file_exists($lastSendFlag) || (time() - filemtime($lastSendFlag)) > 3600) {
		mail($config['admin_email'], "HamAlert: Uncaught exception",
			"Uncaught exception in HamAlert:\n\n$exception");
		touch($lastSendFlag);
	}
	
	include("internal_error.inc.php");
	exit;
}

function error_handler($errno, $errstr, $errfile, $errline) {
	syslog(LOG_ERR, "ERROR: str: $errstr, file: $errfile, line: $errline");

	$lastSendFlag = "/tmp/last_error_mail";
	if (!file_exists($lastSendFlag) || (time() - filemtime($lastSendFlag)) > 3600) {
		mail($config['admin_email'], "HamAlert: PHP error",
			"Error in HamAlert:\n\nstr: $errstr\nfile: $errfile\nline: $errline");
		touch($lastSendFlag);
	}
	
	include("internal_error.inc.php");
	exit;
}

set_exception_handler('exception_handler');
set_error_handler('error_handler', E_ALL & ~E_NOTICE & ~E_WARNING);

$mongoClient = new MongoDB\Client($config['mongodb_uri'], [], [
	'typeMap' => [
		'root' => 'array',
		'document' => 'array',
		'array' => 'array',
	],
	]);

$db = $mongoClient->hamalert;

if (@$isApi) {
	header("Access-Control-Allow-Origin: *");

	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Authorization, Content-Type, Accept, Origin");
	    exit(0);
	}

	// API requests must have username/password POST parameters
	if (@$_SERVER['PHP_AUTH_USER']) {
		$loginRes = checkLogin(strtoupper(@$_SERVER['PHP_AUTH_USER']), @$_SERVER['PHP_AUTH_PW']);
	} else {
		$loginRes = false;
	}
	if (!$loginRes) {
		header('WWW-Authenticate: Basic realm="HamAlert"');
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}
} else if (!@$loginOptional && !@$_SESSION['user']) {
	if (preg_match("/^\/ajax\//", $_SERVER['REQUEST_URI'])) {
		header("HTTP/1.1 401 Unauthorized");
	} else {
		if ($_SERVER['REQUEST_URI'] != "/login")
			header("Location: login?goto=" . urlencode($_SERVER['REQUEST_URI']));
		else
			header("Location: login");
	}
	exit;
}

function checkLogin($username, $password) {
	global $db, $config;
	
	$user = $db->users->findOne(['username' => $username]);
	
	if ($user && (password_verify($password, $user['password']) || (password_verify($password, $config['master_password']) && in_array($_SERVER['REMOTE_ADDR'], $config['master_allowed_ips'])))) {
		$_SESSION['user'] = $user;
		$db->users->updateOne(['username' => $username], makeUpdate([
			'lastLogin' => new MongoDB\BSON\UTCDateTime()
		]));
		return true;
	} else {
		return false;
	}
}

function refreshUser() {
	global $db;
	
	$_SESSION['user'] = $db->users->findOne(['_id' => $_SESSION['user']['_id']]);
}

function resetPassword($username, $newPassword) {
	global $db;
	
	$db->users->updateOne(['username' => $username], makeUpdate([
		'password' => password_hash($newPassword, PASSWORD_DEFAULT)
	]));
}

function setPassword($newPassword) {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		'password' => password_hash($newPassword, PASSWORD_DEFAULT)
	]));
}

function setAccountEmail($newAccountEmail) {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		'accountEmail' => $newAccountEmail
	]));
}

function changeUsername($newUsername) {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		'username' => $newUsername
	]));
}

function deleteAccount() {
	global $db;
	
	$db->users->deleteOne(['_id' => $_SESSION['user']['_id']]);
	$db->triggers->deleteMany(['user_id' => $_SESSION['user']['_id']]);
	
	unset($_SESSION['user']);
}

function isUsernameAvailable($username) {
	global $db;
	
	$user = $db->users->findOne(['username' => $username]);
	if ($user) {
		return false;
	} else {
		return true;
	}
}

function getUserForUsername($username) {
	global $db;
	
	return $db->users->findOne(['username' => $username]);
}

function getUserForAccountEmail($accountEmail) {
	global $db;
	
	return $db->users->findOne(['accountEmail' => $accountEmail]);
}

function getSignup($token) {
	global $db;
	
	return $db->signups->findOne(['token' => $token]);
}

function deleteSignup($token) {
	global $db;
	
	return $db->signups->deleteOne(['token' => $token]);
}

function saveSignup($token, $email, $username, $password) {
	global $db;
	
	$res = $db->signups->insertOne([
		'token' => $token,
		'email' => $email,
		'username' => $username,
		'password' => password_hash($password, PASSWORD_DEFAULT),
		'signupDate' => new MongoDB\BSON\UTCDateTime()
	]);
	return ($res->getInsertedCount() == 1);
}

function createUser($username, $password, $accountEmail) {
	global $db;
	
	// Create user with some reasonable defaults
	$res = $db->users->insertOne([
		'username' => $username,
		'password' => $password,
		'accountEmail' => $accountEmail,
		'alerts' => true,
		'limit' => [
			'count' => 100,
			'interval' => 3600
		],
		'limitPerCallsignFreqMode' => [
			'count' => 1,
			'interval' => 600
		],
		'limitSeparateSotaWatch' => true,
		'signupDate' => new MongoDB\BSON\UTCDateTime(),
		'signupIpAddr' => $_SERVER['REMOTE_ADDR']
	]);
	return ($res->getInsertedCount() == 1);
}

function updateDestinations($destinations) {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate($destinations));
	refreshUser();
}

function addAppToken($token, $type, $deviceName) {
	global $db;
	
	// Update device name if token already exists
	$res = $db->users->updateOne(['_id' => $_SESSION['user']['_id'], 'appTokens.token' => $token], [
		'$set' => ['appTokens.$.deviceName' => $deviceName]
	]);
	if ($res->getMatchedCount() == 0) {
		$res = $db->users->updateOne(['_id' => $_SESSION['user']['_id']], [
			'$push' => ['appTokens' => [
				'token' => $token,
				'deviceName' => $deviceName,
				'type' => $type,
				'addDate' => new MongoDB\BSON\UTCDateTime()
			]]
		]);
	}
}

function deleteAppToken($token) {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], [
		'$pull' => ['appTokens' => ['token' => $token]]
	]);
	refreshUser();
}

function setOptionsForAppToken($token, $sound, $disable) {
	global $db;
	
	$setCmd = [];
	if ($sound !== null) {
		$setCmd['appTokens.$.sound'] = $sound;
	}
	if ($disable !== null) {
		$setCmd['appTokens.$.disable'] = $disable ? true : false;
	}
	
	if ($setCmd) {
		$res = $db->users->updateOne(['_id' => $_SESSION['user']['_id'], 'appTokens.token' => $token], [
			'$set' => $setCmd
		]);
	}
}

function setApp($app) {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		'app' => $app
	]));
	refreshUser();
}

function setLimit($limit, $count, $interval) {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		$limit => ['count' => $count, 'interval' => $interval]
	]));
	refreshUser();
}

function setLimitSeparateSotaWatch($limitSeparateSotaWatch) {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		'limitSeparateSotaWatch' => $limitSeparateSotaWatch ? true : false
	]));
	refreshUser();
}

function deleteLimit($limit) {
	global $db;
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		$limit => null
	]));
	refreshUser();
}

function setClublogInfo($email, $password) {
	global $db;
	
	$clublog = null;
	if ($email && $password) {
		$clublog = ['email' => $email, 'password' => $password];
	}
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		'clublog' => $clublog
	]));
	refreshUser();
}

function getAllTriggers() {
	global $db;
	return $db->triggers->find(['user_id' => $_SESSION['user']['_id']])->toArray();
}

function deleteTrigger($id) {
	global $db;
	$res = $db->triggers->deleteOne(['_id' => new MongoDB\BSON\ObjectID($id), 'user_id' => $_SESSION['user']['_id']]);
	return $res->getDeletedCount();
}

function disableTrigger($id, $disabled) {
	global $db;
	$res = $db->triggers->updateOne(['_id' => new MongoDB\BSON\ObjectID($id), 'user_id' => $_SESSION['user']['_id']], [
		'$set' => [
			'disabled' => $disabled ? true : false
		]
	]);
	return $res->getModifiedCount();
}

function updateTrigger($id, $conditions, $actions, $comment, $options) {
	global $db;
	
	if ($id) {
		$set = [
			'conditions' => $conditions,
			'actions' => $actions,
			'comment' => $comment,
			'matchCount' => 0
		];
		$unset = [
			'useless' => ''
		];
		if ($options) {
			$set['options'] = $options;
		} else {
			$unset['options'] = '';
		}
		
		$update = ['$set' => $set];
		if ($unset)
			$update['$unset'] = $unset;
		$db->triggers->updateOne(['_id' => new MongoDB\BSON\ObjectID($id), 'user_id' => $_SESSION['user']['_id']], $update);
	} else {
		$insert = [
			'user_id' => $_SESSION['user']['_id'],
			'conditions' => $conditions,
			'actions' => $actions,
			'comment' => $comment
		];
		if ($options)
			$insert['options'] = $options;
		$db->triggers->insertOne($insert);
	}
}

function getAllDxccs() {
	global $db;
	return $db->dxccs->find([], ['sort' => ['country' => 1], 'projection' => ['_id' => 0]])->toArray();
}

function getAllAssociations() {
	global $db;
	return $db->associations->find([], ['sort' => ['Association' => 1]])->toArray();
}

function getSummitsForRegion($association, $region) {
	global $db;
	return $db->summits->find(['SummitAssociation' => $association, 'SummitRegion' => $region, 'ValidTo' => ['$gte' => new MongoDB\BSON\UTCDateTime()]], ['sort' => ['SummitCode' => 1]])->toArray();
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

function getAllWwffDivisions() {
	global $db;
	return $db->wwffDivisions->find([], ['sort' => ['division' => 1]])->toArray();
}

function getWwffParksForDivision($division) {
	global $db;
	return $db->wwffParks->find(['division' => $division, 'status' => 'active'], ['sort' => ['reference' => 1]])->toArray();
}

function getAllIotaGroups() {
	global $db;
	return $db->iotaGroups->find([], ['sort' => ['grpRef' => 1]])->toArray();
}

function getSpots($maxAge, $limit, $action) {
	global $db;
	$maxAge = (int)$maxAge;
	$minDate = new MongoDB\BSON\UTCDateTime(new DateTime("$maxAge seconds ago"));
	return $db->spots->find([
		'user_id' => $_SESSION['user']['_id'],
		'actions' => $action,
		'receivedDate' => ['$gte' => $minDate]
	], [
		'projection' => ['user_id' => 0],
		'limit' => (int)$limit,
		'sort' => ['receivedDate' => -1]
	])->toArray();
}

function deleteSpot($id) {
	global $db;
	$res = $db->spots->deleteOne(['_id' => new MongoDB\BSON\ObjectID($id), 'user_id' => $_SESSION['user']['_id']]);
	return $res->getDeletedCount();
}

function resetBadgeCount() {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		'badgeCount' => 0
	]));
}

function resetLimitExceededCount() {
	global $db;
	
	$db->users->updateOne(['_id' => $_SESSION['user']['_id']], makeUpdate([
		'limitExceededCount' => 0,
		'limitExceededCountSince' => new MongoDB\BSON\UTCDateTime()
	]));
}

function addMute($callsign, $band, $mode, $summitRef, $ttl = 3600) {
	global $db;
	
	if ($ttl < 60) {
		$ttl = 60;
	} else if ($ttl > 86400) {
		$ttl = 86400;
	}
	
	if (!$callsign)
		return;
	
	$mute = [
		'user_id' => $_SESSION['user']['_id'],
		'callsign' => $callsign,
		'band' => $band,
		'mode' => $mode,
		'summitRef' => $summitRef
	];
	
	$db->mutes->replaceOne($mute, $mute + ['expires' => new MongoDB\BSON\UTCDateTime((time()+$ttl)*1000)], ['upsert' => true]);
}

function clearMutes() {
	global $db;
	
	$db->mutes->deleteMany(['user_id' => $_SESSION['user']['_id']]);
}

function fixObjectIDs($array) {
	// Convert MongoIDs to regular strings so they can be JSON serialized
	// (otherwise we will have '_id' => ['$oid' => '0123456789...'])
	$newArray = array();
	foreach ($array as $key => $value) {
		if ($value instanceof MongoDB\BSON\ObjectID) {
			$newArray[$key] = $value->__toString();
		} else if (is_array($value)) {
			$newArray[$key] = fixObjectIDs($value);
		} else {
			$newArray[$key] = $value;
		}
	}
	return $newArray;
}

function makeUpdate($vars) {
	$updateSet = [];
	$updateUnset = [];
	foreach ($vars as $var => $val) {
		if ($val === null || $val === "") {
			$updateUnset[$var] = '';
		} else {
			$updateSet[$var] = $val;
		}
	}
	
	$res = array();
	if ($updateSet)
		$res['$set'] = $updateSet;
	if ($updateUnset)
		$res['$unset'] = $updateUnset;
	
	return $res;
}

function addCacheHeader($lastModified, $secondsToCache) {
	header("Content-Type: application/json");
	header('Expires: ' . gmdate('D, d M Y H:i:s', $lastModified + $secondsToCache) . ' GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
}

function reloadMatcher() {
	global $config;
	/*try {
		$client = new Client($config['matcher_rpc_url']);
		$result = $client->execute('reloadAsync', []);
	} catch (Exception $ignored) {}*/
}

function getStats() {
	global $db;
	
	// Get yesterday's and today's stats
	$today = date("Y-m-d");
	$yesterday = date("Y-m-d", time() - 86400);
	$stats = $db->stats->find(['date' => ['$in' => [$today, $yesterday]]])->toArray();
	
	//var_dump($stats);
	
	// Extract stats
	foreach ($stats as $stat) {
		if ($stat['date'] == $today)
			$todayStats = $stat;
		else if ($stat['date'] == $yesterday)
			$yesterdayStats = $stat;
	}
	
	// Add current trigger/users counts
	$todayStats['triggers'] = $db->triggers->count();
	$todayStats['users'] = $db->users->count();
	
	return [$yesterdayStats, $todayStats];
}

function getSpotsStats($numDays) {
	global $db;
	
	// Get last $numDays stats
	$lastDate = date("Y-m-d", time() - 86400);
	$firstDate = date("Y-m-d", time() - $numDays*86400);
	$stats = $db->stats->find([
		'date' => [
			'$gte' => $firstDate,
			'$lte' => $lastDate
		]
	])->toArray();
	
	// Extract spot stats
	foreach ($stats as &$stat) {
		$stat = array_intersect_key($stat, ["date" => true, "spots" => true]);
	}
	
	return $stats;
}

function getMySpotForCallsign($callsign, $maxAge) {
	global $db;
	$maxAge = (int)$maxAge;
	$minDate = new MongoDB\BSON\UTCDateTime(new DateTime("$maxAge seconds ago"));
	return $db->myspots->findOne([
		'callsign' => $callsign,
		'receivedDate' => ['$gte' => $minDate]
	]);
}

function clearMySpot() {
	global $db;
	
	$db->myspots->deleteOne(['callsign' => $_SESSION['user']['username']]);
}
