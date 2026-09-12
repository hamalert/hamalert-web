<?php
require_once(__DIR__ . "/db.inc.php");

function formatTriggerComments($comments) {
	if (!$comments) {
		return '';
	}
	if (is_array($comments)) {
		return implode(', ', $comments);
	}
	return $comments;
}

function formatSpotDetails($spot) {
	// D-STAR is keyed by mode, not source: source now names the feed (quadnet/ircddb/dstarusers).
	if ($spot['mode'] == 'dstar') {
		// Frequency first like other spots; nothing when the repeater's frequency is unknown
		// (a band guessed from the module letter is only used for matching)
		$details = isset($spot['frequency']) ? $spot['frequency'] . " MHz, " : "";
		if (@$spot['dvEvent'] == 'linked') {
			$details .= "Linked " . @$spot['dvNode'] . " to " . @$spot['dvReflector'];
		} else if (@$spot['dvReflector'] && @$spot['dvNode']) {
			$details .= "Active on " . $spot['dvReflector'] . " via " . $spot['dvNode'];
		} else if (@$spot['dvReflector']) {
			// A dstarusers.org reflector-module report (e.g. "REF030-C") has no separate node -
			// the reflector itself is what was heard.
			$details .= "Active on " . $spot['dvReflector'];
		} else {
			$details .= "Active on " . @$spot['dvNode'];
		}
		if (@$spot['comment']) {
			$details .= ' "' . $spot['comment'] . '"';
		}
		if (isset($spot['dvDuration'])) {
			$details .= " (" . number_format($spot['dvDuration'], 1) . " s)";
		}
	} else if (isset($spot['frequency'])) {
		$details = $spot['frequency'] . " MHz";
		if (@$spot['mode']) {
			$details .= " " . strtoupper($spot['mode']);
		}
		if (@$spot['summitRef']) {
			$details .= ", SOTA " . $spot['summitRef'];
		}
		if (@$spot['wwffRef']) {
			$details .= ", WWFF " . $spot['wwffRef'];
		}
		if (@$spot['iotaGroupRef']) {
			$details .= ", IOTA " . $spot['iotaGroupRef'];
		}
	} else {
		$details = @$spot['mode'] ? strtoupper($spot['mode']) : "";
	}

	$html = htmlspecialchars($details);
	if (@$spot['rawText']) {
		$html .= '<br /><small class="text-muted">' . htmlspecialchars($spot['rawText']) . '</small>';
	}
	return $html;
}

// Renders the "no alerts" info box or the alerts table for the last 100 alerts
// in the last 24 hours. Shared between alerts.php (full page) and
// ajax/alerts.php (auto-refresh fragment).
function renderAlertsTable() {
	global $config;

	$spots = getRecentSpots(86400, 100);

	if (!$spots) {
		?>
<div class="alert alert-info" role="alert">
	No alerts in the last 24 hours. Alerts appear here whenever one of your triggers matches a spot, whatever action it uses.
</div>
		<?php
	} else {
		?>
<div class="table-responsive">
<table class="table table-striped">
	<thead>
		<tr>
			<th>Time</th>
			<th>Source</th>
			<th>Callsign</th>
			<th>Details</th>
			<th>Actions</th>
			<th>Trigger comment</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($spots as $spot): ?>
		<tr>
			<td><?php echo htmlspecialchars($spot['receivedDate']->toDateTime()->format("Y-m-d H:i:s") . "Z") ?></td>
			<td><?php echo htmlspecialchars($config['sources'][@$spot['source']] ?? @$spot['source']) ?></td>
			<td><?php echo htmlspecialchars(@$spot['fullCallsign']) ?></td>
			<td><?php echo formatSpotDetails($spot) ?></td>
			<td><?php echo htmlspecialchars(implode(', ', array_diff(@$spot['actions'] ?: [], ['myspot']))) ?></td>
			<td><?php echo htmlspecialchars(formatTriggerComments(@$spot['triggerComments'])) ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
		<?php
	}
}
