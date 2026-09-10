<?php
$pageTitle = 'Alerts';
require_once("alerts.inc.php");

include('settings_begin.inc.php');
?>
<h1 class="page-header">Alerts</h1>

<p>The last 100 alerts sent to you in the last 24 hours, across all actions.</p>

<p>
	<button type="button" class="btn btn-default btn-sm" id="alertsPauseBtn" onclick="toggleAlertsRefresh()"><span class="glyphicon glyphicon-pause" aria-hidden="true"></span> Pause</button>
	<button type="button" class="btn btn-default btn-sm" onclick="refreshAlerts()"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Refresh now</button>
	<small class="text-muted" id="alertsStatus"></small>
</p>

<div id="alertsTable">
<?php renderAlertsTable() ?>
</div>

<script type="text/javascript">
var refreshInterval = 15000;	// milliseconds between auto-refreshes
var alertsTimer = null;
var alertsPaused = false;
var sessionExpired = false;

function loadPausedState() {
	try { return localStorage.getItem('alertsPaused') === '1'; } catch (e) { return false; }
}

function savePausedState(paused) {
	try { localStorage.setItem('alertsPaused', paused ? '1' : '0'); } catch (e) { /* unavailable - ignore */ }
}

function setStatus(text, isWarning) {
	$('#alertsStatus').text(text)
		.toggleClass('text-danger', !!isWarning)
		.toggleClass('text-muted', !isWarning);
}

function updateStatusNormal() {
	var now = new Date().toISOString().substr(11, 8) + 'Z';
	if (alertsPaused) {
		setStatus('Updated ' + now + ' · Paused', false);
	} else {
		setStatus('Updated ' + now + ' · refreshing every ' + Math.round(refreshInterval / 1000) + ' s', false);
	}
}

function expireSession() {
	sessionExpired = true;
	stopTimer();
	setStatus('Session expired, please reload', true);
}

function refreshAlerts() {
	if (sessionExpired) {
		return;
	}
	$.get('ajax/alerts')
		.done(function(html) {
			if (typeof html === 'string' && html.indexOf('id="username"') !== -1) {
				return expireSession();
			}
			$('#alertsTable').html(html);
			updateStatusNormal();
		})
		.fail(function(jqxhr) {
			if (jqxhr.status == 401) {
				return expireSession();
			}
			setStatus('Refresh failed, retrying', true);
		});
}

function startTimer() {
	stopTimer();
	if (!sessionExpired) {
		alertsTimer = setInterval(refreshAlerts, refreshInterval);
	}
}

function stopTimer() {
	if (alertsTimer) {
		clearInterval(alertsTimer);
		alertsTimer = null;
	}
}

function applyPausedState() {
	if (alertsPaused) {
		$('#alertsPauseBtn').html('<span class="glyphicon glyphicon-play" aria-hidden="true"></span> Resume');
		stopTimer();
	} else {
		$('#alertsPauseBtn').html('<span class="glyphicon glyphicon-pause" aria-hidden="true"></span> Pause');
		if (document.visibilityState !== 'hidden') {
			startTimer();
		}
	}
	updateStatusNormal();
}

function toggleAlertsRefresh() {
	alertsPaused = !alertsPaused;
	savePausedState(alertsPaused);
	applyPausedState();
}

$(document).on('visibilitychange', function() {
	if (sessionExpired) {
		return;
	}
	if (document.visibilityState === 'hidden') {
		stopTimer();
	} else if (!alertsPaused) {
		refreshAlerts();
		startTimer();
	}
});

$(function() {
	alertsPaused = loadPausedState();
	applyPausedState();
});
</script>

<?php include('settings_end.inc.php') ?>
