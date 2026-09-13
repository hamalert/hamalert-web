<?php
$pageTitle = 'Alerts';
require_once("alerts.inc.php");

include('settings_begin.inc.php');
?>
<style type="text/css">
	tr.alert-row { cursor: pointer; }
	.alert-toggle { cursor: pointer; }
	tr.alert-detail-row > td { background-color: #f7f7f7; padding-left: 2em; }
	.alert-detail-table { margin-bottom: 0; background-color: transparent; }
	.alert-detail-table > tbody > tr > th,
	.alert-detail-table > tbody > tr > td { color: #666; border-top: none; padding: 2px 8px; }
	.alert-detail-key { white-space: nowrap; font-weight: bold; vertical-align: top; }
</style>

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
var expandedSpotIds = new Set();	// spot ids whose detail row is currently expanded; survives refreshAlerts() re-rendering the table

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

// Toggles the full-details child row for the spot whose overview row was clicked.
// Called via onclick on each "alert-row" <tr> (see renderAlertsTable() in alerts.inc.php).
function toggleSpotDetails(row) {
	var spotId = row.getAttribute('data-spotid');
	if (!spotId) {
		return;
	}
	if (expandedSpotIds.has(spotId)) {
		expandedSpotIds.delete(spotId);
	} else {
		expandedSpotIds.add(spotId);
	}
	applyRowExpansion(row, expandedSpotIds.has(spotId));
}

// Shows/hides the detail row that immediately follows a given "alert-row" <tr>,
// and flips its chevron to match.
function applyRowExpansion(row, expand) {
	var detailRow = row.nextElementSibling;
	if (!detailRow || !detailRow.classList.contains('alert-detail-row')) {
		return;
	}
	detailRow.style.display = expand ? '' : 'none';
	var chevron = row.querySelector('.alert-toggle');
	if (chevron) {
		chevron.classList.toggle('glyphicon-chevron-down', expand);
		chevron.classList.toggle('glyphicon-chevron-right', !expand);
	}
}

// Re-applies expandedSpotIds to the current table - needed because refreshAlerts()
// replaces #alertsTable's HTML wholesale every 15 s, which would otherwise collapse
// every expanded row on each refresh.
function reapplyExpandedState() {
	$('#alertsTable .alert-row').each(function() {
		var spotId = this.getAttribute('data-spotid');
		applyRowExpansion(this, expandedSpotIds.has(spotId));
	});
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
			reapplyExpandedState();
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
